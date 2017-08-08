<?php

/**
 * Description of cart_item
 *
 * @author lepercon
 */

class cart_item
{
    public $cart_item_id;
    public $cart_id;
    public $purchasable_class;
    public $purchasable_class_id;
    public $quantity;
    public $resultant_class_id;
    public $cart_item_priced_as_patron_type_id;

    /**
     * @var cart_item
     */
    public $has_reaction_with_cart_item;
    public $has_reaction_amount;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'cart_item', 'cart_item', 'cart_item_id');
        $this->db_interface->class_property_exclusions = array('has_reaction_with_cart_item', 'has_reaction_amount');
        if ($id !== null) {
            $this->get_cart_item($id);
        }
    }

    /**
     * @param user $user
     * @return float
     */
    public function get_price($user)
    {
        $cls = $this->purchasable_class;
        $item = new $cls($this->purchasable_class_id);
        return $item->get_price($user);
    }

    public function get_quantity()
    {
        $cls = $this->purchasable_class;
        $item = new $cls($this->purchasable_class_id);
        return $item->get_quantity();
    }

    public function react_with_items($items, $is_test = false)
    {
        $cls = $this->purchasable_class;
        $item = new $cls($this->purchasable_class_id);
        return $item->react_with_items($items, $is_test);
    }

    public function do_precheckout()
    {
        $cls = $this->purchasable_class;
        $item = new $cls($this->purchasable_class_id);
        return $item->do_precheckout_processing();
    }

    public function get_cart_item($id)
    {
        return $this->db_interface->get($id);
    }

    public function set_cart_item()
    {
        return $this->db_interface->set();
    }

    public function delete()
    {
        return $this->db_interface->delete($this->cart_item_id);
    }

    public function __toString()
    {
        return "item #" . $this->cart_item_id . " " . $this->purchasable_class . "(" . $this->purchasable_class_id . ") x " . $this->quantity;
    }

    public function get_cart_object()
    {
        $cls = $this->purchasable_class;
        /* @var $obj purchasable */
        $obj = new $cls($this->purchasable_class_id);
        return $obj;
    }


    static public function get_cart_item_id($cart_item)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM cart_item WHERE purchasable_class = " . db_escape($cart_item->purchasable_class) . " AND purchasable_class_id = " . db_escape($cart_item->purchasable_class_id));
        if (count($results)) {
            return $results[0]['cart_item_id'];
        } else {
            return false;
        }
    }

    static public function get_cart_item_from_class_id($purchasable_class, $purchasable_class_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT cart_item_id FROM cart_item WHERE purchasable_class = " . db_escape($purchasable_class) . " AND purchasable_class_id = " . db_escape($purchasable_class_id));
        if (count($results)) {
            return new cart_item($results[0]['cart_item_id']);
        } else {
            return null;
        }
    }

    static public function get_cart_item_from_resultant_class_id($purchasable_class, $resultant_class_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT cart_item_id FROM cart_item WHERE purchasable_class = " . db_escape($purchasable_class) . " AND resultant_class_id = " . db_escape($resultant_class_id));
        if (count($results)) {
            return new cart_item($results[0]['cart_item_id']);
        } else {
            return null;
        }
    }
}
