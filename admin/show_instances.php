<?php
require_once('../boffice_config.php');

boffice_initialize();
boffice_html::$standard_datetime_picker = true;
$instance = new show_instance();
if (filter_input(INPUT_GET, 'instance_id') !== null) {
    $instance->get(filter_input(INPUT_GET, 'instance_id'));
    boffice_html::$html_body_regions[] = new boffice_html_region($instance->admin_edit_form('show_instances.php?instance_id=' . filter_input(INPUT_GET, 'instance_id')), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
} else {
    new boffice_error("No instance specified.");
}

echo boffice_template_simple("Admin - Instances");