<?php

/**
 * Description of reservation
 *
 * @author lepercon
 */
class package
{
    public $package_id; //parent
    public $cart_item_id;

    /**
     * @var purchasable_package_model
     */
    public $package_model;

    public $benefits;
    /**
     * @var transaction
     */
    public $originating_transaction;


    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'package', 'package', 'package_id');
        $this->db_interface->class_property_exclusions = array('package_model', 'benefits', 'originating_transaction');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get($id)
    {
        global $global_conn;
        $this->db_interface->get($id);

        $cart_item = new cart_item($this->cart_item_id);
        $this->package_model = $cart_item->get_cart_object();
        $this->benefits = $this->package_model->benefits;

        $results = db_query($global_conn, "
	    SELECT * 
	    FROM package
	    LEFT JOIN cart_item USING (cart_item_id)
	    LEFT JOIN cart USING (cart_id)
	    LEFT JOIN transaction USING (cart_id)
	    WHERE cart_item_id = " . db_escape($this->cart_item_id) . "
	    ");
        $this->originating_transaction = new transaction($results[0]['transaction_id']);
    }

    public function is_active()
    {
        $expired = strtotime("+" . $this->package_model->package_model_duration_in_days . " days", strtotime($this->originating_transaction->datetime)) <= time();
        $value = 0;

        /* @var $benefit purchasable_package_model_benefit */
        foreach ($this->benefits as $benefit) {
            $value += $this->get_benefit_net_value($benefit->package_model_benefit_type);
        }

        return !$expired AND $value > 0;
    }

    public function get_usages($benefit_type)
    {
        global $global_conn;
        $return = array();
        foreach (db_query($global_conn, "
	    SELECT * 
	    FROM 
		package_usage 
		LEFT JOIN purchasable_package_model_benefit ON (benefit_id = package_model_benefit_id) 
	    WHERE 
		package_id = " . db_escape($this->package_id) . "
		AND package_model_benefit_type = " . db_escape($benefit_type)) as $item) {
            $return[] = new package_usage($item['package_usage_id']);
        }
        return $return;
    }

    public function get_usage_sum($benefit_type)
    {
        $total = 0;
        foreach ($this->get_usages($benefit_type) as $usage) {
            $total += $usage->package_usage_deduction;
        }
        return $total;
    }

    public function get_benefit($benefit_type)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM purchasable_package_model_benefit WHERE package_model_benefit_type = " . db_escape($benefit_type) . " AND package_model_id = " . db_escape($this->package_model->package_model_id));
        if (count($results)) {
            return new purchasable_package_model_benefit($results[0]['package_model_benefit_id']);
        } else {
            return null;
        }
    }

    public function get_benefit_total_value($benefit_type)
    {
        $benefit = $this->get_benefit($benefit_type);
        return $benefit->package_model_benefit_value;
    }

    public function get_benefit_net_value($benefit_type)
    {
        return $this->get_benefit_total_value($benefit_type) - $this->get_usage_sum($benefit_type);
    }


    static public function get_all_packages_for_user($user)
    {
        global $global_conn;
        $results = db_query($global_conn, "
	    SELECT * 
	    FROM package
	    LEFT JOIN cart_item USING (cart_item_id) 
	    LEFT JOIN cart USING (cart_id) 
	    WHERE cart.user_id = " . db_escape($user->user_id) . "
	");
        $packages = array();
        foreach ($results as $item) {
            $packages[] = new package($item['package_id']);
        }
        return $packages;
    }

    static public function get_active_packages_for_user($user)
    {
        $active_packages = array();
        foreach (package::get_all_packages_for_user($user) as $package) {
            if ($package->is_active()) {
                $active_packages[] = $package;
            }
        }
        return $active_packages;
    }


    public function react_with_items($items, $is_test = false)
    {
        $is_test = 0; //is_test is not used here, see do_process
        $deduction = 0;
        foreach ($this->benefits as $benefit) {
            $max_value = $benefit->package_model_benefit_value - count($this->get_usages($benefit->package_model_benefit_type));
            /* @var $cart_item cart_item */
            foreach ($items as $cart_item) {
                if ($cart_item->purchasable_class === $benefit->package_model_benefit_type_class) {
                    $quantity = min($max_value, $cart_item->quantity);
                    $max_value -= $quantity; //could make $max_value = zero, but that's okay cause $99 * 0 = 0
                    $cart = new cart($cart_item->cart_id);
                    $deduction += $cart_item->get_price(new user($cart->user_id)) * $quantity;
                    // !! packages need to be generated, and cart_items need to be processed against during do_process
                }
            }
        }
        return $deduction;
    }

    public function do_process($items, $cart_item, $transaction_id = 0)
    {
        foreach ($this->benefits as $benefit) {
            $max_value = $benefit->package_model_benefit_value - count($this->get_usages($benefit->package_model_benefit_type));
            /* @var $cart_item cart_item */
            foreach ($items as $cart_item) {
                if ($cart_item->purchasable_class === $benefit->package_model_benefit_type_class AND $max_value > 0) {
                    $quantity = min($max_value, $cart_item->quantity);
                    $max_value -= $quantity;
                    $usage = new package_usage();
                    $usage->benefit_id = $benefit->package_model_benefit_id;
                    $usage->package_id = $this->package_id;
                    $usage->package_usage_deduction = $quantity;
                    $usage->transaction_id = $transaction_id;
                    $usage->set();
                }
            }
        }
    }


    /**
     *
     * @global PDO $global_conn
     * @param user $user
     */
    static public function advertise($user = null)
    {
        global $global_conn, $site_path;
        if ($user === null AND user::current_user() !== null) {
            $user = user::current_user();
        }
        if ($user === null) {
            $results = db_query($global_conn, "SELECT * FROM purchasable_package_model WHERE package_model_date_available < NOW() AND package_model_date_close > NOW()");
        } else {
            $results = db_query($global_conn, "SELECT * FROM purchasable_package_model WHERE package_model_date_available < NOW() AND package_model_date_close > NOW() AND package_model_patron_type_id = " . db_escape($user->patron_type_id));
        }
        $string = "<div class='available-packages'>";
        foreach ($results as $i) {
            $package = new purchasable_package_model($i['package_model_id']);
            $string .= "
		<div class='package'>
		    <div class='name'>" . $package->package_model_name . "</div>
		    <div class='description'>" . $package->package_model_description . "</div>
		    <div class='validity'>Valid until " . date("m/Y", strtotime("+" . $package->package_model_duration_in_days . " days")) . "</div>
		    <div class='multiuse'>" . ($package->package_model_is_single_use ? "Any value left on the package is rolled over for your next visit." : "Single-use") . "</div>
		    <div class='description'>$" . money($package->package_model_cost) . "</div>
		    <form action='" . $site_path . "package/new/" . $package->package_model_id . "' method='POST'>
			<button type='submit'>Buy " . $package->package_model_name . "</button>
		    </form>global $site_path;
		</div>";
        }
        if (count($results) === 0) {
            $string .= "There are no packages available at the moment.";
        }
        $string .= "</div>";

        return $string;
    }
}

