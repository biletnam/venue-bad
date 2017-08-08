<?php
require_once 'boffice_config.php';

$target_url = filter_input(INPUT_GET, 'target_url');
if ($target_url == null OR $target_url == false) {
    $target_url = "//" . $site_domain . $site_path . $site_account_url;
}
if (boffice_logged_in()) {
    header("location: $target_url");
    exit();
}

boffice_html::$html_body_regions[] = new boffice_html_region(user::user_login_public_interface($target_url));

echo boffice_template_simple("some test");
