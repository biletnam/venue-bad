<?php

/**
 * This API should be limited to commands available to the general public and volunteers.
 */

require_once '../boffice_config.php';
global $global_conn;

$user = user::current_user();

$command = filter_input(INPUT_POST, 'command');

if ($command === "volunteer_information") {
    $show_instance_worker_id = filter_input(INPUT_POST, 'show_instance_worker_id');
    if ($show_instance_worker_id < 1) {
        die("ERROR. Need a show_instance_worker_id.");
    }
    $show_instance_worker = new show_instance_worker($show_instance_worker_id);
    $string = "
	<form class='volunteer-signup' method='POST' action='#'>
	    <div class='form-row'>
		<label for='worker-type-name'>Job Type</label>
		<div id='worker-type-name' class='style-as-form-input'>" . $show_instance_worker->show_instance_worker_type->show_instance_worker_type_name . "</div>
	    </div>
	    <div class='form-row'>
		<label for='worker-type-desc'>Job Description</label>
		<div id='worker-type-desc' class='style-as-form-input'>" . $show_instance_worker->show_instance_worker_type->show_instance_worker_type_description . "</div>
	    </div>
	    <div class='form-row'>
		<label for='worker-type-prereq'>Job Requirements</label>
		<div id='worker-type-prereq' class='style-as-form-input'>" . $show_instance_worker->show_instance_worker_type->show_instance_worker_type_requirements . "</div>
	    </div>
	    <div class='form-row'>
		<label for='signup'>&nbsp;</label>
		<button type='SUBMIT' value='Sign Up'>Sign Up</button>
	    </div>
	    <input type='hidden' name='show_instance_worker_id' value='" . htmlspecialchars($show_instance_worker_id) . "' />	    
	</form>";


} else if ($command === "volunteer_signup") {
    $show_instance_worker_id = filter_input(INPUT_POST, 'show_instance_worker_id');
    if ($show_instance_worker_id < 1) {
        die("ERROR. Need a show_instance_worker_id.");
    }
    $show_instance_worker = new show_instance_worker($show_instance_worker_id);
    if ($show_instance_worker->show_instance_worker_status === show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_UNFILLED) {
        show_instance_worker::fill($show_instance_worker_id, $user->user_id, '0');
        $string = "Success";
    } else {
        $string = "Failed. That position is not available.";
    }


} else if ($command === "volunteer_cancel") {
    $show_instance_id = filter_input(INPUT_POST, 'show_instance_id');
    if ($show_instance_id < 1 AND $user->user_id < 1) {
        die("ERROR. Need a show_instance_id and valid user");
    }
    $results = db_query($global_conn, "SELECT * FROM show_instance_workers WHERE user_id = " . db_escape($user->user_id) . " AND show_instance_id = " . db_escape($show_instance_id));
    $show_instance_worker = new show_instance_worker($results[0]['show_instance_worker_id']);
    if ($show_instance_worker->show_instance_worker_status === show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_FILLED) {
        show_instance_worker::unfill($show_instance_worker->show_instance_worker_id);
        $string = "1";
    } else {
        $string = "Failed. That is not your job to cancel. Alert the Guards!";
    }


} else if ($command === "donation_add") {
    if ($user->user_id > 0) {
        $cart = cart::cart_from_user_id($user->user_id);
        $donation = purchasable_donation::create($user->user_id, filter_input(INPUT_POST, 'donation_amount'), filter_input(INPUT_POST, 'donation_message'));
        $cart->cart_item_new("purchasable_donation", $donation->purchasable_donation_id, '1');
        $string = "1";
    } else {
        $string = "Need to login first.";
    }


} else {
    $string = "no_command";
}

if (isset($string) AND is_string($string) AND $string !== "") {
    echo $string;
}