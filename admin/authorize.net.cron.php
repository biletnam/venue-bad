<?php

require('../boffice_config.php');


$approved = 0;
$pending = 0;
$failed = 0;
$errored = 0;
$total = 0;
$merchant = new merchant_authorize_dot_net();

global $global_conn;


foreach (db_query($global_conn, "SELECT * FROM payment_finacial_details WHERE payment_finacial_details_status = " . db_escape(payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING)) as $row) {
    set_time_limit(90);
    $transaction = new transaction($row['transaction_id']);
    $details = transaction::payment_finacial_details($transaction->transaction_id);
    if ($details->vendor_invoice_id < 1) {
        $errored++;
    } else {
        $answer = $merchant->update_settlement($transaction->transaction_id);
        if ($answer === payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_APPROVED) {
            $approved++;
        } else if ($answer === payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_PENDING) {
            boffice_notice::create("A previously created transaction has beed set to 'pending' by our payment processor.", boffice_notice::BOFFICE_NOTICE_SEVERITY_HIGH, $user_id, $reservation_id);
            $pending++;
        } else if ($answer === payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_FAILED) {
            boffice_notice::create("A transaction has failed.", boffice_notice::BOFFICE_NOTICE_SEVERITY_HIGH, $transaction->user_id);
            $failed++;
        } else if ($answer === payment_finacial_details::PAYMENT_FINACIAL_DETAILS_STATUS_VOIDED) {
            boffice_notice::create("A transaction has been voided.", boffice_notice::BOFFICE_NOTICE_SEVERITY_NORMAL, $transaction->user_id);
            $approved++;
        } else {
            $pending++;
        }
    }
    $total++;
}

boffice_notice::create("Settlement Report: " . $approved . "approved, " . $failed . "failed, " . $errored . "bad_records, " . $pending . "pending. " . $total . "total.", boffice_notice::BOFFICE_NOTICE_SEVERITY_LOW);



