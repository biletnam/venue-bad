<?php

require_once '../boffice_config.php';
$user = user::current_user();
if (!($user->user_is_office_admin OR $user->user_is_finacial_admin OR $user->user_is_show_admin)) {
    die('Access Denied');
}

$term = filter_input(INPUT_GET, 'term');
$array = array();
$q = db_escape("%" . $term . "%");
$results = db_query($global_conn, "SELECT * FROM users WHERE user_name_last LIKE $q OR user_name_first LIKE $q OR user_email LIKE $q ORDER BY user_name_last, user_name_first ASC");
if (count($results)) {
    foreach ($results as $user) {
        $array[] = array('value' => $user['user_id'], 'label' => $user['user_name_last'] . ", " . $user['user_name_first'] . ". " . $user['user_email']);
    }
}

echo json_encode($array);