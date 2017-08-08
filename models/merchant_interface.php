<?php
/**
 * Description of merchant
 *
 * @author lepercon
 */

class merchant_interface
{
    public $amount;
    public $card_number;
    public $exp_month;
    public $exp_year;
    public $cvc;
    public $invoice_id;
    public $last_name;
    public $first_name;
    public $address;
    public $zip;
    public $last_error;
    public $vendor_invoice_id;
    public $vendor_authorization_code;
    public $payment_method_according_to_vendor;
    public $vendor;
    public $transaction_id; //created on successful transaction, this is "our" transaction id, not the vendor's

    public function __construct()
    {

    }

    public function charge()
    {
        die("Cannot call charge on sudo-abstract interface 'merchant_interface'.");
    }

    public function charge_from_swipe($line1, $line2)
    {
        unset($line1, $line2);
        die("cannot charge card from here");
    }

    public function refund($transaction_id, $amount)
    {
        unset($transaction_id, $amount);
        die("Cannot refund on abstract class merchant_interface.");
    }

    public function update_settlement($transaction_id)
    {
        unset($transaction_id);
        die("Cannot update settlement data on abstract class merchant_interface.");
    }

    public function create_finacial_details($transaction_id = 0)
    {
        $deets = new payment_finacial_details();
        $deets->card_expiry = $this->exp_month . "/" . $this->exp_year;
        $deets->card_last_4 = substr($this->card_number, -4);
        $deets->our_invoice_id = $this->invoice_id;
        $deets->vendor = $this->vendor;
        $deets->vendor_invoice_id = $this->vendor_invoice_id;
        $deets->card_address_line1 = $this->address;
        $deets->card_address_zip = $this->zip;
        $deets->vendor_invoice_id = $this->vendor_invoice_id;
        $deets->card_first_name = $this->first_name;
        $deets->card_last_name = $this->last_name;
        $deets->vendor_authorization_code = $this->vendor_authorization_code;
        $deets->payment_method = $this->payment_method_according_to_vendor;
        $deets->transaction_id = $transaction_id;
        $deets->payment_finacial_details_amount = $this->amount;

        if ($deets->card_last_4 > 0) {
            $deets->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING;
        } else {
            $deets->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_NONTRANSIENT;
        }

        if (isset($_SESSION['boffice']['terminal']['cart_id'])) {
            $user = user::current_user();
            $deets->terminal = filter_input(INPUT_SERVER, 'REMOTE_ADDR') . " :: " . $user->user_name_last . ", " . $user->user_name_first . " " . $user->user_id;
        } else {
            $deets->terminal = 'web';
        }

        $deets->set();

        return $deets->payment_finacial_details_id;
    }

    public function last_error()
    {
        return $this->last_error;
    }


}
