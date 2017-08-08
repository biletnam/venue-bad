<?php

require_once('boffice_config.php');


boffice_initialize();
boffice_html::$js_external_src[] = "//" . $site_domain . $site_path . "_resources/external/jquery.creditCardValidator.js";

global $global_conn;

$p = filter_input(INPUT_GET, 'p');
if (substr($p, -1) === "/") {
    $p = substr($p, 0, -1);
}
$p_parts = explode('/', $p);
$user = user::current_user();
user::login_required('checkout');
$cart = cart::cart_from_user_id($user->user_id);

if (count($p_parts) === 1 AND $p_parts[0] === 'giftcard') {
    if (filter_input(INPUT_POST, 'giftcard_id') !== null AND filter_input(INPUT_POST, 'giftcard_key') !== null) {
        if (purchasable_giftcard_instance::is_valid(filter_input(INPUT_POST, 'giftcard_id'), filter_input(INPUT_POST, 'giftcard_key'))) {
            $card = purchasable_giftcard_instance::get_card_by_id(filter_input(INPUT_POST, 'giftcard_id'));
            $new_giftcard_usage_id = purchasable_giftcard::create_unprocessed_usage($card->purchasable_giftcard_instance_id, 0, $cart->cart_id);
            if ($card->get_remaining_value() > 0) {
                $max_value = min($cart->cart_total_with_deductions(true), $card->get_remaining_value());
                $string = "
		<form action='//" . $site_domain . $site_path . "checkout/giftcard' method='POST'>
		    <div class='form-row'>Giftcard XXXX XXXX " . substr($card->purchasable_giftcard_instance_human_id, -4) . "</div>
		    <div class='form-row'>Remaining value $" . money($card->get_remaining_value()) . "</div>
		    <div class='form-row'><label for='amount'>Apply Amount</label><input type='text' name='amount' value='" . $max_value . "' id='amount' /></div>
		    <input type='hidden' name='giftcard_usage_id' value='$new_giftcard_usage_id' />
		    <div class='form-row'><button type='SUBMIT' value='Add Giftcard'>Add Giftcard</button></div>
		    <script type='text/javascript'>
			$(function() {
			    $('#amount').spinner({
				min:1,
				max:$max_value
			    });
			});
		    </script>
		</form>
		";
            } else {
                $string = "<div><p>Giftcard XXXX XXXX " . substr($card->purchasable_giftcard_instance_human_id, -4) . " has no remaining value. <a href='//" . $site_domain . $site_path . "checkout/'>Checkout</a></div>";
            }
            boffice_html::$html_body_regions[] = new boffice_html_region($string, boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
        } else {
            new boffice_error("The giftcard number and key did not match. <a href='//" . $site_domain . $site_path . "checkout/'>Checkout</a>", true);
        }

    } else if (filter_input(INPUT_POST, 'giftcard_usage_id') !== null AND filter_input(INPUT_POST, 'amount') !== null) {
        $new_giftcard_usage_id = filter_input(INPUT_POST, 'giftcard_usage_id');
        $results = db_query($global_conn, "SELECT * FROM giftcard_usage WHERE giftcard_usage_id = " . db_escape($new_giftcard_usage_id));
        if (count($results === 1)) {
            $card = new purchasable_giftcard_instance($results[0]['purchasable_giftcard_instance_id']);
            $max_value = min($card->get_remaining_value(), $cart->cart_total_with_deductions(true));
            if (filter_input(INPUT_POST, 'amount') > $max_value) {
                header("location: //" . $site_domain . $site_path . "checkout/");
                die();
            }
            db_exec($global_conn, build_update_query($global_conn, "giftcard_usage", array('giftcard_usage_amount' => filter_input(INPUT_POST, 'amount')), " giftcard_usage_id = " . db_escape($new_giftcard_usage_id)));
            header("location: //" . $site_domain . $site_path . "checkout/");
            die();
        } else {
            boffice_html::$html_body_regions[] = new boffice_html_region("<a href='//" . $site_domain . $site_path . "checkout/'>Checkout</a>");
        }
    } else {
        boffice_html::$html_body_regions[] = new boffice_html_region("<a href='//" . $site_domain . $site_path . "checkout/'>Checkout</a>");
    }
} else {
    $cart_summary = "
	<div class='cart-summary'>
	    " . $cart->cart_contents_html('cart-contents', true, true);
    $deductions = $cart->cart_items_reaction(true);
    if ($deductions > 0) {
        $cart_summary .= "
		    <div class='cart-row'><label>Subtotal</label> " . money($cart->cart_sub_total()) . "</div>
		    <div class='cart-row'><label>Savings</label> " . money($deductions) . " </div>
		    <div class='cart-row'><label>Total</label> " . money($cart->cart_sub_total() - $deductions) . "</div>";
    } else {
        $cart_summary .= "
		    <div class='cart-row'>Total - $" . money($cart->cart_sub_total()) . "</div>
		";
    }
    $cart_summary .= " 
	</div>";

    boffice_html::$html_body_regions[] = new boffice_html_region($cart_summary, boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
    boffice_html::$html_body_regions[] = new boffice_html_region($cart->cart_checkout(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);

}

echo boffice_template_simple("Checkout");

