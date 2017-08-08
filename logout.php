<?php
require_once 'boffice_config.php';

if (!boffice_logged_in()) {
    header("location: //$site_domain.$site_path");
    exit();
} else {
    boffice_log_out();
}

boffice_html::$html_body_regions[] = new boffice_html_region("<div class='notice'>You have logged out.</div>");

echo boffice_template_simple("Logged out");