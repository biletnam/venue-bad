<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of payment_finacial_details
 *
 * @author lepercon
 */
class payment_finacial_details
{
    public $payment_finacial_details_id;
    public $transaction_id;
    public $payment_finacial_details_amount;
    public $payment_finacial_details_status;
    const PAYMENT_FINACIAL_DETAILS_STATUS_APPROVED = "APPROVED";
    const PAYMENT_FINACIAL_DETAILS_STATUS_FAILED = "FAILED";
    const PAYMENT_FINACIAL_DETAILS_STATUS_PENDING = "PENDING";
    const PAYMENT_FINACIAL_DETAILS_STATUS_NONTRANSIENT = "NONTRANSIENT";
    const PAYMENT_FINACIAL_DETAILS_STATUS_VOIDED = "VOIDED";
    const PAYMENT_FINACIAL_DETAILS_STATUS_REFUNDED = "REFUNDED";

    public $card_last_4;
    public $card_expiry;
    public $card_last_name;
    public $card_first_name;
    public $card_address_line1;
    public $card_address_zip;

    public $our_invoice_id;

    public $vendor;
    public $vendor_batch_id;
    public $vendor_invoice_id;
    public $vendor_batch_date;
    public $vendor_gateway_fee;
    public $vendor_authorization_code;

    public $terminal;

    public $payment_method;
    const PAYMENT_METHOD_VISA = "Visa";
    const PAYMENT_METHOD_MASTER_CARD = "MasterCard";
    const PAYMENT_METHOD_DINERS_CLUB = "Diners";
    const PAYMENT_METHOD_AMEX = "AmericanExpress";
    const PAYMENT_METHOD_DISCOVER = "Discover";
    const PAYMENT_METHOD_CASH = "Cash";
    const PAYMENT_METHOD_CHECK = "Check";
    const PAYMENT_METHOD_GIFTCARD = "Giftcard";
    const PAYMENT_METHOD_COMP = "Comp";
    const PAYMENT_METHOD_LEGACY = "Legacy";

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'payment_finacial_details', 'payment_finacial_details', 'payment_finacial_details_id');
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

    static public function payment_finacial_details_from_transaction_id($transaction_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT payment_finacial_details_id FROM payment_finacial_details WHERE transaction_id = " . db_escape($transaction_id));
        if (count($results)) {
            return new payment_finacial_details($results[count($results) - 1]['payment_finacial_details_id']);
        } else {
            return null;
        }
    }

}
