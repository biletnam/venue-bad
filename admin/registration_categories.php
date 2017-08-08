<?php
require_once('../boffice_config.php');

boffice_initialize();

$cat = new purchasable_registration_category();
if (filter_input(INPUT_GET, 'row_id') !== null) {
    $cat->get(intval(filter_input(INPUT_GET, 'row_id')));
}
boffice_html::$html_body_regions[] = new boffice_html_region($cat->admin_edit_form(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
boffice_html::$html_body_regions[] = new boffice_html_region($cat->admin_list('registration_categories.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
boffice_html::$standard_ckeditor = true;
echo boffice_template_simple("Admin - Users");