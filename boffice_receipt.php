<?php
require_once('boffice_config.php');
boffice_initialize();

if (filter_input(INPUT_GET, 'p') === null OR filter_input(INPUT_GET, 'p') === "") {
    global $site_domain;
    header("location: //$site_domain.$site_path");
}

$p = filter_input(INPUT_GET, 'p');
if (substr($p, -1) === "/") {
    $p = substr($p, 0, -1);
}

$p_parts = explode('/', $p);

if (count($p_parts) === 1) {
    user::login_required($site_path . "receipt/" . $p_parts[0]);
    $user = user::current_user();
    $cart = new cart($p_parts[0]);
    if ($cart->user_id !== $user->user_id) {
        new boffice_error("This receipt does not belong to you.", false);
    } else {
        boffice_html::$html_body_regions[] = new boffice_html_region($cart->cart_contents_html(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
    }
    echo boffice_template_simple("Receipt");
} else if (count($p_parts) === 4 AND $p_parts[0] === 'ticket') {
    if (reservation_ticket::verify_ticket_url($p_parts[1], $p_parts[2], $p_parts[3])) {
        //sleep(rand(1,4));
        $ticket = reservation_ticket::get_ticket_from_barcode($p_parts[2]);
        $ticket->ticket_image_data();
    } else {
        new boffice_error("Sorry, we cannot validate your ticket request.");
        boffice_html::$html_body_regions[] = new boffice_html_region("", boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
        sleep(rand(3, 8));
        echo boffice_template_simple("Receipt");
    }
} else {
    new boffice_error("Sorry, we cannot find your reciept.");
    boffice_html::$html_body_regions[] = new boffice_html_region("", boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
    sleep(rand(1, 4));
    echo boffice_template_simple("Receipt");
}

