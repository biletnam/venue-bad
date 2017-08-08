<?php
require_once 'boffice_config.php';

boffice_html::$html_body_regions[] = new boffice_html_region(user::new_user_public_interface());

echo boffice_template_simple("some test");

