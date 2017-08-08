<?php

require_once('../boffice_config.php');

boffice_initialize();

$reggie = new purchasable_registration();
if (filter_input(INPUT_GET, 'row_id') !== null) {
    $reggie->get(intval(filter_input(INPUT_GET, 'row_id')));
}
boffice_html::$html_body_regions[] = new boffice_html_region($reggie->admin_edit_form('registrationables.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
boffice_html::$html_body_regions[] = new boffice_html_region($reggie->admin_list('registrationables.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
boffice_html::$standard_ckeditor = true;
echo boffice_template_simple("Admin - Registrationable Things");