<?php
require_once('boffice_config.php');
boffice_initialize();

$p = filter_input(INPUT_GET, 'p');
if (substr($p, -1) === "/") {
    $p = substr($p, 0, -1);
}
$p_parts = explode('/', $p);

if (strlen($p) === 0) {
    foreach (db_query($global_conn, "SELECT * FROM purchasable_registration LEFT JOIN purchasable_registration_category USING (purchasable_registration_category_id) ORDER BY purchasable_registration_category_name, reg_name ASC ") as $item) {
        $reggie = new purchasable_registration($item['purchasable_registration_id']);
        boffice_html::$html_body_regions[] = new boffice_html_region($reggie->display(2, false), boffice_html_region::BOFFICE_HTML_REGION_TYPE_TEASER);
    }


} else if (count($p_parts) === 1) {
    /**
     * Really doesn't seem to do anyting. There's no way to register for a purchasable_registration unless its the instance. Would be nice though...
     */

//    if(is_numeric($p_parts[0])) {
//	$reggie = new purchasable_registration(intval($p_parts[0]));
//    } else {
//	$reggie = new purchasable_registration(1);
//    }
//    boffice_html::$html_body_regions[] = new boffice_html_region($reggie->display(2, true), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);


} else if (count($p_parts) === 2) {
    user::login_required("//" . $site_domain . $site_path . "register/" . intval($p_parts[0]) . "/" . intval($p_parts[1]));
    $reggie = new purchasable_registration($p_parts[0]);
    $class = 'purchasable_registration';
    $id = $p_parts[0];
    if ($p_parts[1] !== '0') {
        $instance = new purchasable_registration_instance($p_parts[1]);
        $class = 'purchasable_registration_instance';
        $id = $p_parts[1];
    }
    if ($p_parts[1] !== '0') {
        $string = $instance->display();
    } else {
        $string = $reggie->display(2, FALSE);
    }
    if (isset($_SESSION['boffice'], $_SESSION['boffice']['cart_pending']) AND user::current_user() !== null) {
        $cart = cart::cart_from_user_id(user::current_user()->user_id);
        $cart->cart_item_new($_SESSION['boffice']['cart_pending']['class'], $_SESSION['boffice']['cart_pending']['id'], $_SESSION['boffice']['cart_pending']['quantity'], true);
        $string = "<div class='notice'>Added to cart. <a href='" . $site_path . "checkout'>Checkout</a> or <a href='$site_path'>Keep Looking</a>.</div>";
        unset($_SESSION['boffice']['cart_pending']);
    }
    if (filter_input(INPUT_POST, 'quantity') !== null) {
        if (user::current_user() === null) {
            $_SESSION['boffice']['cart_pending'] = array('class' => $class, 'id' => $id, 'quantity' => filter_input(INPUT_POST, 'quantity'));
            header("location: " . $site_path . "login.php?target_url=" . $site_path . "register/" . $p_parts[0] . "/" . $p_parts[1]);
        } else {
            $cart = cart::cart_from_user_id(user::current_user()->user_id);
            $cart->cart_item_new($class, $id, filter_input(INPUT_POST, 'quantity'), true);
            $string = "<div class='notice'>Added to cart. <a href='" . $site_path . "checkout'>Checkout</a> or <a href='$site_path'>Keep Looking</a>.</div>";
        }
    }
    boffice_html::$html_body_regions[] = new boffice_html_region($string, boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
}

echo boffice_template_simple("Some title");