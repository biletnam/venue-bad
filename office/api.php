<?php

require_once '../boffice_config.php';

require_once 'api.reservations.php';
require_once 'api.business.php';
require_once 'api.web.php';

$user = user::current_user();
if ($user === null) {
    die('no_session');
}
if (!($user->user_is_office_admin OR $user->user_is_finacial_admin OR $user->user_is_show_admin)) {
    die('Access Denied');
}

//setup
$string = "";


//Basic Variables
if (filter_input(INPUT_GET, 'use_get') === '1') {
    $method = INPUT_GET;
} else {
    $method = INPUT_POST;
}
$command = filter_input($method, 'command');
$query = filter_input($method, 'query');
$user_id = filter_input($method, 'user_id');
$show_id = filter_input(INPUT_POST, 'show_id');
$seat_instance_id = filter_input($method, 'seat_instance_id');
$show_instance_id = filter_input($method, 'show_instance_id');
$reservation_id = filter_input($method, 'reservation_id');
$purchasable_registration_id = filter_input($method, 'purchasable_registration_id');
$purchasable_registration_category_id = filter_input($method, 'purchasable_registration_category_id');
$purchasable_registration_instance_id = filter_input($method, 'purchasable_registration_instance_id');


if ($command === 'shows_active') {
    $string .= "<select>";
    foreach (db_query($global_conn, "SELECT * FROM shows LEFT JOIN show_instance USING (show_id) WHERE datetime > NOW() ORDER BY datetime ASC") as $show) {
        $string .= "<option value='" . $show['show_instance_id'] . "'>" . date('M j, g:ia', strtotime($show['datetime'])) . " - " . substr($show['title'], 0, 90) . "</option>";
    }
    $string .= "</select>";
} else if ($command === 'users_search') {
    $q = db_escape("%" . $query . "%");
    $results = db_query($global_conn, "SELECT * FROM users WHERE user_name_last LIKE $q OR user_name_first LIKE $q OR user_email LIKE $q ORDER BY user_name_last, user_name_first ASC");
    if (count($results)) {
        $string = "<ul class='users_results'>";
        foreach ($results as $user) {
            $string .= "<li class='user' user_id='" . $user['user_id'] . "'>" . $user['user_name_last'] . ", " . $user['user_name_first'] . ". " . $user['user_email'] . "</li>";
        }
        $string .= "</ul>";
    } else {
        $string = "No user matches those results.";
    }
} else if ($command === 'user_data') {
    $string = "<div class='tabs user_data' user_id='" . $user_id . "'>
	<ul>
	    <li><a href='#user_" . $user_id . "_data'>Information</a></li>
	    <li><a href='#user_" . $user_id . "_purchases'>Purchases</a></li>	    
	    <li><a href='#user_" . $user_id . "_packages'>Packages</a></li>
	    <li><a href='#user_" . $user_id . "_transactions'>Transactions</a></li>
	</ul>";

    //Tab for user data
    $user = new user($user_id);
    $user_element = new boe("div", $user->admin_edit_form());
    $user_element->class = "user_data";
    $user_element->id = "user_" . $user_id . "_data";
    $string .= (String)$user_element;

    //Tab for User Purchases
    $string .= "<div id='user_" . $user_id . "_purchases' class='user-purchases'><ul>";
    foreach (db_query($global_conn, "SELECT * FROM transaction LEFT JOIN cart_item USING (cart_id) WHERE user_id = " . db_escape($user_id)) as $cart_item_result) {
        $cart_item = new cart_item($cart_item_result['cart_item_id']);
        $string .= "<li class='" . $cart_item->purchasable_class . "' " . $cart_item->purchasable_class . "='" . $cart_item->purchasable_class_id . "' result_id='" . $cart_item->resultant_class_id . "'>";

        if ($cart_item->purchasable_class === null) {
            $cls = "purchasable_null";
        } else {
            $cls = $cart_item->purchasable_class;
        }

        $obj = new $cls($cart_item->purchasable_class_id);
        if ($cart_item->purchasable_class === 'purchasable_seat_instance') {
            $show_instance = new show_instance($obj->show_instance_id);
            $string .= "Seat. " . $obj->get_readible_name() . " @ " . date("Y-m-d g:ia", strtotime($show_instance->datetime));
        } else if ($cart_item->purchasable_class === 'purchasable_registration_instance') {
            $string .= "Registration. " . $obj->get_readible_name() . " x " . $cart_item->quantity;
        } else if ($cart_item->purchasable_class === 'purchasable_registration') {
            $string .= "Registration. " . $obj->get_readible_name() . " x " . $cart_item->quantity;
        } else if ($cart_item->purchasable_class === 'purchasable_package_model') {
            $string .= "Package. " . $obj->get_readible_name();
        } else {
            $string .= "Unknown class: " . $cart_item->purchasable_class . " (" . $cart_item->cart_item_id . ")";
        }
        $string .= "</li>";
    }
    $string .= "</ul></div>";


    //Tab for user transactions
    $string .= "<div id='user_" . $user_id . "_transactions' class='user-transactions'><ul>";
    foreach (transaction::transactions_by_user($user->user_id) as $trans) {
        $string .= "<li class='transaction' transaction_id='" . $trans->transaction_id . "'>" . date("Y-m-d g:ia", strtotime($trans->datetime)) . "</li>";
    }
    $string .= "</div>";


    //Tab for packagages
    $string .= "<div id='user_" . $user_id . "_packages' class='user_packages'><ul>";
    foreach (package::get_all_packages_for_user($user) as $package) {
        $string .= "<li class='package " . ($package->is_active() ? 'active' : 'inactive') . "' package_id='" . $package->package_id . "'>(" . $package->package_id . ") " . $package->package_model->package_model_name . ". Created " . date('M jS, Y', strtotime($package->originating_transaction->datetime)) . "</li>";
    }
    $string .= "</div>";


    //End Tab Wrapper
    $string .= "</div>";


} else if ($command === "new_user_dialog") {
    $user = new user();
    $string = $user->admin_edit_form();


} else if ($command === "user_data_as_select_option") {
    $user = new user($user_id);
    $string = "<option value='" . $user->user_id . "'>" . $user->user_name_first . " " . $user->user_name_last . "</option>";


} else if ($command === 'reservation_pull') {
    $string = reservation_to_string(new reservation($reservation_id));
} else if ($command === "reservation_by_seat_instance_id" OR $command === "reservation_by_purchasable_instance_id") {
    $string = reservation_to_string(reservation::get_reseravtion_by_seat_instance_id(filter_input(INPUT_POST, "purchasable_seat_instance_id")));
} else if ($command === "reservation_cancel") {
    $reservation = new reservation($reservation_id);
    $string = $reservation->cancel(false);


// REGISTRATIONS
//===============
} else if ($command === "registration_by_purchasable_instance") {
    $q = "
	SELECT * 
	FROM cart_item 
	    LEFT JOIN transaction USING (cart_id)
	    LEFT JOIN registrations USING (transaction_id)
	WHERE cart_item.purchasable_class = 'purchasable_registration_instance' AND cart_item.purchasable_class_id = " . db_escape(filter_input(INPUT_POST, 'purchasable_registration_instance_id'));
    $results = db_query($global_conn, $q);
    $reggie_instance = new purchasable_registration_instance($results[0]['registration_id']);
    $reggie = new purchasable_registration($reggie_instance->purchasable_registration_id);
    var_dump($results);
    $string = "<div class='results'><ul>
	<li>" . $reggie->reg_name . $reggie_instance->get_readible_name() . "</li>
	<li>Quantity: " . $results[0]['quantity'] . "</li>
	<li>Purchased: " . $results[0]['cart_item_added_datetime'] . "</li>
	<li class='transaction' transaction_id='" . $results[0]['transaction_id'] . "'>Transaction id: " . $results[0]['transaction_id'] . "</li>
	<li class='user' user_id='" . $results[0]['user_id'] . "'>User id: " . $results[0]['user_id'] . "</li>
    </ul></div>";

} else if ($command === "new_class_dialog") {
    $registrations = new purchasable_registration();
    $string = $registrations->admin_edit_form();


// SHOPPING CART
//===============	    
} else if ($command === "transaction") {
    $transaction_id = filter_input(INPUT_POST, 'transaction_id');
    $results = db_query($global_conn, 'SELECT * FROM transaction LEFT JOIN payment_finacial_details USING (transaction_id) WHERE transaction_id = ' . db_escape($transaction_id));
    if ($results[0]['payment_finacial_details_amount'] > 0) {
        $class = "valid";
    } else {
        $class = "voided";
    }
    $string = "<div class='transaction-results $class'><h2>$class</h2><ul>";
    foreach ($results[0] as $key => $value) {
        $string .= "<li>" . $key . " - " . $value . "</li>";
    }
    $string .= "</ul>";
    if ($results[0]['payment_finacial_details_amount'] > 0) {
        $string .= "<button class='refundable' transaction_id='$transaction_id'>Refund</button>";
    }
    $string .= "</div>";


} else if ($command === "transaction_refund") {
    global $merchant_class;

    $transaction_id = filter_input(INPUT_POST, 'transaction_id');
    if ($transaction_id < 1) {
        die('need a valid transaction id');
    }
    $payment_details = transaction::payment_finacial_details($transaction_id);
    $merchant = new $merchant_class();
    if ($merchant->refund($transaction_id, $payment_details->payment_finacial_details_amount)) {
        $string = "1";
    } else {
        $string = $merchant->last_error;
    }


} else if ($command === "new_seat_cart_item") {
    $cart = cart::current_cart_for_terminal();
    $cart_item = $cart->cart_item_new("purchasable_seat_instance", $seat_instance_id, 1);
    $string = $cart_item->cart_item_id;

} else if ($command === "new_general_seat_cart_item") {
    $cart = cart::current_cart_for_terminal();
    if ($user_id === null) {
        prepend_log("Tried to create a new cart_item wihtout a user_id in api.php:new_general_seat_cart_item");
        die('needs user_id');
    }
    $cart->user_id = $user_id;
    $cart->set_cart();
    $show_instance = new show_instance($show_instance_id);
    $show_instance->get_purchasable_seating_general();
    $cart_item = $cart->cart_item_new("purchasable_seating_general", $show_instance->purchasable_seating_general->purchasable_seating_general_id, filter_input($method, 'quantity'), true);
    $string = $cart_item->cart_item_id;

} else if ($command === "empty_cart") {
    cart::delete_cart_for_terminal();


} else if ($command === "get_current_cart") {
    $cart = cart::current_cart_for_terminal();
    $string = "<ul>";
    /* @var $item cart_item */
    foreach ($cart->items as $item) {
        /* @var $obj purchasable */
        $obj = $item->get_cart_object();
        $string .= "<li>" . $obj->get_readible_name() . ".<span class='line-cost'> ";
        if ($item->quantity > 1) {
            $string .= " x" . $item->quantity . " @" . $obj->get_price(null) . "/each = $" . money($obj->get_price(null) * $item->quantity);
        } else {
            $string .= "$" . money($obj->get_price(null));
        }
        $string .= "</span></li>";
    }
    $string .= "</ul>";
    $string .= $cart->cart_contents_html_package_usage();
    if ($cart->cart_user_account_value_usage(true) > 0) {
        $string .= "<ul><li class='account-value-usage'>This transaction will use <span class='line-cost'>$" . $cart->cart_user_account_value_usage(true) . "</span> of this user's internal account value.</li></ul>";
    }
    $string .= $cart->cart_contents_html_giftcard_usage();
    $string .= "  
	<div class='register-summary'>
	    <div class='form-row subtotal' subtotal='" . $cart->cart_sub_total() . "'><label>Subtotal</label>" . money($cart->cart_sub_total()) . "</div> 
	    <div class='form-row'><label>Deductions</label>" . money($cart->cart_items_reaction(true) + $cart->cart_user_account_value_usage(true)) . "</div>
	    <div class='form-row total' total='" . ($cart->cart_sub_total() - ($cart->cart_items_reaction(true) + $cart->cart_user_account_value_usage(true))) . "'><label>Total</label>$ " . money($cart->cart_sub_total() - ($cart->cart_items_reaction(true) + $cart->cart_user_account_value_usage(true))) . "</div>
	</div>
	";


} else if ($command === "cart_set_user_id") {
    $cart = cart::current_cart_for_terminal();
    $cart->user_id = $user_id;
    $cart->set_cart();


} else if ($command === "get_current_cart_subtotal") {
    $cart = cart::current_cart_for_terminal();
    $string = $cart->cart_sub_total();


} else if ($command === "get_current_cart_deductions") {
    $cart = cart::current_cart_for_terminal();
    $string = $cart->cart_items_reaction(true) + $cart->cart_user_account_value_usage(true);


} else if ($command === "get_current_cart_total") {
    $cart = cart::current_cart_for_terminal();
    $string = $cart->cart_sub_total() - ($cart->cart_items_reaction(true) + $cart->cart_user_account_value_usage(true));


} else if ($command === "process_current_cart_manually") {
    $cart = cart::current_cart_for_terminal();
    $transaction_id = $cart->cart_checkout_actualize(filter_input(INPUT_POST, 'transaction_type'));
    unset($_SESSION['boffice']['terminal']['cart_id']);
    $string = $transaction_id;

} else if ($command === "card_swipe") {
    $cart = cart::current_cart_for_terminal();
    $response = $cart->card_charge_swiped_card(filter_input(INPUT_POST, 'line1'), filter_input(INPUT_POST, 'line2'));
    if ($response) {
        $string = "1";//success or card error message
    } else {
        $string = "Failed";
    }


// SEATING CHART, WILLCALL
//=========================
} else if ($command === "seat_readible_name") {
    $seat = new purchasable_seat_instance($seat_instance_id);
    $string .= $seat->get_readible_name();


} else if ($command === "seating_chart") {
    $show_instance = new show_instance($show_instance_id);
    if ($show_instance->seating_chart_id > 0) {
        //Reserved Seating
        if (filter_input(INPUT_POST, 'refresh_chart') === '1') {
            $show_instance->seating_chart_html_update();
        }
        $string = $show_instance->seating_chart_html();
    } else {
        //General Seating
        $show_instance->get_purchasable_seating_general();
        $total = $show_instance->purchasable_seating_general->purchasable_seating_general_quantity_total;
        $reserved = $show_instance->purchasable_seating_general->purchasable_seating_general_quantity_total - $show_instance->purchasable_seating_general->get_quantity();
        $available = $show_instance->purchasable_seating_general->get_quantity();
        $string .= "
	    <div class='general-seating-chart-form'>
		<input type='text' id='count' value='2' />
		<select class='user-id'></select>
		<button>Create Reservation</button>
	    </div>
	    <ul class='general-seating-chart-details'>
		<li class='total' total='$total'><span class='label'>Total</span> $total</li>
		<li class='reserved' reserved='$reserved'><span class='label'>Reserved</span> $reserved</li>
		<li class='available' available='$available'><span class='label'>Available</span> $available</li>
	    </ul>
	    <div id='show_guage'></div>
	    ";
    }


} else if ($command === "seatingchart_new") {
    $chart = new seating_chart();
    $string .= $chart->admin_editor(32, $site_path . "office/");


} else if ($command === "willcall") {
    $show_instance = new show_instance($show_instance_id);
    $string = "
	<div class='tabs'>
	    <ul>
		<li><a href='#tabs-willcall'>Willcall</a></li>
		<li><a href='#tabs-workers'>Workers</a></li>
	    </ul>
	    <div id='tabs-willcall'>" . $show_instance->willcall() . "</div>
            <div id='tabs-workers'>Loading...</div> 
	</div>";
} else if ($command === "show_worker_list") {
    $show_instance = new show_instance($show_instance_id);
    $string = $show_instance->get_workers_admin();
} else if ($command === "show_worker_new") {
    $show_instance_worker = new show_instance_worker();
    $string = $show_instance_worker->admin_edit_form_create();
} else if ($command === "show_worker_new_submit") {
    $show_instance_worker = new show_instance_worker();
    $show_instance_worker->show_instance_id = filter_input(INPUT_POST, 'show_instance_id');
    $show_instance_worker->show_instance_worker_status = show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_UNFILLED;
    $show_instance_worker->user_id = 0;
    $show_instance_worker->show_instance_worker_type_id = filter_input(INPUT_POST, 'show_instance_worker_type_id');
    $show_instance_worker->set();
    $string .= "Created new job.";
} else if ($command === "show_worker_unfill") {
    if (filter_input(INPUT_POST, 'show_instance_worker_id') < 1) {
        die("need a valid show_instance_worker_id");
    }
    show_instance_worker::unfill(filter_input(INPUT_POST, 'show_instance_worker_id'));
    $string = "Worker unassigned.";
} else if ($command === "show_worker_fill") {
    $string = "
	<form method='POST' action='#' class='assign-worker-dialog'>
	    <div class='form-row'>
		<label for='worker_user_id'>User</label>
		<select name='worker_user_id' class='worker_user_id' id='worker_user_id'></select>
	    </div>
	    <div class='form-row'>
		<label for='worker_plus_one'>Plus One?</label>
		<select name='worker_plus_one' id='worker_plus_one'>
		    <option value='0' selected>No +1</option>
		    <option value='1'>Yes +1</option>
		</select>
	    </div>
	    <input type='hidden' name='show_instance_worker_id' value='" . filter_input(INPUT_POST, 'show_instance_worker_id') . "' />
	    <button type='SUBMIT' value='Assign User'>Assign User</button>
	</form>";
} else if ($command === "show_worker_fill_submit") {
    if (filter_input(INPUT_POST, 'worker_user_id') < 1) {
        die("Need to select a user. Search for a user and pull up their account to add them to the available users drop-down list.");
    }
    $show_instance_worker_id = filter_input(INPUT_POST, 'show_instance_worker_id');
    show_instance_worker::fill($show_instance_worker_id, filter_input(INPUT_POST, 'worker_user_id'), filter_input(INPUT_POST, 'worker_plus_one'));
    $string = "Assigned";


// SHOW DETAILS
//==============
} else if ($command === "show_edit_dialog") {
    $show_instance = new show_instance($show_instance_id);
    $show = new show($show_instance->show_id);
    $string .= $show->admin_edit_form_boxoffice($site_path . 'office/');
} else if ($command === "showtime_new") {
    $string = "<form action='" . $site_path . "office/' method='POST'><select name='show_id'>";
    foreach (db_query($global_conn, "SELECT * FROM shows") as $show) {
        $string .= "<option value='" . $show['show_id'] . "'>" . $show['title'] . "</option>";
    }
    $string .= "</select>
	<input type='text' class='datetime' name='datetime' id='datetime' />
	<script>$('#datetime').datetimepicker({step:30, formatTime:'g:ia'});</script>
	<button type='submit'>Create</button>
	<input type='hidden' name='showtime_new' value='1' />
	</form>";
} else if ($command === "showtime_edit_dialog") {
    $show_instance = new show_instance($show_instance_id);
    $string .= $show_instance->admin_edit_form($site_path . 'office/');

    /** Redundant
     * } else if ($command === "show_instance_details") {
     * $instance = new show_instance($show_instance_id);
     * $string = "<h2>needs edit listener in api and connected js</h2>";
     * $string .= $instance->admin_edit_form();
     */

} else if ($command === "show_instance_details_edit") {
    $instance = new show_instance($show_instance_id);
    $string = $instance->admin_edit_form();


// PACKAGES
//==========
} else if ($command === "package_pull") {
    $package = new package(filter_input(INPUT_POST, 'package_id'));
    $string = "<div class='package'><h3>Benefits</h3><ul>";
    /* @var $benny purchasable_package_model_benefit */
    foreach ($package->benefits as $benny) {
        $string .= "<li>" . $benny->package_model_benefit_label . ". " . $package->get_benefit_net_value($benny->package_model_benefit_type) . " of " . $package->get_benefit_total_value($benny->package_model_benefit_type) . " remaining </li>";
        $usages = $package->get_usages($benny->package_model_benefit_type);
        if (count($usages) === 0) {
            $string .= "No usages";
        } else {
            $string .= "<ul>Usages";
            foreach ($usages as $u) {

                if ($benny->package_model_benefit_type === purchasable_package_model_benefit::PACKAGE_MODEL_BENEFIT_TYPE_TICKET) {

                    $string .= "<li class='transaction' transaction_id='" . $u->transaction_id . "'>Transaction - #" . $u->transaction_id . " [reservation]</li>";
                } else {
                    $string .= "<li class='transaction' transaction_id='" . $u->transaction_id . "'>Transaction - #" . $u->transaction_id . "</li>";
                }
            }
            $string .= "</ul>";
        }
    }
    $string .= "</ul></div>";


} else if ($command === "packages_available") {
    $string = "<ul id='packages-list'><select class='user-id'></select>";
    foreach (purchasable_package_model::get_packages_available(false, 0) as $purchasable_package_model) {
        $string .= "
	    <li>
		<span class='title'>" . $purchasable_package_model->package_model_name . "</span><span class='cost'>$" . $purchasable_package_model->get_price(null) . " </span>
		<span class='patron_type'>Patron types: " . ($purchasable_package_model->package_model_patron_type_id > 0 ? patron_types::patron_type_id_to_label($purchasable_package_model->package_model_patron_type_id) : "All patron types") . "</span>
		<span class='period'>Valid for " . $purchasable_package_model->package_model_duration_in_days . " days</span>
		<span class='add'><button class='add' purchasable_package_model_id = '" . $purchasable_package_model->package_model_id . "' value='Add'>Add to cart</button></span>
	    </li>";
    }
    $string .= "</ul>";


} else if ($command === "package_add_to_cart") {
    $cart = cart::current_cart_for_terminal();
    if ($user_id === null) {
        prepend_log("Tried to create a new cart_item wihtout a user_id in api.php:package_add_to_cart");
        die('needs user_id');
    }
    if (filter_input($method, "purchasable_package_model_id") === null) {
        prepend_log("Tried to create a new cart_item wihtout a purchasable_package_model_id in api.php:package_add_to_cart");
        die('needs purchasable_package_model_id');
    }
    $cart->user_id = $user_id;
    $cart->set_cart();
    $cart_item = $cart->cart_item_new("purchasable_package_model", filter_input($method, "purchasable_package_model_id"), 1, true);
    $string = $cart_item->cart_item_id;


//=======================================
//               Giftcards               
//=======================================
} else if ($command === "giftcard_creation_form") {
    $string = "
	<div id='giftcard-creation-from'>
	    <div class='form-row'><label for='amount'>Amount</label><input type='text' id='amount' val='54' /></div>
	    <div class='form-row'><label for='method'>Delivery Method</label><select id='method'>
		<option value='print'>Print</option>
		<option value='ship'>Print and Ship</option>
		<option value='email'>Email</option>
	    </select></div>
	    <div class='form-row'><label class='method-data' for='method_data'>Address</label><textarea class='method-data' id='method_data'></textarea></div>
	    <div class='form-row'><button class='add'>Add</button></div>
	</div>";


} else if ($command === "giftcard_create") {
    $giftcard = new purchasable_giftcard_instance();
    $giftcard->purchasable_giftcard_instance_starting_value = filter_input($method, 'amount');
    $giftcard->purchasable_giftcard_instance_send_data = filter_input($method, 'method_data');
    $giftcard->purchasable_giftcard_instance_send_method = filter_input($method, 'method');
    $giftcard->set();
    $cart = cart::current_cart_for_terminal();
    $cart->cart_item_new('purchasable_giftcard_instance', $giftcard->purchasable_giftcard_instance_id, 1, true);
    $string = $giftcard->purchasable_giftcard_instance_id;


} else if ($command === "giftcard_pull") {
    $giftcard = new purchasable_giftcard_instance(filter_input($method, 'giftcard_id'));
    $url = $site_domain . $site_path . "giftcard/" . $giftcard->purchasable_giftcard_instance_robot_url . "/" . $giftcard->purchasable_giftcard_instance_human_id . ".jpg";
    $string = "<p>$" . $giftcard->get_remaining_value() . " of " . $giftcard->purchasable_giftcard_instance_starting_value . " remaining.</p>
	<pre>" . $giftcard->purchasable_giftcard_instance_send_data . "</pre>
	<a href='//$url' target='_blank'><img style='width:250px;'  src='//$url' /></a>";


} else if ($command === "giftcard_validate") {
    if (purchasable_giftcard_instance::is_valid(filter_input($method, 'id'), filter_input($method, 'key'))) {
        $card = purchasable_giftcard_instance::get_card_by_id(filter_input($method, 'id'));
        $string = $card->purchasable_giftcard_instance_id;
    } else {
        $string = '-1';
    }


} else if ($command === "giftcard_value") {
    $card = new purchasable_giftcard_instance(filter_input($method, 'instance_id'));
    $string = "" . $card->get_remaining_value();


} else if ($command === "giftcard_add") {
    $cart = cart::current_cart_for_terminal();
    $card = new purchasable_giftcard_instance(filter_input($method, 'instance_id'));
    $card->create_unprocessed_usage($card->purchasable_giftcard_instance_id, filter_input(INPUT_POST, 'amount'), $cart->cart_id);
    $string = '1';


//=======================================
//               CLASSES                 
//=======================================
} else if ($command === "update_classes_list") {
    $results = db_query($global_conn, "SELECT * FROM purchasable_registration LEFT JOIN purchasable_registration_category USING (purchasable_registration_category_id) ORDER BY purchasable_registration_category_id, reg_name ASC ");
    $string = "<select id='classes_list'>";
    foreach ($results as $row) {
        $string .= "<option value='" . $row['purchasable_registration_id'] . "'>" . $row['purchasable_registration_category_name'] . " - " . $row['reg_name'] . "</option>";
    }
    $string .= "</select>";


} else if ($command === "edit_class_dialog") {
    $purchasable_registration = new purchasable_registration($purchasable_registration_id);
    $string .= $purchasable_registration->admin_edit_form();


} else if ($command === "pull_class") {
    $purchasable_registration = new purchasable_registration($purchasable_registration_id);
    $string .= $purchasable_registration->admin_office_list();


} else if ($command === "new_class_category_dialog") {
    $puchasable_registration_category = new purchasable_registration_category();
    $string = $puchasable_registration_category->admin_edit_form();


} else if ($command === "class_category_list") {
    $string .= purchasable_registration_category::list_select();


} else if ($command === "edit_class_category_dialog") {
    $puchasable_registration_category = new purchasable_registration_category($purchasable_registration_category_id);
    $string = $puchasable_registration_category->admin_edit_form();


} else if ($command === "new_purchasable_registration_instance") {
    $purchasable_registration_instance = new purchasable_registration_instance();
    $string .= $purchasable_registration_instance->admin_edit_form($purchasable_registration_id);


} else if ($command === "add_registration_to_cart") {
    $cart = cart::current_cart_for_terminal();
    $cart_item = $cart->cart_item_new("purchasable_registration_instance", $purchasable_registration_instance_id, 1, true);
    $string = $cart_item->cart_item_id;

} else if ($command === "readible_class_name") {
    $purchasable_registration = new purchasable_registration($purchasable_registration_id);
    $string .= $purchasable_registration->get_readible_name();


//=======================================
//               Notices                 
//=======================================
} else if ($command === "notice_list_unacknowledged") {
    $notice_string = "";
    foreach (boffice_notice::list_unacknowledged() as $notice) {
        $class = "notice unacknowledged severity-" . $notice->boffice_notice_severity;
        $notice_string .= "
	    <li class='$class' boffice_notice_id='" . $notice->boffice_notice_id . "'>
		<button class='acknowledge' boffice_notice_id='" . $notice->boffice_notice_id . "'>&nbsp;</button><span class='date'>" . date("M j, g:ia", strtotime($notice->boffice_notice_datetime)) . "</span><br /><span class='message'>" . htmlentities($notice->boffice_notice_message) . "</span>
	    </li>";
    }
    if ($notice_string) {
        $string = "<ul class='notice-list-unacknowledged'>" . $notice_string . "</ul>";
    } else {
        $string = 'no_notices';
    }

} else if ($command === "notice_list_unacknowledged_count") {
    $string = count(boffice_notice::list_unacknowledged()) . "";

} else if ($command === "notice_acknowledge") {
    $boffice_notice_id = filter_input(INPUT_POST, 'boffice_notice_id');
    if ($boffice_notice_id < 1 OR !$user->user_has_any_elevated_privileges()) {
        die("Need a notice_id or permission denied");
    }
    $notice = new boffice_notice($boffice_notice_id);
    if ($notice->boffice_notice_acknowledged === '1') {
        die("Notice already acknoledged.");
    }
    $notice->acknowledge();
    $string = '1';


//=======================================
//               Bussniz                 
//=======================================
} else if ($command === "bus_overview") {
    $string = bus_overview();
} else if ($command === "bus_show") {
    $string = bus_show(filter_input($method, 'show_id'));
} else if ($command === "bus_instances") {
    $string = bus_instances(filter_input($method, 'show_id'));


//=======================================
//               "Web"                   
//=======================================
} else if ($command === "web_show_editor") {
    $string = web_show_editor();
} else if ($command === "web_shows_list") {
    $string = web_shows_list();
} else if ($command === "web_show_people") {
    $string = web_show_people();
} else if ($command === "web_show_add_person") {
    $string = web_show_add_person();
} else if ($command === "web_show_remove_person") {
    $string = web_show_remove_person();
} else if ($command === "web_show_instances") {
    $string = web_show_instances();
} else if ($command === "web_show_instance_delete") {
    $string = web_show_instance_delete();
} else if ($command === "web_show_instance_create") {
    $string = web_show_instance_create();
} else if ($command === "web_prop_list") {
    $string = web_prop_list();
} else if ($command === "web_prop_update") {
    $string = web_prop_update();
} else if ($command === "web_show_images_list") {
    $string = web_show_images_list();
} else if ($command === "web_show_image_upload") {
    $string = web_show_image_upload();
} else if ($command === "web_show_image_delete") {
    $string = web_show_image_delete();


// MVC - CONTROLLER
//==================
} else if ($command === "update_standard") {
    $cls = filter_input(INPUT_POST, 'update_cls');
    $object = new $cls(filter_input(INPUT_POST, 'row_id'));
    $object->admin_edit_form();
    if (boffice_error::any_errors()) {
        $string = "0";
    } else {
        $string = "1";
    }


} else if ($command === "show_new_dialog") {
    $show = new show();
    $string .= $show->admin_edit_form($site_path . 'office/');


} else {
    $string = "no_command";
}

if (isset($string) AND is_string($string) AND $string !== "") {
    echo $string;
}