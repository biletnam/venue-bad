<?php

require_once('../boffice_config.php');
global $global_conn, $boffice_database_connection_host_address, $boffice_database_connection_password, $boffice_database_connection_type, $boffice_database_connection_username, $boffice_database_name;

ob_implicit_flush(true);
ob_end_flush();
echo "<html><head></head><body><!-- THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... !-->";

$old_trustus = new PDO($boffice_database_connection_type . ':host=' . $boffice_database_connection_host_address . ';dbname=old_trustus;', $boffice_database_connection_username, $boffice_database_connection_password);
$old_trustus->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$old_users = new PDO($boffice_database_connection_type . ':host=' . $boffice_database_connection_host_address . ';dbname=old_users;', $boffice_database_connection_username, $boffice_database_connection_password);
$old_users->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<p>This migrates all data except ulogin</p>";

db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 0;");

$tables = array(
    "payment_finacial_details",
    "package_usage",
    "package",
    'cart_item',
    'purchasable_seat_instance',
    'reservation',
    'shows',
    'show_instance',
    'transaction',
    'cart',
    'giftcard_usage',
    'purchasable_giftcard_instance',
    'purchasable_registration',
    'purchasable_registration_instance',
    'purchasable_registration_category',
    'purchasable_seating_general',
    'registrations',
    'show_instance_cache',
    'page_files_cache',
);
foreach ($tables as $table) {
    echo "<p>Clearing $table... ";
    db_exec($global_conn, "DELETE FROM $table");
    db_exec($global_conn, "ALTER TABLE $table auto_increment = 1");
    echo "DONE </p>";
}
db_exec($global_conn, "DELETE FROM page_files WHERE user != 'SYSTEM'");


//Migrate users

/*
db_exec($global_conn, "TRUNCATE users");
$patron_types = array('Adult'=>'1','Senior'=>'2','Military'=>'3','Student'=>'4');
$i=0;
foreach(db_query($old_trustus, "SELECT * FROM users") as $row) {
    $user = new user();
    $user->ulogin_id = $row['user_id'];
    $user->user_email = $row['email'];
    $user->user_address_line1 = $row['address1'];
    $user->user_address_line2 = $row['address2'];
    $user->user_city = $row['city'];
    $user->user_state = $row['state'];
    $user->user_name_last = $row['lastName'];
    $user->user_name_first = $row['firstName'];
    $user->user_note = "";
    $user->user_reservation_reminders = 0;
    $user->user_account_value = 0;
    $user->patron_type_id = $patron_types[$row['patron_type']];	
    $user->user_email_list = 0;
    $user->user_is_company = 0;
    $user->user_is_finacial_admin = $row['patron_type'] === 'patron' ? 0 : 1;
    $user->user_is_office_admin = $row['patron_type'] === 'patron' ? 0 : 1;
    $user->user_is_show_admin = $row['patron_type'] === 'patron' ? 0 : 1;
    $user->user_is_class_admin = $row['patron_type'] === 'patron' ? 0 : 1;
    if($row['billing_name'] !== "") {
	$user->user_note .= "Has used '".$row['billing_name']."' as a bill-to name. ";
    }
    $user->set();
    if($user->user_id > 0) {
	$i++;
    }
}
echo "<p>Migrated $i users</p>";
 */


$j = 0;
echo "<p>Migrating packages... <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
foreach (db_query($old_trustus, "SELECT * FROM packages WHERE package_starting_count - package_used_tickets > 0") as $row) {
    $cart = new cart();
    $cart->cart_is_active = '0';
    $cart->user_id = $row['user_id'];
    $cart->set_cart();
    $cart_item = new cart_item();
    $cart_item->purchasable_class = "purchasable_package_model";
    $cart_item->purchasable_class_id = '1';
    $cart->cart_item_add($cart_item);
    $cart->cart_checkout_actualize('Legacy');

    $bogus_transaction = new transaction();
    $bogus_transaction->user_id = $row['user_id'];
    $bogus_transaction->set();

    $package_usage = new package_usage();
    $package_usage->benefit_id = '1';
    $package_usage->transaction_id = $bogus_transaction->transaction_id;
    $package_usage->package_id = $cart_item->resultant_class_id;
    $package_usage->package_usage_deduction = $row['package_starting_count'] - $row['package_used_tickets'];
    $package_usage->set();

    echo ".";
    if ($j % 100 === 0) {
        echo $j . "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    $j++;


}

echo "$j <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Done</p><p>Migrating shows...";


$show_type_to_seating_chart_id_map = array(1 => '1', 2 => '1', 3 => '0', 4 => '0', 5 => '0', 6 => '0', 7 => '1', 8 => '1');
$show_type_to_series_id = array(1 => '1', 2 => '1', 3 => '0', 4 => '0', 5 => '0', 6 => '0', 7 => '1', 8 => '1');
$show_type_to_base_price_map = array(1 => '22', 2 => '27', 3 => '15', 4 => '15', 5 => '15', 6 => '15', 7 => '30', 8 => '30');
$show_type_to_stage_id = array(1 => '1', 2 => '1', 3 => '2', 4 => '2', 5 => '2', 6 => '3', 7 => '1', 8 => '1');
foreach (db_query($old_trustus, "SELECT * FROM shows") as $row) {
    global $global_conn, $site_domain, $site_path, $site_files_path;
    echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp;" . $row['show_name'] . "... ";

    if (strlen($row['imgBackground']) > 2) {
        $url = $row['imgBackground'];
    } else {
        $url = "images/sampleBackground2.jpg";
    }
    $cover_image_url = "//" . $site_domain . $site_path . $site_files_path . put_web_file_in_sql("http://trustus.org/" . $url);
    $show = new show();
    $show->title = $row['show_name'];
    $show->url_name = classy($row['show_name']);
    $show->description = "<p>" . $row['show_people'] . "</p>" . $row['show_description'];
    $show->cover_image_url = $cover_image_url;
    $show->seating_chart_id = $show_type_to_seating_chart_id_map[$row['show_type_id']];
    $show->seating_chart_general_count = $show_type_to_seating_chart_id_map[$row['show_type_id']] === '0' ? 50 : 0;
    $show->stage_id = $show_type_to_stage_id[$row['show_type_id']];
    $show->show_base_price = $show_type_to_base_price_map[$row['show_type_id']];
    $show->show_seat_price_model = 'show_seat_price_model_patron_type';
    $show->set();

    $image_mappings = array('imgPoster' => '2', 'imgThumb' => '1', 'imgBackground' => '3', 'imgBackground2' => '3', 'imgBackground3' => '3', 'imgBackground4' => '3', 'imgMobile' => '4');
    foreach ($image_mappings as $old => $new_id) {
        if (strlen($row[$old]) > 2) {
            $image_id = put_web_file_in_sql("http://trustus.org/" . $row[$old]);
            db_exec($global_conn, build_insert_query($global_conn, 'show_image_assignments', array(
                'show_id' => $show->show_id,
                'show_image_type_id' => $new_id,
                'page_file_id' => $image_id,
            )));
        }
    }

    foreach (db_query($old_trustus, "SELECT * FROM instances WHERE show_id = " . db_escape($row['show_id'])) as $instance_data) {
        $show->create_instance($instance_data['datetime']);
        echo ".";
    }

    echo " Done";
}
echo "<br/>Done</p>";


