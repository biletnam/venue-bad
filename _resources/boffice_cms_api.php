<?php

require_once '../boffice_config.php';

$user = user::current_user();
if ($user === null) {
    die('no_session');
}
if (!$user->user_has_any_elevated_privileges()) {
    die('Access Denied');
}

//setup
$string = "";


//Basic Variables
if (filter_input(INPUT_GET, 'use_get') === '1') {
    $method = INPUT_GET;
} else {
    $method = INPUT_POST;
}
$command = filter_input($method, 'command');
$boffice_html_static_id = filter_input($method, 'boffice_html_static_id');
$boffice_html_dynamic_id = filter_input($method, 'boffice_html_dynamic_id');
$boffice_html_group_id = filter_input($method, 'boffice_html_group_id');
$boffice_html_page_id = filter_input($method, 'boffice_html_page_id');

if ($command === "edit_static_prepare") {
    $boffice_html_static = new boffice_html_static($boffice_html_static_id);
    $string .= $boffice_html_static->admin_edit_form();


} else if ($command === "edit_dynamic_prepare") {
    $boffice_html_dynamic = new boffice_html_dynamic($boffice_html_dynamic_id);
    $string .= $boffice_html_dynamic->admin_edit_form();


//MVC controller
} else if ($command === "update_standard") {
    $cls = filter_input(INPUT_POST, 'update_cls');
    $object = new $cls(filter_input(INPUT_POST, 'row_id'));
    $object->admin_edit_form();
    if (boffice_error::any_errors()) {
        $string = "0";
    } else {
        $string = "1";
    }
}


if (isset($string) AND is_string($string) AND $string !== "") {
    echo $string;
}