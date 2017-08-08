<?php
require_once('../boffice_config.php');

boffice_initialize();

$show = new show();
if (filter_input(INPUT_GET, 'show_id') !== null) {
    $show->get(filter_input(INPUT_GET, 'show_id'));
}
boffice_html::$html_body_regions[] = new boffice_html_region($show->admin_edit_form(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
if ($show->show_id > 0) {
    boffice_html::$html_body_regions[] = new boffice_html_region($show->admin_create_instance('shows.php?show_id=' . $show->show_id), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
    boffice_html::$html_body_regions[] = new boffice_html_region($show->admin_instances_list('show_instances.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
}
boffice_html::$html_body_regions[] = new boffice_html_region($show->admin_list_shows('shows.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
boffice_html::$standard_ckeditor = true;
echo boffice_template_simple("Admin - Shows");