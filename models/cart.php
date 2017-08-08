<?php

/**
 * Description of cart
 *
 * @author lepercon
 */
class cart
{
    public $cart_id;
    public $user_id;
    public $ip;
    public $created;
    public $modified;
    public $accessed;
    public $paid;
    public $cart_is_active;

    public $items = array();

    /** @var user */
    private $user;
    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'cart', 'cart', 'cart_id');
        $this->db_interface->class_property_exclusions[] = 'items';
        $this->ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        if ($id !== null) {
            $this->get_cart($id);
        }
    }

    public function cart_sub_total()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->get_price($this->user) * $item->quantity;
        }
        return $total;
    }

    public function cart_total_with_deductions($is_test = true)
    {
        return $this->cart_sub_total() - $this->cart_items_reaction($is_test);
    }

    public function cart_items_reaction($is_test = false)
    {
        $discount = 0;
        foreach ($this->items as $i) {
            $discount += $i->react_with_items($this->items, $is_test);
        }

        $packages = package::get_active_packages_for_user($this->user);
        if (count($packages) > 0) {
            foreach ($packages as $package) {
                $discount += $package->react_with_items($this->items, $is_test);
            }
        }

        $giftcards = purchasable_giftcard::get_unprocess_usages($this->cart_id);
        if (count($giftcards) > 0) {
            foreach ($giftcards as $row) {
                $discount += $row['giftcard_usage_amount'];
            }
        }

        return $discount;
    }

    public function cart_contents_html($class = 'cart-contents', $can_remove_items = false, $show_prices = false)
    {
        global $site_domain, $site_path;
        $string = "<ul class='$class'>";
        if (count($this->items) === 0) {
            $string .= "Your cart is empty.";
        }
        if ($can_remove_items AND filter_input(INPUT_POST, 'remove_cart_item_id') !== null AND intval(filter_input(INPUT_POST, 'remove_cart_item_id')) > 0) {
            $cart_item = new cart_item(filter_input(INPUT_POST, 'remove_cart_item_id'));
            $cart_item->delete();
            purchasable_giftcard_instance::remove_unprocessed_usages($this->cart_id);
            header("location: //" . $site_domain . $site_path . "checkout/");
            die();
        }
        foreach ($this->items as $item) {
            $cls = $item->purchasable_class;
            $obj = new $cls($item->purchasable_class_id);
            $string .= "<li>" . $obj->get_readible_name();
            if ($can_remove_items) {
                $string .= "<form action='//" . $site_domain . $site_path . "checkout/' method='POST'><input type='hidden' name='remove_cart_item_id' value='" . $item->cart_item_id . "' />";
            }
            if ($show_prices) {
                if ($item->quantity > 1) {
                    $string .= money($item->get_price($this->user)) . " x " . $item->quantity . " = <span class='line-cost'>$" . money($item->quantity * $item->get_price($this->user)) . "</span>";
                } else {
                    $string .= "<span class='line-cost'>$" . money($item->get_price($this->user)) . "</span>";
                }
            }
            if ($can_remove_items) {
                $string .= "<button type='SUBMIT' class='remove-item' value='remove'>remove</button></form>";
            }
            $string .= "</li>";
        }
        $string .= "</ul>";
        $string .= $this->cart_contents_html_package_usage();
        $string .= $this->cart_contents_html_giftcard_usage();
        return $string;

    }

    public function cart_contents_html_giftcard_usage()
    {
        $string = "";
        $usages = purchasable_giftcard::get_unprocess_usages($this->cart_id);
        if (count($usages) > 0) {
            $string .= "<ul class='giftcard-usage'>";
            foreach ($usages as $row) {
                $card = new purchasable_giftcard_instance($row['purchasable_giftcard_instance_id']);
                $string .= "<li>Using $" . money($row['giftcard_usage_amount']) . " from giftcard XXXX-XXXX-" . substr($card->purchasable_giftcard_instance_human_id, 8) . "</li>";
            }
            $string .= "</ul>";
        }
        return $string;
    }

    public function cart_contents_html_package_usage()
    {
        $string = "";
        $packages = package::get_active_packages_for_user($this->user);
        if (count($packages) > 0) {
            $string .= "<ul class='deductions'>";
            foreach ($packages as $package) {
                $string .= "<li>Package usage (" . money($package->react_with_items($this->items)) . ") </li>";
            }
            $string .= "</ul>";
        }
        return $string;
    }

    public function cart_user_account_value_usage($is_test = false)
    {
        $discount = $this->cart_items_reaction($is_test);
        $remaining = $this->cart_sub_total() - $discount;
        if ($remaining > 0 AND cart::cart_is_for_a_terminal($this->cart_id) AND $this->user->user_account_value > 0) {
            $using_user_account_amount = min($remaining, $this->user->user_account_value);
            if ($is_test === false) {
                $this->user->user_note .= "<p>Used $" . $using_user_account_amount . " from this users internal account balance</p>";
                $this->user->user_account_value -= $using_user_account_amount;
                $this->user->set();
            }
            return $using_user_account_amount;
        } else {
            return 0;
        }
    }

    public function cart_checkout()
    {
        global $site_path;
        $string = "";
        $deduction = $this->cart_items_reaction(true);
        if ($this->cart_sub_total() === $deduction AND count($this->items) > 0) {
            $can_fast_forward = true;
        } else {
            $can_fast_forward = false;
        }
        if (!$this->cart_checkout_items_preprocessing() AND $this->cart_can_checkout(true)) {
            if (filter_input(INPUT_POST, 'cart_checkout_form') === '1' AND !$can_fast_forward) {
                $this->cart_checkout_validate();
            }
            if (filter_input(INPUT_POST, 'cart_checkout_form') === '1' AND !boffice_error::has_form_errors()) {
                if ($can_fast_forward) {
                    $this->cart_checkout_actualize();
                    header("location: " . $site_path . "receipt/" . $this->cart_id);
                } else if ($this->cart_charge_card()) {
                    header("location: " . $site_path . "receipt/" . $this->cart_id);
                }
            }
            if (filter_input(INPUT_POST, 'cart_checkout_form') !== '1' OR boffice_error::has_form_errors()) {
                if ($can_fast_forward) {
                    $string .= "<form action='' method='POST'><input type='hidden' name='cart_checkout_form' value='1' /><button type='submit' id='fast_forward'>Confirm Purchase of $0.00</button></form>";
                } else {
                    $string .= $this->cart_checkout_html_form();
                }
            }
        }
        return $string;
    }

    public function card_charge_swiped_card($line1, $line2)
    {
        global $merchant_class;
        $deduction = $this->cart_items_reaction(true);
        $amount = $this->cart_sub_total() - $deduction;
        $invoice_id = random_string(19);

        $merchant = new $merchant_class();
        $merchant->amount = $amount;
        $merchant->invoice_id = $invoice_id;


        if (cart::cart_has_paid($this->cart_id)) {
            //avoid charging patron's credit card more than once
            prepend_log("cart has paid");
            return true;
        }
        if ($merchant->charge_from_swipe($line1, $line2)) {
            $transaction_id = $this->cart_checkout_actualize();
            prepend_log("here " . $merchant->card_number);
            $merchant->create_finacial_details($transaction_id);
            return true;
        } else {
            prepend_log($merchant->last_error());
            die("HERE");

            return false;
        }
    }

    private function cart_charge_card()
    {
        global $merchant_class;
        $deduction = $this->cart_items_reaction(true);
        $amount = $this->cart_sub_total() - $deduction;

        /**
         * Authorize.net has a max value of 20
         * BluePay is undocumented but claims to support 'everything authorize.net does'
         * Stripe allows arbitrary metadata key/value pairs with not apparent value limit
         */
        $invoice_id = random_string(19);

        $merchant = new $merchant_class();
        $merchant->amount = $amount;
        $merchant->card_number = filter_input(INPUT_POST, 'credit_card_number');
        $merchant->exp_month = filter_input(INPUT_POST, 'expiry_month');
        $merchant->exp_year = filter_input(INPUT_POST, 'expiry_year');
        $merchant->cvc = '';
        $merchant->invoice_id = $invoice_id;
        $merchant->last_name = filter_input(INPUT_POST, 'user_name_last');
        $merchant->first_name = filter_input(INPUT_POST, 'user_name_first');
        $merchant->address = filter_input(INPUT_POST, 'user_address_line1');
        $merchant->zip = filter_input(INPUT_POST, 'user_zip');

        if (cart::cart_has_paid($this->cart_id)) {
            //avoid charging patron's credit card more than once
            return true;
        } else if ($merchant->charge()) {
            $transaction_id = $this->cart_checkout_actualize();
            $merchant->create_finacial_details($transaction_id);
            return true;
        } else {
            new boffice_error($merchant->last_error(), true, 'credit_card_number');
        }
    }

    /**
     * Assumes payment has occured
     */
    public function cart_checkout_actualize($transaction_type = 'web')
    {
        $loose_payment_amount = $this->cart_total_with_deductions(true);

        foreach ($this->items as $item) {
            $item->react_with_items($this->items, FALSE); // FALSE here means Do Action
        }
        $transaction = new transaction();
        $transaction->cart_id = $this->cart_id;
        $transaction->datetime = date("Y-m-d H:i:s");
        $transaction->user_id = $this->user_id;
        $transaction->set();

        $packages = package::get_active_packages_for_user($this->user);
        if (count($packages) > 0) {
            foreach ($packages as $package) {
                $package->do_process($this->items, null, $transaction->transaction_id);
            }
        }

        $giftcards = purchasable_giftcard::get_unprocess_usages($this->cart_id);
        if (count($giftcards) > 0) {
            foreach ($giftcards as $row) {
                purchasable_giftcard::actualize_unprocessed_usage($row['giftcard_usage_id'], $transaction->transaction_id);
            }
        }

        /* @var $item cart_item */
        foreach ($this->items as $item) {
            $obj = $item->get_cart_object();
            $obj->do_process($this->items, $item, $transaction->transaction_id);
        }

        $this->paid = date("Y-m-d H:i:s");
        $this->cart_is_active = 0;
        $this->set_cart();


        if (cart::cart_is_for_a_terminal($this->cart_id)) {
            $this->cart_user_account_value_usage(false);
        }

        //Terminal Handling
        if ($transaction_type !== "web") {
            $csr = user::current_user();
            $deets = new payment_finacial_details();
            $deets->payment_finacial_details_amount = $loose_payment_amount;
            $deets->payment_method = $transaction_type;
            $deets->terminal = $csr->user_id . " @ " . filter_input(INPUT_SERVER, 'REMOTE_ADDR');
            $deets->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_NONTRANSIENT;
            $deets->transaction_id = $transaction->transaction_id;
            $deets->set();
        }

        return $transaction->transaction_id;
    }

    static public function cart_has_paid($cart_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT paid FROM cart WHERE cart_id = " . db_escape($cart_id) . " LIMIT 1;");
        return $results[0]['paid'] !== db_null_date();
    }

    private function cart_checkout_validate()
    {
        $required = array(
            'user_name_last' => 'Last Name',
            'user_name_first' => 'First Name',
            'user_address_line1' => 'Address',
            'user_city' => 'City',
            'user_zip' => 'Zip',
            'credit_card_number' => 'Card Number',
        );
        foreach ($required as $field => $name) {
            if (filter_input(INPUT_POST, $field) === null OR filter_input(INPUT_POST, $field) === false OR filter_input(INPUT_POST, $field) === "") {
                new boffice_error("$name cannot be blank.", true, $field);
            }
        }
        if (!luhn_check(filter_input(INPUT_POST, 'credit_card_number'))) {
            new boffice_error("Your credit card number does not appear to be valid", true, 'credit_card_number');
        }
    }

    private function cart_checkout_html_form()
    {
        global $site_domain, $site_path;
        $fields = array('user_name_last', 'user_name_first', 'user_address_line1', 'user_address_line2', 'user_city', 'user_state', 'user_zip');
        $user_name_first = $user_name_last = $user_address_line1 = $user_address_line2 = $user_city = $user_state = $user_zip = null; //NB bitching
        foreach ($fields as $field) {
            $$field = $this->user->$field;
            if (filter_input(INPUT_POST, $field) !== null) {
                $$field = filter_input(INPUT_POST, $field);
            }
        }
        $USA_STATES = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'Dist of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming');
        $string = "
	    <div class='checkout-form'>
		<form id='cart_add_giftcard' method='POST' action='//" . $site_domain . $site_path . "checkout/giftcard'>
		    <div class='form-row'><a class='creditcard-show link'>Pay by credit card</a></div>		
		    <div class='form-row'><label for='giftcard_id'>Giftcard Number</label><input type='text' id='giftcard_id' name='giftcard_id' placeholder='123456789012' /></div>
		    <div class='form-row'><label for='giftcard_key'>Giftcard Key</label><input type='text' id='giftcard_key' name='giftcard_key' placeholder='ABCD' /></div>
		    <div class='form-row'><button type='SUMBIT' value='Add Giftcard'>Add Giftcard</button></div>
		</form>";
        if (boffice_property("checkout_donations_on") === '1') {
            $string .= "
		    <form id='cart_add_donation' method='POST' action='//" . $site_domain . $site_path . "checkout'>
			<div class='form-row'><a class='creditcard-show link'>Back to checkout</a></div>
			<div class='form-row'><label for='donation_amount'>Donation Amount</label><input type='text' id='donation_amount' name='donation_amount' value='" . boffice_property("checkout_donations_default_amount") . "' /></div>
			<div class='form-row'><label for='donation_message'>Donation Message (optional)</label><input type='text' id='donation_message' name='donation_message'  /></div>
			<div class='form-row'><button type='SUMBIT' value='Add Donation'>Add Donation</button></div>
		    </form>";
        }
        $string .= "
		<form id='cart_checkout_form' method='POST' action='//" . $site_domain . $site_path . "checkout/'  enctype='multipart/form-data' >
		    <div class='form-row'><a class='giftcard-show link'>Add a giftcard</a></div>
		    " . (boffice_property("checkout_donations_on") === '1' ? "<div class='form-row'><a class='donation-prompt link'>" . boffice_property("checkout_donations_prompt") . "</a></div>" : "") . "
		    <div class='form-row'><label for='user_name_first'>Last First</label><input type='text' value='$user_name_first' id='user_name_first' name='user_name_first' />" . boffice_error::form_error_by_element('user_name_first') . "</div>
		    <div class='form-row'><label for='user_name_last'>Last Name</label><input type='text' value='$user_name_last' id='user_name_last' name='user_name_last' />" . boffice_error::form_error_by_element('user_name_last') . "</div>
		    <div class='form-row'>
			<label for='user_address_line1' style='height:2em;'>Address Line 1</label>
			<input type='text' value='$user_address_line1' id='user_address_line1' name='user_address_line1' />" . boffice_error::form_error_by_element('user_address_line1') . "<br />
			<span class='label-fill'>&nbsp;</span>Street Address, P.O. box, company name, c/o    
		    </div>
		    <div class='form-row'>
			<label for='user_address_line2' style='height: 2em;'>Address Line 2</label>
			<input type='text' value='$user_address_line2' id='user_address_line2' name='user_address_line2' /><br />
			<span class='label-fill'>&nbsp;</span>Apartment, suite, unit, building, floor, etc.    
		    </div>
		    <div class='form-row'><label for='user_city'>City</label><input type='text' value='$user_city' id='user_city' name='user_city' />" . boffice_error::form_error_by_element('user_city') . "</div>
		    <div class='form-row'><label for='user_state'>State</label><select name='user_state' id='user_state' style='width:9em;'>";
        foreach ($USA_STATES as $abbr => $name) {
            $string .= "<option value='$abbr' " . ($abbr === $user_state ? "SELECTED='SELECTED'" : "") . " >$name</option>";
        }
        $string .= "</select>
			<label for='user_zip' style='width:2em; display:inline-block; '>Zip</label><input type='text' value='$user_zip' id='user_zip' name='user_zip' style='width:7em;' />" . boffice_error::form_error_by_element('user_zip') . "
		    </div>
		    
		    <div class='form-row'><label for='credit_card_number'>Card Number</label><input type='text' value='' id='credit_card_number' name='credit_card_number' style='width:10em;'/>" . boffice_error::form_error_by_element('credit_card_number') . "<span class='card-image'></span></div>
		    <div class='form-row'><label for='expiry_month'>Expiration</label><select name='expiry_month' id='expiry_month' style='width:auto;'><option disabled='DISABLED'>Month</option>
			<option value='01'>01 - January</option><option value='02'>02 - February</option><option value='03'>03 - March</option><option value='04'>04 - April</option><option value='05'>05 - May</option><option value='06'>06 - June</option><option value='07'>07 - July</option><option value='08'>08 - August</option><option value='09'>09 - September</option><option value='10'>10 - October</option><option value='11'>11 - November</option><option value='12'>12 - December</option></select>
		    <label for='expiry_year'></label><select id='expiry_year' name='expiry_year'  style='width:auto;'><option disabled='DISABLED'>Year</option>";
        foreach (array_keys(array_fill(date('Y'), 20, '0')) as $year) {
            $string .= "<option value='$year' " . (filter_input(INPUT_POST, 'expiry_year') == $year ? "SELECTED='SELECTED'" : "") . " >" . $year . "</option>";
        }
        $string .= "</select></div>
		    <input type='hidden' name='cart_checkout_form' value='1' />
		    <div class='form-row'><span class='label-fill'>&nbsp;</span><button type='SUBMIT' value='Purchase' id='submit_button'>Purchase</button></div>
		</form>
	    </div>";


        boffice_html::$js_external_src[] = $site_path . "cart.js";
        return $string;
    }

    private function cart_checkout_items_preprocessing()
    {
        foreach ($this->items as $item) {
            $preprocessing = $item->do_precheckout();
            if ($preprocessing === true) {
                return true;
            }
        }
        return false;
    }

    public function cart_can_checkout($throw_errors = false)
    {
        $can = true;
        if (!$this->user_id) {
            $can = false;
            if ($throw_errors) {
                new boffice_error("Please login first", false);
            }
        }
        if (!$this->cart_id OR count($this->items) === 0) {
            $can = false;
            if ($throw_errors) {
                new boffice_error("You have nothing in your cart.", false);
            }
        }
        if (strtotime($this->paid) > 0) {
            $can = false;
            if ($throw_errors) {
                new boffice_error("This cart has already been checked out.", false);
            }
        }
        return $can;
    }

    public function item_is_in_cart($purchasable_class, $purchasable_class_id)
    {
        foreach ($this->items as $item) {
            if ($item->purchasable_class == $purchasable_class AND $item->purchasable_class_id == $purchasable_class_id) {
                return true;
            }
        }
        return false;
    }

    public function cart_item_new($purchasable_class, $purchasable_class_id, $quantity, $multiples_allowed = false)
    {
        if ($multiples_allowed === true OR !$this->item_is_in_cart($purchasable_class, $purchasable_class_id)) {
            $item = new cart_item();
            $item->cart_id = $this->cart_id;
            $item->purchasable_class = $purchasable_class;
            $item->purchasable_class_id = $purchasable_class_id;
            $item->quantity = $quantity;
            $item->set_cart_item();
            $this->cart_item_add($item);
            return $item;
        } else {
            return cart_item::get_cart_item_from_class_id($purchasable_class, $purchasable_class_id);
        }
    }

    public function cart_item_add($cart_item)
    {
        $this->items[] = $cart_item;
    }

    public function cart_item_remove($cart_item)
    {
        $i = array_search($cart_item, $this->items);
        if ($i !== false) {
            array_splice($this->items, $i, 1);
            $cart_item->delete();
        }
    }

    public function get_cart($id)
    {
        global $global_conn;
        $this->db_interface->get($id);
        $items_result = db_query($global_conn, "SELECT * FROM cart_item WHERE cart_id = " . db_escape($id));
        foreach ($items_result as $item_data) {
            $item = new cart_item();
            $item->cart_id = $id;
            $item->cart_item_id = $item_data['cart_item_id'];
            $item->purchasable_class_id = $item_data['purchasable_class_id'];
            $item->purchasable_class = $item_data['purchasable_class'];
            $item->resultant_class_id = $item_data['resultant_class_id'];
            $item->quantity = $item_data['quantity'];
            $item->cart_item_priced_as_patron_type_id = $item_data['cart_item_priced_as_patron_type_id'];
            $item->cart_item_added_datetime = $item_data['cart_item_added_datetime'];
            $this->items[] = $item;
        }
        $this->user = new user($this->user_id);
        $this->update_accessed_datetime();
    }

    static public function get_cart_from_transaction_id($transaction_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT cart_id FROM transcation WHERE transaction_id = " . db_escape($transaction_id));
        return new cart($results[0]['cart_id']);
    }

    public function update_accessed_datetime()
    {
        global $global_conn;
        $this->accessed = date("Y-m-d H:i:s");
        db_exec($global_conn, "UPDATE cart SET accessed = " . db_escape($this->accessed) . " WHERE cart_id = " . db_escape($this->cart_id));
    }

    public function set_cart()
    {
        $this->modified = date("Y-m-d H:i:s");
        if (!$this->cart_id) {
            cart::deactivate_all_carts_for_user($this->user_id);
            $this->created = date("Y-m-d H:i:s");
            $this->user = new user($this->user_id);
        }
        $this->db_interface->set();
    }

    public function __toString()
    {
        $string = "Cart #" . $this->cart_id . " for #" . $this->user_id . "@" . $this->ip . ". created:" . $this->created . ", modified:" . $this->modified;
        /* @var $cart_item cart_item */
        foreach ($this->items as $cart_item) {
            $string .= $cart_item;
        }
        return $string;
    }

    static public function deactivate_cart($cart_id)
    {
        global $global_conn;
        db_exec($global_conn, build_update_query($global_conn, 'cart', array('cart_is_active' => '0'), " cart_id = " . db_escape($cart_id)));
    }

    static public function deactivate_all_carts_for_user($user_id)
    {
        global $global_conn;
        db_exec($global_conn, build_update_query($global_conn, 'cart', array('cart_is_active' => '0'), " user_id = " . db_escape($user_id)));
    }

    static public function cart_exists($cart_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM cart WHERE cart_id = " . db_escape($cart_id));
        return count($results) > 0;
    }

    static public function current_cart_for_terminal()
    {
        if (isset($_SESSION['boffice'], $_SESSION['boffice']['terminal'], $_SESSION['boffice']['terminal']['cart_id']) AND cart::cart_exists($_SESSION['boffice']['terminal']['cart_id'])) {
            return new cart($_SESSION['boffice']['terminal']['cart_id']);
        } else {
            $cart = new cart();
            $cart->set_cart();
            if (!isset($_SESSION['boffice'])) {
                $_SESSION['boffice'] = array();
            }
            if (!isset($_SESSION['boffice']['terminal'])) {
                $_SESSION['boffice']['terminal'] = array();
            }
            $_SESSION['boffice']['terminal']['cart_id'] = $cart->cart_id;
            return $cart;
        }
    }

    static public function cart_is_for_a_terminal($cart_id)
    {
        return isset(
                $_SESSION['boffice'],
                $_SESSION['boffice']['terminal'],
                $_SESSION['boffice']['terminal']['cart_id']
            ) AND $_SESSION['boffice']['terminal']['cart_id'] === $cart_id;
    }

    /**
     * @global PDO $global_conn
     * @param int $user_id
     * @param boolean $require_active Does the cart need to be active?
     * @return cart
     */
    static public function cart_from_user_id($user_id, $require_active = true)
    {
        global $global_conn;
        $where = "";
        if ($require_active) {
            $where = "  AND cart_is_active = '1'";
        }
        $results = db_query($global_conn, "SELECT * FROM cart WHERE user_id = " . db_escape($user_id) . $where);
        if (count($results)) {
            return new cart($results[0]['cart_id']);
        } else {
            $cart = new cart();
            $cart->user_id = $user_id;
            $cart->cart_is_active = 1;
            $cart->set_cart();
            purchasable_giftcard_instance::remove_unprocessed_usages($cart->cart_id);
            return $cart;
        }
    }

    static public function delete_cart_for_terminal()
    {
        if (isset($_SESSION['boffice']['terminal']['cart_id'])) {
            $cart_id = $_SESSION['boffice']['terminal']['cart_id'];
            global $global_conn;
            db_exec($global_conn, "DELETE FROM cart_item WHERE cart_id = " . db_escape($cart_id));
            db_exec($global_conn, "DELETE FROM cart WHERE cart_id = " . db_escape($cart_id));
            unset($_SESSION['boffice']['terminal']['cart_id']);
        }
    }

    /**
     * Does the cart have items of type $type
     * @param string $type One of the item type constants from the purchasable class
     * @return boolean
     */
    public function has_type($type)
    {
        foreach ($this->items as $item) {
            $cls = $item->purchasable_class;
            $instance = new $cls($item->purchasable_class_id);
            if ($instance->purchasable_item_type === $type) {
                return true;
            }
        }
        return false;
    }

}
