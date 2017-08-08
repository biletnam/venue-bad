<?php

require_once 'boffice_config.php';
if (!boffice_logged_in()) {
    header("location: $site_login_url?target_url=myaccount.php");
    exit();
}

$user = user::current_user();

$reservations_string = "";
foreach ($user->reservations(true) as $reservation) {
    $instance = $reservation->get_show_instance();
    $reservations_string .= "<li><span class='title'>" . $instance->title . "</span>: ";
    if ($instance->seating_chart_id > 0) {
        $seats = $reservation->get_seats();
        $reservations_string .= count($seats) . " seat" . (count($seats) > 1 ? "s" : "") . "<ul>";
        foreach ($seats as $seat) {
            $ticket = reservation_ticket::get_latest_valid_ticket_from_seat_instance_id($seat->purchasable_seat_instance_id);
            $reservations_string .= "<li>" . $seat->get_readible_name(false);
            if ($ticket !== null) {
                $reservations_string .= "<a href=\"//" . $site_domain . $site_path . "receipt/ticket/" . $ticket->reservation_ticket_robot_url . "/" . $ticket->reservation_ticket_robot_barcode . "/" . $ticket->purchasable_seat_instance_id . "\" target='_blank'>View/Print Ticket</a>";
                //http://localhost/boffice/receipt/ticket/thisistheroboturl/thisistherobotid/2
            }
            $reservations_string .= "</li>";
        }
        $reservations_string .= "</ul>";
    } else {
        $cart_item = $reservation->get_cart_item();
        $reservations_string .= $cart_item->quantity . " seat" . ($cart_item->quantity > 1 ? "s" : "");
    }

    $reservations_string .= "</li>";

}
if ($reservations_string !== "") {
    $string = "<div class='reservations'><h2>Reservations</h2><ul>" . $reservations_string . "</ul></div>";
} else {
    $string = "<div class='reservations'><h2>Reservations</h2><p>You have no reservations.</p></div>";
}


if ($user->user_is_volunteer === '1') {
    $string .= "<div class='volunteering'><h2>Show Workers and Volunteering</h2>" . $user->show_instance_worker_status_string() . "</div>";
}

boffice_html::$html_body_regions[] = new boffice_html_region($string);

echo boffice_template_simple("My Account");

