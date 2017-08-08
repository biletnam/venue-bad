<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of transaction
 *
 * @author lepercon
 */
class transaction
{
    public $transaction_id;

    public $datetime;
    public $user_id;
    public $cart_id;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'transaction', 'transaction', 'transaction_id');
        $this->db_interface->class_property_exclusions = array('payment_finacial_detals');
        if ($id != null) {
            $this->get_transaction($id);
        }
    }

    public function get_transaction($id)
    {
        $this->db_interface->get($id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    static public function transactions_by_user($user_id)
    {
        global $global_conn;
        $return = array();
        foreach (db_query($global_conn, "SELECT * FROM transaction WHERE user_id = " . db_escape($user_id)) as $i) {
            $return[] = new transaction($i['transaction_id']);
        }
        return $return;
    }

    static public function transaction_for_cart($cart_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT transaction_id FROM transaction WHERE cart_id = " . db_escape($cart_id));
        if (count($results) === 1) {
            return new transaction($results[0]['transaction_id']);
        } else {
            return null;
        }
    }

    /**
     * Alias of payment_finacial_details::payment_finacial_details_from_transaction_id
     * @param int $transaction_id
     * @return \payment_finacial_details
     */
    static public function payment_finacial_details($transaction_id)
    {
        return payment_finacial_details::payment_finacial_details_from_transaction_id($transaction_id);
    }

    /**
     * Get an array of transactions for a date range
     * @param int $start_time
     * @param int $end_time
     * @param string $filter_by_purchasable_class i.e. purchasable_seat_instance, purchasable_seating_general, purchasable_registration_instance, purchasable_package_model, etc...
     * @return array
     */
    static public function transactions_for_date_range($start_time, $end_time, $filter_by_purchasable_class = null)
    {
        global $global_conn;
        $return = array();
        if ($filter_by_purchasable_class === null) {
            $query = "SELECT transaction_id FROM transaction WHERE datetime >= " . db_escape(date("Y-m-d H:i:s", $start_time)) . " AND datetime <= " . db_escape(date("Y-m-d H:i:s", $end_time));
        } else {
            $query = "
		SELECT transaction_id 
		FROM transaction 
		LEFT JOIN cart_item USING (cart_id)
		WHERE datetime >= " . db_escape(date("Y-m-d H:i:s", $start_time)) . " AND datetime <= " . db_escape(date("Y-m-d H:i:s", $end_time)) . "
		    AND cart_item.purchasable_class = " . db_escape($filter_by_purchasable_class) . "
	    ";
        }
        foreach (db_query($global_conn, $query) as $row) {
            $return[] = new transaction($row['transaction_id']);
        }
        return $return;
    }

    /**
     * Get an array of transactions that occur on $day
     * @param string $day of the form Y-m-d
     * @param string $filter_by_purchasable_class i.e. purchasable_seat_instance, purchasable_seating_general, purchasable_registration_instance, purchasable_package_model, etc...
     * @return array
     */
    static public function transactions_for_day($day, $filter_by_purchasable_class = null)
    {
        return transaction::transactions_for_date_range(strtotime($day . "00:00:00"), strtotime($day . "23:59:59"), $filter_by_purchasable_class);
    }

    /**
     * Get an array of transactions for the past 30 days
     * @param string $filter_by_purchasable_class i.e. purchasable_seat_instance, purchasable_seating_general, purchasable_registration_instance, purchasable_package_model, etc...
     * @return type
     */
    static public function transaction_for_past_30days($filter_by_purchasable_class = null)
    {
        return transaction::transactions_for_date_range(strtotime("-30 days"), time(), $filter_by_purchasable_class);
    }
}


