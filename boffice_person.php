<?php
require_once('boffice_config.php');
boffice_initialize();
global $global_conn;

if (filter_input(INPUT_GET, 'p') === null) {
    global $site_domain;
    boffice_html::$html_body_regions[] = new boffice_html_region("We should make a profile's list here, maybe... ");
} else {
    $p = filter_input(INPUT_GET, 'p');
    if (substr($p, -1) === "/") {
        $p = substr($p, 0, -1);
    }
    $p_parts = explode('/', $p);
    $id = intval($p_parts[0]);
    $user = new user($id);
    if ($id === 0 OR $user->user_name_last === '') {
        new boffice_error("Cannot find that profile");
    } else {
        $results = db_query($global_conn, "SELECT * FROM show_people WHERE user_id = " . db_escape($id));
        if ($user->user_is_company !== '1' AND count($results) === 0) {
            new boffice_error("Sorry, that's not an active profile.");
        } else {
            boffice_html::$html_body_regions[] = new boffice_html_region($user->display());
        }
    }
}

echo boffice_template_simple("Profiles");