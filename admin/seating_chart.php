<?php

require_once('../boffice_config.php');

boffice_initialize();

if (filter_input(INPUT_GET, 'row_id') !== null) {
    $model = new seating_chart(intval(filter_input(INPUT_GET, 'row_id')));
} else {
    $model = new seating_chart();
}

boffice_html::$html_body_regions[] = new boffice_html_region($model->admin_edit_form(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
if ($model->id) {
    boffice_html::$html_body_regions[] = new boffice_html_region($model->admin_editor(), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
} else {
    boffice_html::$html_body_regions[] = new boffice_html_region(seating_chart::admin_list('seating_chart.php'), boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED);
}

echo boffice_template_simple("Admin - Seating Chart");