<?php
require_once('../boffice_config.php');

boffice_initialize();


if (filter_input(INPUT_GET, 'row_id') !== null) {
    $model = new purchasable_seat_abstract(filter_input(INPUT_GET, 'row_id'));
} else {
    $model = new purchasable_seat_abstract();
}

boffice_html::$html_body_regions[] = new boffice_html_region($model->admin_edit_form(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
boffice_html::$html_body_regions[] = new boffice_html_region($model->admin_list('seating_chart_seat_types.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
boffice_html::$standard_ckeditor = true;
echo boffice_template_simple("Admin - Seating Chart Seat Types");