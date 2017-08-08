<?php

require_once('../boffice_config.php');
boffice_initialize();

$instance = new purchasable_registration_instance();
$instance->purchasable_registration_id = filter_input(INPUT_GET, 'row_id');
if (filter_input(INPUT_GET, 'Reg_id') !== null AND filter_input(INPUT_GET, 'Reg_id') !== "") {
    $instance->get(filter_input(INPUT_GET, 'Reg_id'));
}
boffice_html::$html_body_regions[] = new boffice_html_region($instance->admin_edit_form(filter_input(INPUT_GET, 'reg_id')), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
boffice_html::$standard_datetime_picker = true;
echo boffice_template_simple("Admin - Registrationable Instances");