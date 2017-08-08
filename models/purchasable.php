<?php

/*
 * License
 */

/**
 * Description of purchasable_items
 *
 * @author Jason Steelman
 */
abstract class purchasable
{
    public $purchasable_id;
    public $purchasable_price;
    public $purchasable_quantity;
    public $purchasable_tax_rate;

    public $purchasable_item_type;
    const ITEM_TYPE_SEAT = "ITEM_TYPE_SEAT";
    const ITEM_TYPE_SEAT_GENERAL = "ITEM_TYPE_SEAT_GENERAL";
    const ITEM_TYPE_REGISTRATION = "ITEM_TYPE_REGISTRATION";
    const ITEM_TYPE_GIFTCARD = "ITEM_TYPE_GIFTCARD";
    const ITEM_TYPE_PACKAGE = "ITEM_TYPE_PACKAGE";
    const ITEM_TYPE_MERCHANDISE = "ITEM_TYPE_MERCHANDISE";
    const ITEM_TYPE_DONATION = "ITEM_TYPE_DONATION";

    public $purchasable_status;
    const ITEM_STATUS_AVAILABLE = "ITEM_STATUS_AVAILABLE";
    const ITEM_STATUS_UNAVAILABLE = "ITEM_STATUS_UNAVAILABLE";
    const ITEM_STATUS_SOLDOUT = "ITEM_STATUS_SOLDOUT";

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable', 'purchasable', 'purchasable_id');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get_price($user)
    {
        $user->user_id; //keeps NB from whinning
        return $this->purchasable_price;
    }

    /**
     * Should always be quantity available unless we're talking giftcards or donations
     * @return int
     */
    public function get_quantity()
    {
        return $this->quantity;
    }

    public function get_tax_rate()
    {
        return $this->tax_rate;
    }

    public function get_readible_name()
    {
        return "unnamed";
    }

    public function react_with_items($items, $is_test = false)
    {
        isset($items); //NB whine prevention
        isset($is_test); //NB whine prevention
        return 0;
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

    }

    public function is_available()
    {
        return $this->get_quantity() > 0 AND $this->purchasable_status === purchasable::ITEM_STATUS_AVAILABLE;
    }

    public function get_status_statement($include_quantities = true)
    {
        $string = "";
        if ($this->get_quantity() === 0) {
            $string = "Sold Out";
        } else if ($this->purchasable_status === purchasable::ITEM_STATUS_UNAVAILABLE) {
            $string = "Unavailable";
        } else if ($this->purchasable_status === purchasable::ITEM_STATUS_SOLDOUT) {
            $string = "Sold Out";
        } else {
            if ($include_quantities) {
                $string = $this->get_quantity() . " available";
            } else {
                $string = "Available";
            }

        }
        return $string;
    }
}
