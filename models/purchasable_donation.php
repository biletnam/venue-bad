<?php

/**
 * Description of purchasable_donation
 *
 * @author lepercon
 */
class purchasable_donation extends purchasable
{
    public $purchasable_donation_id;
    public $user_id;
    public $purchasable_donation_datetime;
    public $purchasable_donation_value;
    public $purchasable_donation_note;
    public $transaction_id;
    public $purchasable_donation_status;
    const PURCHASABLE_DONATION_STATUS_PENDING = "PENDING";
    const PURCHASABLE_DONATION_STATUS_CONFIRMED = "CONFIRMED";
    const PURCHASABLE_DONATION_STATUS_CANCELLED = "CANCELLED";

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "purchasable_donation", "purchasable_donation", "purchasable_donation_id");
        parent::__construct();
        parent::get(6);
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
        isset($user);
        return $this->purchasable_donation_value;
    }

    public function get_readible_name()
    {
        return money($this->purchasable_donation_value) . " donation";
    }

    /**
     *
     * @param array $items current cart's cart->items
     * @param cart_item $cart_item
     * @param int $transaction_id
     */
    public function do_process($items, $cart_item, $transaction_id = 0)
    {
        isset($items, $cart_item);
        $this->transaction_id = $transaction_id;
        $this->purchasable_donation_datetime = date("Y-m-d H:i:s");
        $this->purchasable_donation_status = purchasable_donation::PURCHASABLE_DONATION_STATUS_CONFIRMED;
        $this->set();
        $user = new user($this->user_id);
        $user->user_is_donor = '1';
        $user->set();
        $notice = $user->user_name_first . " " . $user->user_name_last . " just donated " . money($this->purchasable_donation_value) . ". " . $this->purchasable_donation_note;
        boffice_notice::create($notice, boffice_notice::BOFFICE_NOTICE_SEVERITY_NORMAL, $user->user_id);
    }

    static public function create($user_id, $value, $note = "")
    {
        $donation = new purchasable_donation();
        $donation->user_id = $user_id;
        $donation->purchasable_donation_datetime = date("Y-m-d H:i:s");
        $donation->purchasable_donation_status = purchasable_donation::PURCHASABLE_DONATION_STATUS_PENDING;
        $donation->purchasable_donation_value = $value;
        $donation->purchasable_donation_note = $note;
        $donation->set();
        return $donation;
    }

}
