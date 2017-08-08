<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of merchant_authorize
 *
 * @author lepercon
 */
class merchant_authorize_dot_net extends merchant_interface
{
    CONST MERCHANT_AUTHORIZE_DOT_NET = "MERCHANT_AUTHORIZE_DOT_NET";

    public function __construct()
    {
        $this->vendor = merchant_authorize_dot_net::MERCHANT_AUTHORIZE_DOT_NET;
        parent::__construct();
    }

    public function charge()
    {
        global $merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key, $merchant_authorize_dot_net_sandbox;
        $transaction = new AuthorizeNetAIM($merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key);
        $transaction->setSandbox($merchant_authorize_dot_net_sandbox);
        $transaction->amount = $this->amount;
        $transaction->card_num = $this->card_number;
        $transaction->exp_date = $this->exp_month . "/" . $this->exp_year;
        $transaction->card_code = $this->cvc;
        $transaction->duplicate_window = '15';
        $transaction->invoice_num = $this->invoice_id;
        $response = $transaction->authorizeAndCapture();

        if ($response->approved) {
            $this->vendor_invoice_id = $response->transaction_id;
            $this->vendor_authorization_code = $response->authorization_code;
            $this->payment_method_according_to_vendor = $response->card_type;
            return true;
        } else {
            $this->last_error = $response->response_reason_text;
            return false;
        }
    }

    public function charge_from_swipe($line1, $line2)
    {
        global $merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key, $merchant_authorize_dot_net_sandbox;
        $merchant = new AuthorizeNetCP($merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key);
        $merchant->setSandbox($merchant_authorize_dot_net_sandbox);
        $merchant->setTrack1Data($line1);
        $merchant->setTrack2Data($line2);
        $merchant->amount = $this->amount;
        $merchant->invoice_num = $this->invoice_id;
        $response = $merchant->authorizeAndCapture();

        if ($response->approved) {
            $this->vendor_invoice_id = $response->transaction_id;
            $this->vendor_authorization_code = $response->authorization_code;
            $this->payment_method_according_to_vendor = $response->card_type;
            return true;
        } else {
            $this->last_error = $response->response_reason_text;
            return false;
        }
    }

    public function refund($transaction_id, $amount)
    {
        global $merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key, $merchant_authorize_dot_net_sandbox;
        $payment_details = transaction::payment_finacial_details($transaction_id);

        if ($payment_details->vendor !== $this->vendor) {
            die("Cannot process refund on vendor " . $payment_details->vendor . " while working with " . $this->vendor);
        }
        if ($payment_details->card_last_4 === "" or strlen($payment_details->card_last_4) < 4) {
            die("Cannot process refund on card because the card's last 4 digits were not logged properly.");
        }
        if ($amount < 0.00001) {
            die("Cannot process refund on non-positive amount of $amount.");
        }
        if ($amount > $payment_details->payment_finacial_details_amount) {
            die("Cannot refund more than " . $payment_details->payment_finacial_details_amount . " - $amount requested.");
        }

        $merchant = new AuthorizeNetAIM($merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key);
        $merchant->setSandbox($merchant_authorize_dot_net_sandbox);
        if ($payment_details->vendor_batch_id === "" OR $payment_details->vendor_batch_id === "0") {
            //Performing a void instead
            if (intval($payment_details->payment_finacial_details_amount) !== intval($amount)) {
                $this->last_error = "This transaction has not been processed and we cannot refund a partial amount. Resubmit the request with the amount set to the original settlement amount to void the transaction.";
                return false;
            }
            $response = $merchant->void($payment_details->vendor_invoice_id);
            if ($response->approved) {
                $payment_details->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING;
                $payment_details->payment_finacial_details_amount = 0;
                $payment_details->set();
            }
        } else {
            //Performing a refund/credit
            $response = $merchant->credit($payment_details->vendor_invoice_id, $amount, $payment_details->card_last_4);
            if ($response->approved) {
                $new_transaction = new transaction($transaction_id);
                $new_transaction->transaction_id = null;
                $new_transaction->set();
                $new_details = new payment_finacial_details();
                $new_details->payment_finacial_details_id = null;
                $new_details->transaction_id = $new_transaction->transaction_id;
                $new_details->payment_finacial_details_amount = 0 - intval($amount);
                $new_details->vendor_authorization_code = $response->authorization_code;
                $new_details->vendor_batch_date = "0000-00-00 00:00:00";
                $new_details->vendor_batch_id = "";
                $new_details->vendor_gateway_fee = 0;
                $new_details->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING;
                $new_details->set();
            }
        }


        if ($response->approved) {
            return true;
        } else {
            $this->last_error = $response->response_reason_text;
            return false;
        }
    }


    public function update_settlement($transaction_id)
    {
        global $merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key, $merchant_authorize_dot_net_sandbox;
        $merchant = new AuthorizeNetTD($merchant_authorize_dot_net_id, $merchant_authorize_dot_net_key);
        $merchant->setSandbox($merchant_authorize_dot_net_sandbox);
        $payment_details = transaction::payment_finacial_details($transaction_id);
        $vendor_id = $payment_details->vendor_invoice_id;
        $response = $merchant->getTransactionDetails($vendor_id);
        $status = $response->xml->transaction->transactionStatus . "";

        $approved_array = array(
            'approvedReview',
            'settledSuccessfully',
            'returnedItem',
        );
        $voided_array = array(
            'voided'
        );
        $pending_array = array(
            'authorizedPendingCapture',
            'capturedPendingSettlement',
            'refundPendingSettlement',
            'pendingFinalSettlement',
            'pendingSettlement',
            'underReview',
            'updatingSettlement',
            'authorizedPendingRelease',
            'FDSAuthorizedPendingReview',
            'FDSPendingReview'
        );
        $failed_array = array(
            'communicationError',
            'declined',
            'couldNotVoid',
            'expired',
            'generalError',
            'failedReview',
            'settlementError',
            'communicationError',
            'chargeback',
            'chargebackReversal'
        );

        if (in_array($status, $approved_array) and $payment_details->payment_finacial_details_status !== payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_APPROVED) {
            $payment_details->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_APPROVED;
            $payment_details->vendor_batch_id = $response->xml->transaction->batch->batchId . "";
            $payment_details->vendor_batch_date = date("Y-m-d H:i:s", strtotime($response->xml->transaction->batch->settlementTimeLocal . ""));
            $payment_details->set();
            return $payment_details->payment_finacial_details_status;
        } else if (in_array($status, $failed_array) AND $payment_details->payment_finacial_details_status !== payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_FAILED) {
            $payment_details->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_FAILED;
            $payment_details->set();
            return $payment_details->payment_finacial_details_status;
        } else if (in_array($status, $pending_array) and $payment_details->payment_finacial_details_status !== payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING) {
            $payment_details->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING;
            $payment_details->set();
            return $payment_details->payment_finacial_details_status;
        } else if (in_array($status, $voided_array)) {
            $payment_details->payment_finacial_details_status = payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_VOIDED;
            $payment_details->set();
        } else {
            return false;
        }

    }
}
