<?php

/**
 * Description of package_model
 *
 * @author lepercon
 */

class purchasable_package_model extends purchasable
{
    public $package_model_id;
    public $package_model_name;
    public $package_model_description;
    public $package_model_date_available;
    public $package_model_date_close;
    public $package_model_duration_in_days;
    public $package_model_is_single_use;
    public $package_model_cost;
    public $package_model_patron_type_id;

    public $benefits;

    private $db_interface;

    public function __construct($id = null)
    {

        $this->db_interface = new boffice_standard_interface($this, 'purchasable_package_model', 'purchasable_package_model', 'package_model_id');
        $this->db_interface->class_property_exclusions = array('benefits');
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function set()
    {
        return $this->db_interface->set();
    }

    public function delete()
    {
        return $this->db_interface->delete($this->package_model_id);
    }

    public function get_quantity()
    {
        return 99;
    }

    public function get_readible_name()
    {
        return $this->package_model_name;
    }

    public function get_price($user)
    {
        return $this->package_model_cost;
    }

    public function get($id)
    {
        global $global_conn;
        $return = $this->db_interface->get($id);
        $this->benefits = array();
        foreach (db_query($global_conn, "SELECT * FROM purchasable_package_model_benefit WHERE package_model_id = " . db_escape($id)) as $bene) {
            $this->benefits[] = new purchasable_package_model_benefit($bene['package_model_benefit_id']);
        }
        return $return;
    }


    public function react_with_items($items, $is_test = false)
    {
        $temp_package = new package();
        $temp_package->benefits = $this->benefits;
        return $temp_package->react_with_items($items, $is_test);
    }

    public function do_precheckout_processing()
    {
        //more like an interface...
    }

    /**
     *
     * @param array $items current cart's cart->items
     * @param cart_item $cart_item
     * @param int $transaction_id
     */
    public function do_process($items, $cart_item, $transaction_id = 0)
    {
        if ($cart_item->cart_item_id === null AND $cart_item->purchasable_class_id > 0) {
            $cart_item->set_cart_item();
        }

        $package = new package();
        $package->cart_item_id = $cart_item->cart_item_id;
        $package->set();

        $cart_item->resultant_class_id = $package->package_id;
        $new_package = new package($package->package_id);

        $new_package->do_process($items, $cart_item, $transaction_id);
    }

    public function is_available()
    {
        return strtotime($this->package_model_date_available) < time() AND strtotime($this->package_model_date_close) > time();
    }

    public function get_status_statement($include_quantities = true)
    {
        $string = "";
        if ($this->is_available()) {
            $string .= "Available";
        } else {
            $string .= "Not Available";
        }
        return $string;
    }

    static public function get_packages_available($restricted_to_sales_window = true, $restrict_to_patron_type_id = 0)
    {
        global $global_conn;
        $return = array();
        $where = " 1 ";
        if ($restricted_to_sales_window) {
            $where .= " AND package_model_date_available < NOW() AND package_model_date_close > NOW() ";
        }
        if (intval($restrict_to_patron_type_id) > 0) {
            $where .= " AND package_model_patron_type_id = " . db_escape($restrict_to_patron_type_id) . " ";
        }
        foreach (db_query($global_conn, "SELECT package_model_id FROM purchasable_package_model WHERE " . $where) as $row) {
            $return[] = new purchasable_package_model($row['package_model_id']);
        }
        return $return;
    }
}

