<?php
header('Access-Control-Allow-Origin: *');
require_once '../boffice_config.php';
require_once 'api.reservations.php';
require_once 'api.business.php';

$command = filter_input(INPUT_POST, 'command');
$scan = filter_input(INPUT_POST, 'scan');

header('Content-Type: text/xml');
echo "<xml>";


if ($command === "test_and_update") {
    $response = "0";
    $patron = "";
    $data_string = "";
    $seat = "";
    $ticket = reservation_ticket::get_ticket_from_barcode($scan);

    if ($ticket !== null) {
        if ($ticket->purchasable_seat_instance_id > 0) {
            $instance = new purchasable_seat_instance($ticket->purchasable_seat_instance_id);
            $seat = $instance->get_readible_name(false, true);
            $show_instance_id = $instance->show_instance_id;
            $cart_item = cart_item::get_cart_item_from_class_id("purchasable_seat_instance", $ticket->purchasable_seat_instance_id);
        } else if ($ticket->purchasable_seating_general > 0) {
            $cart_item = cart_item::get_cart_item_from_class_id("purchasable_seating_general", $ticket->purchasable_seating_general);
            $seat = $cart_item->quantity . " seat" . ($cart_item->quantity > 1 ? "s" : "");
            $general_seating_obj = $cart_item->get_cart_object();
            $show_instance_id = $general_seating_obj->show_instance_id;
        }
        $transaction = transaction::transaction_for_cart($cart_item->cart_id);
        $user = new user($transaction->user_id);
        $patron = $user->user_name_first . " " . $user->user_name_last . " (" . $user->user_id . ")";

        if ($ticket->reservation_ticket_status === reservation_ticket::RESERVATION_TICKET_STATUS_ACTIVE) {
            $response = "1";
            $data_string = "Valid Ticket";
        } else if ($ticket->reservation_ticket_status === reservation_ticket::RESERVATION_TICKET_STATUS_CANCELLED) {
            $data_string = "Cancelled";
        } else if ($ticket->reservation_ticket_status === reservation_ticket::RESERVATION_TICKET_STATUS_LOST) {
            $data_string = "Reported LOST";
        } else if ($ticket->reservation_ticket_status === reservation_ticket::RESERVATION_TICKET_STATUS_STOLEN) {
            $data_string = "Reported STOLEN";
        } else if ($ticket->reservation_ticket_status === reservation_ticket::RESERVATION_TICKET_STATUS_CHECKED_IN) {
            $updated = strtotime($ticket->reservation_ticket_updated);
            $seconds = time() - $updated;
            $minutes = floor($seconds / 60);
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
            $data_string = "Used " . $hours . "hours " . $minutes . "minutes ago";
        }

        if (filter_input(INPUT_POST, 'show_instance_id') !== (string)$show_instance_id) {
            $response = "0";
            $data_string .= " !!Wrong Show!!";
        }

        if ($response === "1") {
            //$ticket->check_in();
        }
    } else {
        $data_string = "INVALID barcode";
    }

    echo "
	<response>$response</response>
	<patron>$patron</patron>
	<data_string>$data_string</data_string>
	<seat>$seat</seat>";


} else if ($command === "get_show_name") {
    $show_instance = show::get_current_show_instance(false);
    echo "<show_name>" . htmlspecialchars($show_instance->title) . "</show_name>
	<show_instance_id>" . $show_instance->show_instance_id . "</show_instance_id>
	<time_to_show_in_seconds>" . (strtotime($show_instance->datetime) - time()) . "</time_to_show_in_seconds>";
} else if ($command === "get_scan_count") {
    echo "<scan_count>" . rand(10, 100) . "</scan_count>
	<scan_count_max>" . rand(100, 120) . "</scan_count_max>";
}


echo "</xml>";