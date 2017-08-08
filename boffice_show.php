<?php
require_once('boffice_config.php');
boffice_initialize();

if (filter_input(INPUT_GET, 'p') === null) {
    global $site_domain;
    header("location: //$site_domain");
}

$p = filter_input(INPUT_GET, 'p');
if (substr($p, -1) === "/") {
    $p = substr($p, 0, -1);
}
$p_parts = explode('/', $p);

if ($p === '') {
    $show = show::get_current_show();
} else if (count($p_parts) === 1) {
    if ($p === '') {
        $show = show::get_current_show();
    } else {
        $show = show::get_show_by_url_name($p_parts[0]);
    }
    $price_model_string = $show->show_seat_price_model;

    boffice_html::$html_body_regions[] = new boffice_html_region($show->display_feature(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
    boffice_html::$html_body_regions[] = new boffice_html_region($show->show_times(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
    if ($price_model_string::price_varies_by_show_instance()) {
        boffice_html::$html_body_regions[] = new boffice_html_region("<div class='show-prices notice'>Prices for this show vary by performance.</div>", boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
    } else {
        $instances = show::get_instances($show->show_id, true, 1);
        $first_show = $instances[0];
        boffice_html::$html_body_regions[] = new boffice_html_region($first_show->get_prices_html(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
    }

    boffice_html::$html_body_regions[] = new boffice_html_region(package::advertise(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_TEASER);
} else if (count($p_parts) === 2) {
    user::login_required($site_path . "/show/" . urlencode($p_parts[0]) . "/" . urlencode($p_parts[1]));
    $show_instance = new show_instance($p_parts[1]);
    seating_chart($show_instance);
} else if (count($p_parts) === 3 AND $p_parts[1] === 'seats') {
    user::login_required($site_path . "/show/" . urlencode($p_parts[0]) . "/" . urlencode($p_parts[1]) . "/seats");
    $show_instance = new show_instance($p_parts[2]);
    seating_chart($show_instance);
} else if (count($p_parts) === 3 AND $p_parts[1] === 'reservations') {
    user::login_required($site_path . "/show/" . urlencode($p_parts[0]) . "/" . urlencode($p_parts[1]) . "/seats");
    if (isset($_POST['seat'])) {
        foreach ($_POST['seat'] as $seat_instance_id) {
            $cart = cart::cart_from_user_id(user::current_user()->user_id);
            $cart->cart_item_new('purchasable_seat_instance', intval($seat_instance_id), 1, false);
        }
    } else if (isset($_POST['seat_general_quantity'])) {
        $show_instance = new show_instance(intval($p_parts[2]));
        $cart = cart::cart_from_user_id(user::current_user()->user_id);
        $cart->cart_item_new('purchasable_seating_general', intval($_POST['purchasable_seating_general_id']), intval($_POST['seat_general_quantity']), false);
    }
    boffice_html::$html_body_regions[] = new boffice_html_region("Seats added to your reservation. <a href='" . $site_path . "checkout'>Checkout now</a> or <a href='$site_path'>keep looking</a>?");
}

/**
 * @global string $site_path
 * @global string $site_domain
 * @param show_instance $show_instance
 */
function seating_chart($show_instance)
{
    global $site_path, $site_domain;
    if ($show_instance->seating_chart_id > 0) {
        //Reserved Seating
        boffice_html::$html_body_regions[] = new boffice_html_region($show_instance->seating_chart_html());
        boffice_html::$uses_seating_chart = true;
        boffice_html::$html_body_regions[] = new boffice_html_region("
	    <ul id='seating-chart-selections'></ul>
	    <button value='Add seats to cart' id='add-to-cart'>Add Seats to Cart</button>
	    <form id='seat-form' method='POST' action='//" . $site_domain . $site_path . "show/" . $show_instance->url_name . "/reservations/" . $show_instance->show_instance_id . "'></form>
	");
    } else {
        //General Seating
        $show_instance->get_purchasable_seating_general();
        $max = min(boffice_property('general_seating_max_web_reservation'), $show_instance->purchasable_seating_general->get_quantity());
        boffice_html::$js_internal = "$(function() { $('#seat_general_quantity').spinner({min:1, max:$max}); });";
        boffice_html::$html_body_regions[] = new boffice_html_region("
	    <form id='seat-form' method='POST' action='//" . $site_domain . $site_path . "show/" . $show_instance->url_name . "/reservations/" . $show_instance->show_instance_id . "'>
		<label for='seat_general_quantity'>Select the number of seats</label>
		<input type='text' id='seat_general_quantity' name='seat_general_quantity' value='2' />
		<input type='hidden' name='purchasable_seating_general_id' value='" . $show_instance->purchasable_seating_general->purchasable_seating_general_id . "' />
		<button value='Add seats to cart' id='add-to-cart' type='SUBMIT'>Add Seats to Cart</button>
	    </form>
	");
    }

}


echo boffice_template_simple("Some title");