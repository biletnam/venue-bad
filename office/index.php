<?php

require_once '../boffice_config.php';
require_once 'office.template.php';

user::login_required($site_path . "office");
$user = user::current_user();
if (!($user->user_is_office_admin OR $user->user_is_finacial_admin OR $user->user_is_show_admin)) {
    die('Access Denied');
}

$nav_elements = array();

if ($user->user_is_finacial_admin) {
    $nav_elements['purse.php'] = 'Finace';
}
if ($user->user_is_office_admin) {
    /*
     * Update and Insert hooks. 
     * All we need to do is call the method that starts f_data->start() and f_data will handle the actual form submission
     */
    if (filter_input(INPUT_POST, 'f_data_shows') !== null) {
        if (filter_input(INPUT_POST, 'row_id') !== null) {
            $show = new show(filter_input(INPUT_POST, 'row_id'));
        } else {
            $show = new show();
        }
        $show->admin_edit_form_boxoffice();
    }
    if (filter_input(INPUT_POST, 'showtime_new') === '1') {
        $show = new show(filter_input(INPUT_POST, 'show_id'));
        $show->create_instance(filter_input(INPUT_POST, 'datetime'));
    }
    if (filter_input(INPUT_POST, 'seating_chart_editor_submission')) {
        $chart = new seating_chart();
        $chart->admin_editor(32, $site_path . "office/");
    }
    if (filter_input(INPUT_POST, 'new_user_form') === '1') {
        $user = new user();
        $user->admin_edit_form();
    }
    if (filter_input(INPUT_POST, 'f_data_purchasableregistration') === '1') {
        $registration = new purchasable_registration();
        $registration->admin_edit_form();
    }
    if (filter_input(INPUT_POST, 'f_data_showinstance') === '1') {
        $show_instance = new show_instance(filter_input(INPUT_POST, 'row_id'));
        $show_instance->admin_edit_form();
    }
}

$string = "";


echo boffice_template_admin($string);

