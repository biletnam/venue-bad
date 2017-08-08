<?php

/**
 * Description of reservation
 *
 * @author lepercon
 */
class reservation extends transaction
{
    public $transaction_id; //parent
    public $reservation_id;
    public $reservation_note;

    public $reservation_status;
    const RESERVATION_STATUS_ACTIVE = "ACTIVE";
    const RESERVATION_STATUS_CANCELLED = "CANCELLED";
    const RESERVATION_STATUS_REDEEMED = "REDEEMED";
    const RESERVATION_STATUS_EXPIRED_NOT_REDEEMED = "EXPIRED_NOT_REDEEMED";

    private $db_interface;

    public function __construct($reservation_id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'reservation', 'reservation', 'reservation_id');
        parent::__construct();
        if ($reservation_id !== null) {
            $this->get($reservation_id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        parent::get_transaction($this->transaction_id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function __toString()
    {
        return "Reseravtion: " . $this->reservation_id . " (" . $this->reservation_status . ")";
    }

    public function get_seats()
    {
        global $global_conn;
        $results = db_query($global_conn, "
	    SELECT * FROM 
		transaction 
		LEFT JOIN cart_item USING (cart_id)
		LEFT JOIN purchasable_seat_instance ON (cart_item.purchasable_class_id = purchasable_seat_instance.purchasable_seat_instance_id)
	    WHERE transaction.transaction_id = " . db_escape($this->transaction_id) . " 
		AND cart_item.purchasable_class = 'purchasable_seat_instance'
		AND cart_item.resultant_class_id = " . db_escape($this->reservation_id) . " 
	");
        $return = array();
        foreach ($results as $item) {
            $return[] = new purchasable_seat_instance($item['purchasable_seat_instance_id']);
        }
        return $return;
    }

    /**
     * @return \show_instance
     */
    public function get_show_instance()
    {
        $seats = $this->get_seats();
        if (count($seats)) {
            //Reserved Seating
            $seat0 = $seats[0];
            $show_instance = new show_instance($seat0->show_instance_id);
        } else {
            //General Seating
            $cart_item = cart_item::get_cart_item_from_resultant_class_id('purchasable_seating_general', $this->reservation_id);
            $purchasable_seating_general = new purchasable_seating_general($cart_item->purchasable_class_id);
            $show_instance = $purchasable_seating_general->show_instance;
        }
        return $show_instance;
    }

    public function get_cart_item()
    {
        if ($this->get_show_instance()->seating_chart_id > 0) {
            return cart_item::get_cart_item_from_resultant_class_id('purchasable_seat_instance', $this->reservation_id);
        } else {
            return cart_item::get_cart_item_from_resultant_class_id('purchasable_seating_general', $this->reservation_id);
        }
    }

    public function admin_edit_form()
    {
        global $global_conn;
        $elements = array(
            new f_data_element('Note', 'reservation_note', 'wysiwyg'),
        );
        $f = new f_data($global_conn, 'reservation', 'reservation_id', $elements, $this->reservation_id);
        $f->allow_delete = false;
        return $f->start();
    }

    static public function get_reseravtion_by_seat_instance_id($purchasable_seat_instance_id)
    {
        global $global_conn;
        $q = "SELECT * FROM cart_item WHERE purchasable_class = 'purchasable_seat_instance' AND purchasable_class_id = " . db_escape($purchasable_seat_instance_id);
        $results = db_query($global_conn, $q);
        $reservation = new reservation($results[0]['resultant_class_id']);
        return $reservation;
    }

    public function cancel($refresh_seating_chart = true)
    {
        global $global_conn;
        db_exec($global_conn, "UPDATE reservation SET reservation_status = 'CANCELLED' WHERE reservation_id = " . db_escape($this->reservation_id));

        $package_usages = db_query($global_conn, "SELECT * FROM package_usage WHERE transaction_id = " . db_escape($this->transaction_id));

        $user = new user($this->user_id);
        $refund_amount = 0;
        $i = 0;
        foreach ($this->get_seats() as $seat) {
            $seat->seat_status = purchasable_seat_instance::SEAT_STATUS_AVAILABLE;
            $seat->set();
            if ($refresh_seating_chart) {
                $show_instance = new show_instance($seat->show_instance_id);
                $show_instance->seating_chart_html_update();
            }

            if ($i < count($package_usages)) {
                $usage = new package_usage($package_usages[$i]['package_usage_id']);
                $usage->package_usage_deduction = 0;
                $usage->set();
                $i++;
            } else {
                $refund_amount += $seat->get_price($user);
            }
        }

        if ($refund_amount > 0) {
            $user->user_account_value += $refund_amount;
            $user->user_note .= "<p>Added $" . $refund_amount . " to this users account balance for cancelled reservation #" . $this->reservation_id . "</p>";
            $user->set();
        }

        return "Reset $i package usages and $" . money($refund_amount);
    }
}
