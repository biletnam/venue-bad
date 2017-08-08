<?php
require_once('boffice_config.php');
boffice_initialize();

$p = filter_input(INPUT_GET, 'p');
if (substr($p, -1) === "/") {
    $p = substr($p, 0, -1);
}
$p_parts = explode('/', $p);


if (count($p_parts) === 2 AND $p_parts[0] === 'new' AND is_numeric($p_parts[1])) {
    user::login_required($site_path . "package/new/" . $p_parts[1]);

    $package_model = new purchasable_package_model($p_parts[1]);
    $user = user::current_user();
    if ($package_model->package_model_patron_type_id === '0' OR $package_model->package_model_patron_type_id === $user->patron_type_id) {
        $cart = cart::cart_from_user_id($user->user_id);
        $cart->cart_item_new("purchasable_package_model", $p_parts[1], 1, false);
        boffice_html::$html_body_prepend .= "<div class='item-added-to-cart'>You package has been added to your cart. You can buy and use your new package at the same time at checkout, just add the seats or mechandise you want to use the package <em>for</em> to your cart. <a href='" . $site_path . "checkout'>Checkout</a> or <a href='$site_path'>Keep Looking</a>.</div>";
    } else {
        $patron_type = new patron_types($package_model->package_model_patron_type_id);
        new boffice_error("Sorry, that package is only available to patrons that are " . $patron_type->patron_type_label . ".");
    }

}

echo boffice_template_simple("Some title");