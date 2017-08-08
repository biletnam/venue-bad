<?php


/**
 * Get an array of days or hours whose values are the number of tickets sold for a show
 * @param int $show_id
 * @param bool $round_to_days
 * @return array
 */
function boffice_stat_preorder_time_by_show($show_id, $round_to_days)
{
    $return = array();
    foreach (show::get_instances($show_id, true) as $instance) {
        $return = boffice_array_sub_sum(array($return, boffice_stat_preorder_time_by_instance($instance->show_instance_id, $round_to_days)));
    }
    ksort($return);
    return $return;
}


/**
 * Get an array of days or hours whose values are the number of tickets sold for an instance
 * @param int $instance_id
 * @param bool $round_to_days
 * @return array
 */
function boffice_stat_preorder_time_by_instance($instance_id, $round_to_days = false)
{
    $instance = new show_instance($instance_id);
    $return = array();
    foreach ($instance->get_reservations() as $reservation) {
        $seconds = strtotime($instance->datetime) - strtotime($reservation->datetime);
        $hours = 0 - round($seconds / 60 / 60);
        if ($round_to_days) {
            $hours = $hours / 24;
        }
        if (isset($return[$hours])) {
            $return[$hours]++;
        } else {
            $return[$hours] = 1;
        }
    }
    return $return;
}

/**
 * Sum all the payment_finacial_details for all transactions on a given day
 * @param string $day of the form Y-m-d
 * @param string $filter_by_purchasable_class i.e. purchasable_seat_instance, purchasable_seating_general, purchasable_registration_instance, purchasable_package_model, etc...
 * @return float
 */
function boffice_stat_gross_income_by_day($day, $filter_by_purchasable_class = null)
{
    $sum = 0.0000;
    foreach (transaction::transactions_for_day($day) as $transaction) {
        $details = transaction::payment_finacial_details($transaction->transaction_id, $filter_by_purchasable_class);
        if ($details) {
            $sum += $details->payment_finacial_details_amount;
        }

    }
    return $sum;
}


/**
 *
 * @param string $range_start of type Y-m-d
 * @param string $range_end of type Y-m-d
 * @param string $filter_by_purchasable_class i.e. purchasable_seat_instance, purchasable_seating_general, purchasable_registration_instance, purchasable_package_model, etc...
 * @return array
 */
function boffice_stat_gross_income_date_range($range_start, $range_end, $filter_by_purchasable_class = null)
{
    $array = array();
    $time_i = strtotime($range_start . " 00:00:00");
    if (strtotime($range_start . " 00:00:00") >= strtotime($range_end . " 00:00:00")) {
        $temp = $range_end;
        $range_end = $range_start;
        $range_start = $temp;
    }
    while ($time_i < strtotime($range_end . " 23:59:59")) {
        $array[date("Y-m-d", $time_i)] = boffice_stat_gross_income_by_day(date("Y-m-d", $time_i), $filter_by_purchasable_class);
        $time_i += 24 * 60 * 60;
    }
    return $array;
}


