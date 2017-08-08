<?php
require_once('boffice_config.php');
boffice_initialize();
global $global_conn;

if (filter_input(INPUT_GET, 'p') === null) {
    global $site_domain;
    header("location: //$site_domain");
}

$original_request = explode("?", filter_input(INPUT_SERVER, 'REQUEST_URI'));
if (count($original_request) > 1) {
    $original_vars = explode("&", urldecode($original_request[1]));
    foreach ($original_vars as $var) {
        $parts = explode("=", $var);
        if (isset($parts[1])) {
            $_GET[$parts[0]] = $parts[1];
        } else {
            $_GET[$parts[0]] = "";
        }
    }
}

$page = boffice_html_page::get_page_by_url(filter_input(INPUT_GET, 'p'));
echo $page->say();
