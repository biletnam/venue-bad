<?php

/**
 *
 * @param reservation $reservation
 * @return string
 */
function reservation_to_string($reservation)
{
    $user = new user($reservation->user_id);

    $show_instance = $reservation->get_show_instance();

    $string = "
	<div class='reservation' reservation_id='" . $reservation->reservation_id . "'>
	    <ul>
		<li>Reservation id - " . $reservation->reservation_id . "</li>
		<li class='user' user_id='" . $user->user_id . "'>User - #" . $user->user_id . " " . $user->user_name_last . ", " . $user->user_name_first . "</li>
		<li>Created - " . date("Y-m-d g:ia", strtotime($reservation->datetime)) . "</li>
		<li>Show - " . $show_instance->title . ". Run: " . date("Y-m-d g:ia", strtotime($show_instance->datetime)) . "</li>";
    if ($show_instance->seating_chart_id > 0) {
        $string .= " 
		    <li>Seats 
			<ul>";
        foreach ($reservation->get_seats() as $seat) {
            $string .= "<li>Seat - " . $seat->get_readible_name(false) . "</li>";
        }
        $string .= "
			</ul> 
		    </li>";
    } else {
        $cart_item = $reservation->get_cart_item();
        $string .= "<li>General Seating: " . $cart_item->quantity . " seat" . ($cart_item->quantity > 1 ? "s" : "") . "</li>";
    }
    $string .= " 
		<li>Status - " . $reservation->reservation_status . "</li>
		<li class='transaction' transaction_id='" . $reservation->transaction_id . "'>Transaction - #" . $reservation->transaction_id . "</li>
	    </ul>
	    <div class='edit_form'>" . $reservation->admin_edit_form() . "</div>
	    <div class='reservation-actions'>
		<button class='cancel' reservation_id='" . $reservation->reservation_id . "'>Cancel Reservation</button>
		<button class='change-seats' reservation_id='" . $reservation->reservation_id . "'>Change Seats</button>
		<button class='change-show' reservation_id='" . $reservation->reservation_id . "'>Change Show</button>
	    </div>
	</div>";
    return $string;
}

    
    
 

