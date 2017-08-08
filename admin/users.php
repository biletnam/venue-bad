<?php
require_once('../boffice_config.php');

boffice_initialize();

$user = new user();
if (filter_input(INPUT_GET, 'row_id') !== null) {
    $user->get(intval(filter_input(INPUT_GET, 'row_id')));
}
boffice_html::$html_body_regions[] = new boffice_html_region($user->admin_edit_form(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
boffice_html::$html_body_regions[] = new boffice_html_region($user->admin_list_users('users.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
boffice_html::$standard_ckeditor = true;
echo boffice_template_simple("Admin - Users");