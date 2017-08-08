<?php

require_once('../boffice_config.php');
global $global_conn;
$images_used = array();
ob_implicit_flush(true);
ob_end_flush();
echo "<html><head></head><body><!-- THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... THIS IS garbage text to get ob_flush to start with extra characters... !-->";

if (boffice_logged_in()) {
    $user = user::current_user();
    if ($user->user_has_any_elevated_privileges()) {

        $_SESSION['performing_batch'] = TRUE;

        db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 0;");

        if (filter_input(INPUT_GET, 'users') === '1') {
            echo "<p>Clearing users... ";
            db_exec($global_conn, "DELETE FROM users WHERE user_id > 1");
            db_exec($global_conn, "ALTER TABLE users auto_increment = 2");
            echo "DONE </p>";
            echo "<p>Creating new users... ";
            create_random_users(500);
            echo "Done</p>";
        }

        if (filter_input(INPUT_GET, 'shows') === '1') {
            $tables = array("payment_finacial_details", "package_usage", "package", 'cart_item', 'purchasable_seat_instance', 'reservation', 'shows', 'show_instance', 'transaction', 'cart');
            foreach ($tables as $table) {
                echo "<p>Clearing $table... ";
                db_exec($global_conn, "DELETE FROM $table");
                db_exec($global_conn, "ALTER TABLE $table auto_increment = 1");
                echo "DONE </p>";
            }

            $first_day_of_month = strtotime(date("Y-m-01 00:00:01"));
            $first_friday = strtotime("next friday", $first_day_of_month);
            echo "<p>Creating a new season starting " . date("Y-m-d H:i:s", $first_friday) . "... ";
            $instances = test_create_season($first_day_of_month);
            echo "Done</p>";

            echo "<p>Creating new reservations at random... <br />";
            for ($i = 0; $i < 5000; $i++) {
                echo $i . " of 5000";
                test_create_new_reservation($instances[array_rand($instances)], rand(2, 500));
            }
            echo "DONE</p>";
        }

        if (filter_input(INPUT_GET, 'classes') === '1') {
            echo "<p>Clearing classes... ";
            db_exec($global_conn, "DELETE FROM purchasable_registration");
            db_exec($global_conn, "ALTER TABLE purchasable_registration auto_increment = 1");
            db_exec($global_conn, "DELETE FROM purchasable_registration_category");
            db_exec($global_conn, "ALTER TABLE purchasable_registration_category auto_increment = 1");
            db_exec($global_conn, "DELETE FROM purchasable_registration_instance");
            db_exec($global_conn, "ALTER TABLE purchasable_registration_instance auto_increment = 1");
            db_exec($global_conn, "DELETE FROM registrations");
            db_exec($global_conn, "ALTER TABLE registrations auto_increment = 1");
            echo "Done</p>";
            echo "<p>Creating classes... ";
            $serieses = array();
            for ($i = 0; $i < 5; $i++) {
                $series = new purchasable_registration_category();
                $series->purchasable_registration_category_name = last_name_generator() . " Series";
                $series->set();
                $serieses[] = $series->purchasable_registration_category_id;
            }
            for ($i = 0; $i < 10; $i++) {
                $time = date("Y-m-d 14:00:00", rand(time(), strtotime("+6 months")));
                $class = new purchasable_registration();
                $class->purchasable_registration_category_id = $serieses[array_rand($serieses)];
                $class->purchasable_price = rand(10, 40);
                $class->reg_date_start = $time;
                $class->reg_date_end = date("Y-m-d H:i:s", strtotime("+3 weeks", strtotime($time)));
                $class->reg_date_sales_start = date("Y-m-d H:i:s", strtotime("-4 weeks", strtotime($time)));
                $class->reg_date_sales_end = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($time)));
                $class->reg_price = rand(10, 40);
                $class->reg_quantity = rand(10, 30);
                $class->reg_name = first_name_generator() . " course";
                $class->reg_img_url = "//" . $site_domain . $site_path . "setup/sample_images/" . rand(1, 30) . ".jpg";
                $class->reg_sales_available = purchasable_registration::ITEM_STATUS_AVAILABLE;
                $class->set();

                $class1 = new purchasable_registration_instance();
                $class1->purchasable_registration_id = $class->purchasable_registration_id;
                $class1->purchasable_registration_instance_datetime = date("Y-m-d H:i:s", strtotime("+0 hours", strtotime($time)));
                $class1->set();
                $class2 = new purchasable_registration_instance();
                $class2->purchasable_registration_id = $class->purchasable_registration_id;
                $class2->purchasable_registration_instance_datetime = date("Y-m-d H:i:s", strtotime("+1 weeks", strtotime($time)));
                $class2->set();
                $class3 = new purchasable_registration_instance();
                $class3->purchasable_registration_id = $class->purchasable_registration_id;
                $class3->purchasable_registration_instance_datetime = date("Y-m-d H:i:s", strtotime("+2 weeks", strtotime($time)));
                $class3->set();
                $class4 = new purchasable_registration_instance();
                $class4->purchasable_registration_id = $class->purchasable_registration_id;
                $class4->purchasable_registration_instance_datetime = date("Y-m-d H:i:s", strtotime("+3 weeks", strtotime($time)));
                $class4->set();
            }
            echo "Done</p>";


            echo "<p>Creating class registrations... ";
            for ($i = 0; $i < 500; $i++) {
                set_time_limit(120);
                echo $i . " of 500. ";
                $results = db_query($global_conn, "SELECT purchasable_registration_instance_id FROM purchasable_registration_instance ORDER BY rand() LIMIT 1;");
                test_create_new_registration($results[0]['purchasable_registration_instance_id'], rand(2, 200));
            }
            echo "Done</p>";

        }


        if (filter_input(INPUT_GET, 'do_one_reservation') === '1') {
            test_create_new_reservation(10, 10);
        }

        if (filter_input(INPUT_GET, 'transaction_shuffle') === '1') {
            $range = 10;//days
            $min_range = $range * 24 * 60;
            foreach (db_query($global_conn, "SELECT * FROM transaction") as $row) {
                $offset_mins = rand(-1 * $min_range, $min_range);
                if ($offset_mins > 0) {
                    $new_time = date("Y-m-d H:i:s", strtotime("+" . $offset_mins . " minutes", strtotime($row['datetime'])));
                } else {
                    $new_time = date("Y-m-d H:i:s", strtotime("-" . abs($offset_mins) . " minutes", strtotime($row['datetime'])));
                }
                db_exec($global_conn, "UPDATE transaction SET datetime = " . db_escape($new_time) . " WHERE transaction_id = " . db_escape($row['transaction_id']));
            }
        }

        echo "<p>Updating Instance Caches";
        foreach (db_query($global_conn, "SELECT * FROM show_instance") as $row) {
            $instance = new show_instance($row['show_instance_id']);
            $instance->seating_chart_html_update();
            show_instance_cache::update($instance->show_instance_id);
            echo ".";
        }
        echo " DONE</p>";

        db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 1;");
        $_SESSION['performing_batch'] = FALSE;

        echo "
	    <ul>
		<li><a href='create_testing_data.php?users=1'>Just Users</a></li>
		<li><a href='create_testing_data.php?shows=1&transaction_shuffle=1'>Just Shows</a></li>
		<li><a href='create_testing_data.php?classes=1&transaction_shuffle=1'>Just Classes</a></li>
		<li><a href='create_testing_data.php?do_one_reservation=1'>Just One Reservation</a></li>
		<li><a href='create_testing_data.php?users=1&shows=1&classes=1&transaction_shuffle=1'>Everything</a></li>
	    </ul>";

    } else {
        die('permission denied');
    }
} else {
    die('permission denied');
}


function test_create_edu_category($category)
{

}


function test_create_season($first_day_of_month)
{
    $current_day = $first_day_of_month;
    $instances = array();
    for ($i = 0; $i < 12; $i++) {
        $instances = array_merge($instances, test_create_show($current_day, 3));
        $current_day = strtotime("+4 weeks", $current_day);
    }
    return $instances;
}


function test_create_show($first_friday_date, $weeks)
{
    global $images_used, $site_domain, $site_path;
    $show = new show();
    $show->title = lorium_ipsum(rand(10, 30));
    $show->description = lorium_ipsum(rand(300, 500));
    $show->seating_chart_id = 1;
    $show->stage_id = 1;
    $show->show_base_price = 20;
    $show->show_seat_price_model = "show_seat_price_model_patron_type";
    $show->url_name = classy($show->title);

    $image_num = rand(0, 30);
    if (count($images_used) > 29) {
        die("Max Sample Shows Reached");
    }
    while (array_search($image_num, $images_used)) {
        $image_num = rand(0, 30);
    }
    $show->cover_image_url = "//" . $site_domain . $site_path . "setup/sample_images/$image_num.jpg";
    $show->set();

    $new_instances = array();
    for ($i = 0; $i < $weeks; $i++) {
        $offset = $i * 7;
        $friday = date("Y-m-d 18:00:00", strtotime("+$offset days", $first_friday_date));
        $saturday = date("Y-m-d 18:00:00", strtotime("+" . $offset + 1 . " days", $first_friday_date));
        $sunday = date("Y-m-d 16:00:00", strtotime("+" . $offset + 1 . " days", $first_friday_date));
        $instance1 = $show->create_instance($friday);
        $instance2 = $show->create_instance($saturday);
        $instance3 = $show->create_instance($sunday);
        $new_instances[] = $instance1->show_instance_id;
        $new_instances[] = $instance2->show_instance_id;
        $new_instances[] = $instance3->show_instance_id;
    }
    return $new_instances;
}

function create_random_users($num = 200)
{
    for ($i = 0; $i < $num; $i++) {
        $user = new user();
        $user->patron_type_id = 1;
        $user->user_address_line1 = "123 " . last_name_generator() . " ST";
        $user->user_address_line2 = "";
        $user->user_city = "Columbia";
        $user->user_state = "SC";
        $user->user_is_company = 0;
        $user->user_is_finacial_admin = 0;
        $user->user_is_office_admin = 0;
        $user->user_is_show_admin = 0;
        $user->user_name_first = first_name_generator();
        $user->user_name_last = last_name_generator();
        $user->user_zip = "29205";
        $email = "uscart+" . random_string(12) . "@gmail.com";
        //$user->ulogin_id = user::new_ulogin_user($email, random_string(10)); //Not really needed for system testing as no one will ever know the test passwords
        $user->user_email = $email;
        $user->set();
    }
}

function lorium_ipsum($length = 300)
{
    $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in molestie velit, vehicula iaculis nisi. Ut ac neque in orci consectetur lobortis. Suspendisse potenti. Morbi ut arcu in mi fermentum cursus. Quisque convallis dui orci, interdum tempus urna dapibus dapibus. Integer tincidunt tristique dolor sit amet tincidunt. Aenean dignissim metus sit amet magna lobortis ultricies. Donec eget urna et lorem volutpat semper. Proin tempor tincidunt nisl et facilisis. Nam consequat sem id ipsum semper, ac dapibus tellus cursus. Quisque rutrum lobortis augue, id lacinia velit. Cras lobortis tristique ipsum, nec faucibus justo vehicula eleifend. Curabitur id ipsum quis magna blandit consequat. Vivamus rhoncus, nisi id suscipit finibus, risus ligula viverra sapien, eu condimentum tortor arcu vitae mi. Suspendisse dapibus fringilla erat nec malesuada. Nam eget feugiat ligula, a tempus lorem. Maecenas suscipit quam sit amet velit semper rutrum. Maecenas a ultrices augue. Donec metus nunc, laoreet ac mollis at, tempor id elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Proin vulputate imperdiet elit, vitae convallis mauris volutpat a. In sapien sem, luctus in nibh quis, faucibus tincidunt urna. Curabitur aliquam purus in quam posuere, et varius sapien ornare. Donec pretium, erat vitae imperdiet tempor, ipsum libero malesuada nunc, sit amet rutrum turpis nulla vitae magna. Nam ac ex malesuada, blandit leo sed, pellentesque diam. Sed posuere hendrerit rutrum. Etiam bibendum, libero eget consectetur imperdiet, augue diam rhoncus velit, rutrum rutrum metus massa sed eros. Nunc pulvinar tristique sem, non ullamcorper ante feugiat ac. Ut in purus pharetra, ultricies metus et, euismod turpis. Morbi luctus blandit ligula eget posuere. Vestibulum quis purus tellus. Phasellus sed molestie erat. Integer non velit scelerisque, rutrum dolor et, hendrerit dui. Sed id justo velit. Suspendisse lacinia ex lacus, nec vulputate nisl mollis in. Nulla vulputate vulputate turpis vel rhoncus. Curabitur dapibus ut enim vitae aliquam. Nullam sed vehicula nisi. Nullam tempus justo sit amet hendrerit feugiat. Curabitur cursus mauris est, eget porttitor leo finibus quis. Phasellus congue in ipsum in dictum. Sed mauris leo, scelerisque luctus felis at, aliquet fringilla dolor. Mauris nec turpis sit amet nisl condimentum dictum. Nullam ante erat, congue ut lacus sed, placerat mollis mi. Proin faucibus sapien id molestie consectetur. Nulla semper enim nec eros rhoncus hendrerit. Morbi eget dui sed neque blandit accumsan. Cras eget urna posuere, euismod enim eget, dapibus nunc. Cras dictum risus a lorem maximus, et mollis libero aliquet. Cras pretium ultrices purus eget maximus. Morbi at efficitur quam. Etiam porta sapien ut quam mattis congue. Donec luctus mi ut tortor accumsan, eu ullamcorper erat feugiat. Aenean eget lacus neque. Morbi eget purus quis lorem facilisis dapibus in non tellus. Maecenas semper odio metus, sed venenatis nisl luctus vel. Aliquam a ultrices mauris. Duis magna ipsum, commodo eu metus quis, dictum mattis tortor. Donec nisi sapien, rhoncus vitae metus ut, porttitor ultrices ante. Phasellus metus ex, fringilla ac pellentesque ut, dictum ut magna. Nam pulvinar velit sed lorem fermentum porta. Maecenas sit amet finibus elit. Cras mattis urna a leo feugiat ullamcorper. Quisque eget feugiat nulla. Praesent at odio in dui mattis dignissim vel a eros. Nunc enim enim, elementum nec mauris laoreet, tristique consectetur felis. Nulla in tellus et ipsum pellentesque vehicula et accumsan eros. Phasellus id iaculis sapien. Nullam erat diam, congue a mauris sit amet, faucibus convallis eros. Vivamus suscipit nulla interdum sodales mollis. Cras varius sem id nunc porta tempor. Quisque hendrerit eu est in feugiat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam efficitur, magna eu venenatis consectetur, enim dui molestie neque, in laoreet orci magna a nisl. Duis eleifend iaculis ullamcorper. Fusce id suscipit velit, a pharetra elit. Nullam maximus metus in elit malesuada sagittis. Nulla in lobortis elit. Aliquam suscipit viverra nisi ac rhoncus. Donec feugiat egestas odio, nec eleifend ipsum dictum in. Aenean sit amet nisi aliquet elit rutrum ullamcorper quis eget tellus. Quisque nec velit lectus. Duis lobortis ultricies nisi. Phasellus sollicitudin iaculis dignissim. Integer eget dolor at eros condimentum tincidunt ut dictum enim. Vestibulum rhoncus facilisis tellus, eu aliquet est scelerisque vel. Nullam mollis et justo sed scelerisque. Mauris faucibus interdum tellus, sit amet lacinia eros mollis sit amet. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla sollicitudin, lacus et ullamcorper imperdiet, mauris dui egestas libero, a finibus nunc erat eget odio. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Quisque pharetra sem ut sapien semper, sed commodo elit hendrerit. Nullam at eleifend nibh. Nullam id elementum quam. Phasellus placerat urna nisl, sit amet bibendum lectus semper eget. Maecenas vel eros et lectus blandit lacinia. Maecenas in suscipit sapien, eu pulvinar metus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus ultrices efficitur nisi, vitae tristique leo. In imperdiet nibh porta pharetra viverra. Morbi et lorem sit amet quam pharetra tincidunt vel at quam. Suspendisse lacinia risus in lectus condimentum aliquet. Integer vestibulum massa tortor, eu porta massa tempor et. Maecenas mi tellus, varius non dictum at, viverra in libero. Aenean ac erat ornare, commodo lacus sed, convallis libero. Etiam at lectus arcu. Aliquam erat volutpat. Sed pharetra fringilla ante, non pellentesque lectus scelerisque at. Nullam rhoncus imperdiet ipsum, eu viverra est posuere nec. Sed tincidunt tortor vitae sem maximus sollicitudin. Sed lacus metus, malesuada at elementum at, aliquam ut urna. Ut finibus sed elit nec porta. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed aliquet accumsan suscipit. Morbi accumsan accumsan erat nec vestibulum. Duis placerat sem nec mauris rutrum luctus. Pellentesque sit amet urna vitae ligula maximus aliquam. Etiam tempus augue posuere lacus lacinia viverra non vel arcu. Sed volutpat fermentum elit, non feugiat tortor. In lacus nibh, interdum quis tempor sed, gravida vitae diam. Duis blandit metus a lacinia auctor. Curabitur vel pellentesque turpis. Nunc rutrum varius dolor, eget sollicitudin dolor euismod eget. Ut porta elementum orci, et euismod felis vulputate sed. Proin et scelerisque massa. Sed non lorem ac leo lacinia dictum. Cras ut suscipit est. Suspendisse viverra eros ac consequat aliquet. Proin elit nisl, consequat facilisis magna in, bibendum convallis velit. Maecenas quis mi lacinia, condimentum orci vel, aliquam ligula. Phasellus leo dolor, malesuada eget dui at, lacinia tristique sem. Aenean consectetur, quam sit amet condimentum dapibus, enim nisi iaculis felis, et porta enim tellus sit amet nunc. Vestibulum rhoncus libero et libero euismod dapibus. Donec nisl urna, gravida in porttitor id, ullamcorper ac nisl. Nunc venenatis magna et venenatis cursus. Praesent porttitor sagittis nunc. Nunc tellus diam, facilisis ac pretium nec, rhoncus eget est. Nulla facilisi. In semper massa condimentum, varius ligula sed, fermentum est. Fusce lobortis faucibus orci vel lacinia. Nulla facilisis dui eu eros pretium maximus. Nam consectetur fermentum justo sit amet cursus. Integer pretium sapien vel molestie facilisis. Aenean et nulla purus. In sit amet enim tellus. Suspendisse potenti. Curabitur ac quam vitae justo auctor euismod non eget elit. Cras at mi urna. Phasellus id tempor nunc. Aliquam auctor posuere efficitur. Praesent dignissim ante arcu, eget semper enim commodo sed. In in arcu augue. Vestibulum vel pretium odio. Vestibulum at nisi eu neque ultricies scelerisque. Sed ligula libero, commodo ac maximus in, tristique at justo. Morbi vel malesuada sapien, eu pretium enim. Praesent molestie gravida bibendum. Nunc est augue, iaculis nec iaculis et, sagittis nec nisi. Ut in ultricies sapien. Nam fringilla at augue eget rhoncus. Sed diam felis, auctor id nisl a, volutpat scelerisque ante. Etiam tempor eleifend fringilla. Sed mauris nisi, tristique in sollicitudin a, viverra vitae libero. Mauris fringilla eget lorem vel efficitur. Duis mattis massa nisl, a hendrerit mauris iaculis nec. Integer fermentum mi sed tortor consectetur, eu tincidunt lacus pharetra. Etiam cursus orci lacus, in porta est condimentum et. Quisque vitae neque placerat, maximus nisi non, malesuada nisi. Praesent pulvinar volutpat euismod. Vestibulum ornare mi sed imperdiet placerat. Vivamus ac euismod ipsum. Quisque ultricies dui non placerat pharetra. Nam ornare iaculis urna vel blandit. Donec bibendum pellentesque cursus. Fusce gravida, eros in faucibus molestie, risus purus mollis leo, eget ultrices ligula massa ut orci. Proin vel hendrerit ligula. In vitae erat tristique, laoreet eros porttitor, semper diam. Cras varius nunc et laoreet mattis. Nulla condimentum augue quis commodo pulvinar. Ut a dictum massa, eget ornare felis. Integer nisl orci, semper id ultricies vehicula, commodo tristique tortor. Etiam imperdiet porttitor ullamcorper. Phasellus sagittis risus a eleifend ultricies. Nunc arcu orci, consequat ut euismod sed, feugiat vitae odio. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris id rutrum quam. Donec maximus fringilla porta. Aenean eget rutrum metus. Fusce lobortis tellus est, vitae elementum ipsum congue ac. Mauris ac rutrum lorem, ac iaculis eros. Vestibulum nec placerat orci. Cras in odio rhoncus, convallis dui a, placerat elit. Donec eu gravida ipsum. Morbi gravida magna eget turpis eleifend porttitor. In ac rutrum felis. Curabitur rhoncus bibendum pellentesque. Quisque ultrices auctor ligula sed cursus. Ut dignissim non dolor id venenatis. Vivamus purus quam, vulputate vitae tempus vel, porta a lectus. Sed tincidunt, libero id gravida fringilla, elit enim condimentum quam, at vestibulum metus tortor vel purus. Quisque tellus odio, pellentesque sit amet sapien sit amet, ullamcorper semper libero. Integer et nibh ut dolor porttitor malesuada. Fusce vel blandit diam. Suspendisse ullamcorper ut leo vel posuere. Sed eu varius dolor. Morbi scelerisque urna vitae pharetra tempus. Fusce a lacus at enim consectetur ullamcorper sed in ligula. Aliquam mattis aliquet ultricies. Phasellus pretium massa sed mi tempor, a rutrum erat vehicula. Morbi scelerisque nunc purus, in tempus urna euismod id. Maecenas finibus, quam vel rutrum ultricies, lacus lorem placerat augue, sed convallis dui quam nec eros. Duis vel vestibulum turpis, ut fermentum ligula. Ut scelerisque augue vel vulputate faucibus. Donec eget elit vel magna consequat blandit. Quisque viverra sapien massa. Proin eu consectetur quam. Nunc lacinia ex id efficitur lacinia. Nulla fermentum blandit fermentum. Donec at consequat felis. Fusce ornare facilisis nibh, ac tempus est aliquam a. Nullam sollicitudin blandit leo eu placerat. Aenean diam velit, varius quis augue pulvinar, lacinia aliquet ipsum. Praesent eu ultrices diam. Maecenas ultricies eu lacus ut viverra. Morbi facilisis eros ut varius interdum. Etiam auctor consectetur justo a efficitur. Suspendisse euismod at orci eget facilisis. Morbi fermentum ornare efficitur. Morbi vitae tincidunt lacus. Integer sed varius dolor. Phasellus a nisi fringilla, elementum magna non, finibus sem. Donec nec elit vel eros tempor feugiat nec a orci. Nunc imperdiet risus eget neque volutpat, quis rhoncus libero mollis. Sed bibendum ex eget ipsum feugiat aliquam. Integer turpis enim, consectetur vel orci sed, accumsan euismod purus. Quisque lorem lacus, suscipit ut eleifend eu, fermentum sit amet odio. Donec quis feugiat tellus. Etiam vehicula cursus neque, vel congue est luctus et. Curabitur interdum felis sapien, fringilla molestie nulla pellentesque et. Nam sem turpis, pellentesque eget nunc ac, fermentum euismod diam. In hac habitasse platea dictumst. Donec dapibus diam nec ex consectetur, id pulvinar tortor tempus. Nam fringilla ligula maximus nibh volutpat, vel sagittis lacus blandit. Donec accumsan, sapien vitae fringilla cursus, mauris sem vulputate elit, sit amet egestas nulla justo sed nulla. Mauris ut blandit dui. Etiam rutrum facilisis hendrerit. Morbi mauris tellus, commodo ac sapien quis, mollis interdum eros. Vivamus aliquet, lorem a lacinia blandit, enim tortor finibus dui, a varius odio justo sed odio. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris vestibulum turpis sed dui consequat suscipit. Curabitur mollis, mauris ac facilisis venenatis, lacus orci pharetra orci, vel interdum diam arcu non enim. Proin posuere ipsum vel lorem luctus vulputate et a neque. Nam varius, magna at sagittis pharetra, augue sapien bibendum nulla, quis commodo mi lectus vel magna. Nam volutpat purus ac mi malesuada congue. Curabitur ut venenatis est, tincidunt egestas ante. Proin fringilla est vel viverra consequat. Etiam aliquam, sapien ut convallis bibendum, lacus metus pretium sapien, vitae pharetra justo purus nec nibh. Donec placerat eu nisi ac vestibulum. Nunc facilisis tempor felis, et tincidunt sem consectetur volutpat. Sed id nibh sed metus bibendum suscipit quis id augue. Morbi dapibus, augue ac laoreet maximus, lacus dolor suscipit nisi, pulvinar luctus risus ligula eu lorem. Mauris non lacinia ligula. Duis aliquam diam nisi, at rutrum lectus egestas id. Mauris finibus commodo dignissim. Morbi aliquet, velit sit amet ullamcorper pretium, sapien massa ornare mauris, vehicula vestibulum ante ex tempor erat. Donec convallis, odio volutpat dapibus vulputate, odio orci congue enim, vitae eleifend sem ex sit amet eros. In hac habitasse platea dictumst. Maecenas at ornare elit. Vivamus sit amet arcu non diam tincidunt consequat. Ut non arcu gravida, tempor nunc sit amet, tempus leo. Phasellus vitae tristique augue. In convallis, eros eu rhoncus tristique, sapien enim lobortis sapien, ut scelerisque sem eros ac tellus. Nullam sit amet risus in mi vulputate mollis id in eros. Aenean pulvinar quam ut diam pharetra euismod. Aenean aliquet ornare nisi eget aliquet. Praesent vehicula luctus ligula, ut interdum enim suscipit et. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Ut bibendum semper orci eu tempus. Donec egestas mauris vel tristique tempus. Phasellus volutpat faucibus risus, eu fermentum erat elementum ut. Proin feugiat turpis tincidunt ex maximus, vel tempus leo cursus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum vulputate metus augue, nec auctor dui elementum aliquet. Cras at magna vel neque ornare bibendum. Cras quis pellentesque lacus. Donec maximus efficitur dignissim. Nulla facilisi. Nam eget fermentum lectus. Donec quis metus sit amet ligula euismod ornare id vitae lacus. Donec lectus lacus, semper sit amet tellus nec, pulvinar tincidunt libero. Phasellus nulla ligula, semper rhoncus fringilla laoreet, consectetur et ex. Cras ac aliquet elit, dignissim sollicitudin justo. Aenean viverra turpis ac leo laoreet, sed congue justo suscipit. Proin blandit, arcu ac cursus porta, dolor erat mollis tellus, a bibendum dui lacus non turpis. Donec placerat facilisis quam non feugiat. Donec eget eros diam. Donec congue, turpis dictum ornare sagittis, ante neque sodales enim, non porttitor diam quam in mi. Aenean efficitur, neque id consectetur pellentesque, odio neque posuere sem, sit amet pharetra orci lacus ac massa. Duis non dolor eget libero pulvinar bibendum. Cras nec auctor diam. Suspendisse convallis erat nisi, sit amet scelerisque lorem viverra in. Proin eu interdum orci, sit amet fringilla nisl. Aliquam eros magna, congue ut arcu eu, tincidunt laoreet nulla. Suspendisse fermentum enim vel erat placerat dapibus. Pellentesque ac massa tempor, fringilla tortor ac, semper mi. Duis dolor nunc, iaculis nec faucibus vitae, vulputate eu nisl. Quisque egestas euismod magna. Nullam at ex ac magna rutrum porttitor. Etiam vitae mi nec odio pulvinar commodo. Ut in nisi erat. Proin suscipit diam nibh, in egestas turpis gravida sed. Donec dictum laoreet tempus. Duis ultrices porta nisi et luctus. Integer convallis magna id felis sollicitudin cursus. Morbi quis felis lacus. Vivamus suscipit est eu odio faucibus molestie. Aliquam erat volutpat. Morbi ac faucibus magna, sed interdum lacus. Praesent ut erat hendrerit, consequat neque id, maximus nisl. Mauris maximus, metus ut vehicula tristique, massa elit convallis est, ac sagittis neque magna non arcu. Praesent ut arcu a augue suscipit semper. Cras non diam dolor. Ut ut nunc in erat vestibulum eleifend at et sapien. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque nibh enim, finibus quis condimentum a, rutrum vel arcu. In aliquet lobortis bibendum. Fusce non ornare magna. Maecenas ultricies odio justo, ac vestibulum libero interdum ut. Pellentesque orci nunc, elementum vitae sapien vitae, porttitor pretium elit. Duis scelerisque non diam vel eleifend. Vestibulum porta ultrices risus quis consectetur. Maecenas mi quam, mattis ac augue ac, imperdiet sagittis eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam quis massa sed massa condimentum fringilla. Vestibulum nec magna nisi. Sed commodo odio et lobortis cursus. Sed augue lectus, varius non neque eget, eleifend aliquam ligula. Cras aliquam odio a cursus iaculis. Phasellus nec tincidunt arcu. Nullam at nisi imperdiet, malesuada ante quis, venenatis nisl. Nunc a libero luctus, tincidunt quam sed, interdum erat. Vestibulum condimentum a mi quis elementum. Phasellus elit magna, ornare eu scelerisque facilisis, rhoncus nec diam. Nulla odio sapien, ultricies vitae venenatis sed, porttitor sit amet quam. Cras sem neque, bibendum pulvinar eros faucibus, tristique maximus lorem. Etiam suscipit, est sed interdum viverra, purus eros semper leo, ut pellentesque dolor massa nec risus. Duis eget nisl eget nibh cursus maximus nec ac tortor. Sed nec risus sit amet augue rutrum iaculis. Curabitur sollicitudin, velit ut congue vestibulum, orci sem feugiat erat, eu tempor ipsum lacus eu velit. Pellentesque tempor diam ut fermentum auctor. Pellentesque sed nisi pellentesque, blandit quam vitae, ornare enim. Donec sodales pharetra urna, vitae lobortis mi commodo eget. Ut facilisis vulputate tellus in tristique. Donec consectetur dapibus eleifend. Aenean et enim arcu. Mauris id elit lacinia, varius lectus sit amet, imperdiet odio. Nam vestibulum lorem id dignissim pretium. Mauris ac nisi sed magna varius tristique. Etiam lobortis ultricies quam. Cras at massa et massa feugiat cursus at in neque. Donec quis tellus pellentesque, sagittis nulla ut, pretium nulla. Nam imperdiet nunc ut urna iaculis, eget ornare nunc dapibus. Nunc nibh turpis, auctor nec ante ac, consequat malesuada diam. Sed arcu velit, malesuada ac libero sit amet, aliquet egestas velit. Cras mollis suscipit leo, non facilisis odio accumsan vel. Donec egestas enim vitae turpis bibendum, quis auctor odio aliquam. In non ipsum at orci sodales facilisis. Mauris lacinia, ipsum vitae consectetur convallis, risus libero aliquet ligula, at porttitor lectus nunc nec arcu. Morbi aliquet ex nisl, sed sollicitudin sem dapibus nec. Sed viverra mi ut velit pharetra tristique. Quisque cursus condimentum tincidunt. Maecenas malesuada malesuada posuere. In mauris metus, convallis sit amet euismod at, suscipit vitae enim. Quisque lobortis metus vel ipsum malesuada, a dignissim risus consequat. Aenean posuere mauris vitae neque viverra egestas. Etiam id felis id leo semper consectetur eget ut odio. Donec convallis, elit quis laoreet tempor, nulla lectus cursus enim, sit amet facilisis nunc nisi sed felis. In porttitor erat in pretium ullamcorper. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nulla egestas erat sit amet velit ornare, vitae sagittis quam dapibus. Curabitur nisl mauris, sagittis eu eros imperdiet, dictum dapibus mauris. Pellentesque interdum convallis lorem, vitae mattis mauris viverra et. Nulla vel orci maximus, pulvinar dui quis, imperdiet massa. Phasellus blandit viverra est id ullamcorper. Vivamus pretium nibh eget elit accumsan porttitor. Sed rutrum dictum vehicula. Nunc laoreet euismod ipsum. Praesent sit amet magna et tellus lacinia rhoncus mollis vel ante. Vivamus a scelerisque mi. Donec aliquam lacus enim, ac finibus diam tempor a. In a viverra orci, sed mattis urna. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam ut sem ex. Proin vel tortor vel est tempor elementum. Aliquam egestas ligula ac nulla dapibus elementum. Donec dapibus ipsum condimentum, feugiat nunc ac, consequat ipsum. Cras molestie tortor eu nibh posuere, at scelerisque sem commodo. Duis bibendum feugiat felis nec tempus. Sed in lacinia mauris. Ut et velit nunc. Nullam suscipit lectus a nisi scelerisque semper. In tristique arcu nec enim suscipit, eget rhoncus nisl scelerisque. Fusce mattis lectus sed mollis convallis. Aliquam porttitor nibh id lorem blandit, et aliquam mauris suscipit. Aliquam neque erat, ullamcorper a leo a, accumsan hendrerit nisl. Sed sed semper odio. Mauris mattis pulvinar nunc, quis pharetra augue maximus mattis. Curabitur pellentesque tristique interdum. Maecenas laoreet leo nunc, posuere tincidunt arcu aliquam quis. Sed quis elit a velit scelerisque porttitor vitae auctor ex. Fusce eget imperdiet felis, id efficitur ligula. Mauris eu ullamcorper purus, sed egestas erat. Nam pellentesque felis sed odio lobortis, ac elementum dolor bibendum. Proin eget interdum nulla, sed ornare dolor. Etiam eu magna risus. Cras metus dui, malesuada at rutrum euismod, ultrices id eros. Integer at magna euismod, rutrum lacus et, auctor nibh. Quisque malesuada, risus in porttitor varius, augue lectus fermentum justo, eu lobortis massa ante et dui. Mauris tincidunt leo sit amet ante cursus, in varius orci mattis. Fusce luctus augue at arcu euismod, id posuere tellus tempor. Sed varius massa sed ullamcorper ultrices. Phasellus bibendum elit eget gravida ultrices. Praesent augue diam, laoreet non dolor nec, tempor consequat enim. Sed placerat dolor egestas auctor pretium. Morbi sodales porttitor augue sed tempus. Praesent elementum lobortis varius. In hac habitasse platea dictumst. Suspendisse potenti. Praesent vitae risus eget nisi feugiat condimentum ac aliquam mi. Etiam in nulla ac tellus scelerisque vehicula ut auctor nisl. Fusce ut porttitor sapien. Pellentesque ut tincidunt lectus. Pellentesque fringilla eros nec leo pellentesque, at elementum nunc elementum. Ut bibendum sapien nulla, in lacinia eros pulvinar quis. Pellentesque nibh nibh, condimentum id orci vel, pretium dapibus sapien. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean malesuada imperdiet molestie. Vivamus tristique ex in elit scelerisque laoreet. Nunc facilisis ac augue nec pulvinar. Fusce tincidunt, ligula consectetur euismod aliquet, lectus velit facilisis neque, vel porttitor felis sem in tellus. Integer commodo diam justo, non consectetur sem viverra in. Pellentesque sem odio, eleifend id turpis nec, porta ultrices dolor. Ut accumsan tincidunt est in pellentesque. Sed ut augue a velit imperdiet placerat non feugiat nibh. Nullam facilisis magna quis ipsum blandit volutpat. Aenean cursus luctus ex, et convallis nulla viverra ac. Sed ac odio sapien. Cras vulputate odio ut orci mattis mollis. Aliquam luctus ut arcu sit amet varius. Duis vel laoreet mauris. Etiam dignissim magna nec diam scelerisque, id eleifend neque pulvinar. Morbi fermentum molestie leo et fringilla. Fusce nec sagittis lacus. Nullam tristique sagittis erat, vitae molestie justo cursus a. Suspendisse lobortis tortor vel urna vehicula ullamcorper. Etiam eget dignissim magna. Vivamus eget erat hendrerit, ultricies est at, accumsan orci. Phasellus et arcu id elit finibus hendrerit non non justo. Aenean eu magna magna. Etiam commodo non libero eu malesuada. Donec consequat finibus lorem, placerat suscipit arcu imperdiet et. Pellentesque vel tortor facilisis, aliquam mi at, interdum lacus. Morbi laoreet diam vitae quam rhoncus volutpat. Quisque tincidunt ante quis ante scelerisque, sed suscipit justo porttitor. Pellentesque hendrerit, est quis pellentesque tempus, tortor velit posuere felis, vitae iaculis ligula massa sed nisl. Sed non sagittis tellus. Nullam ligula quam, tempor ac leo id, scelerisque eleifend metus. Proin tincidunt, ante at ultrices tempor, dolor nibh accumsan diam, nec tempus mi lacus et sem. Praesent imperdiet luctus turpis. Suspendisse aliquam, magna vitae blandit sodales, eros odio porta turpis, nec lacinia turpis nulla sit amet magna. Quisque turpis justo, sodales quis felis non, euismod suscipit dolor. Sed vel lectus ut dolor finibus euismod in elementum purus. Vestibulum egestas ultricies mauris, suscipit tincidunt purus dapibus vitae. Etiam mauris erat, bibendum imperdiet dapibus eget, euismod nec leo. Morbi placerat mauris vitae ex consectetur suscipit. Pellentesque euismod tincidunt ornare. Nulla ac venenatis felis. Donec id lorem metus. Nunc pharetra est sit amet mi consequat tempor. Suspendisse ultrices eget lectus ac vestibulum. Maecenas in lacus augue. Ut et mollis libero, vitae commodo odio. Donec pulvinar metus aliquet tortor ultrices bibendum. Suspendisse potenti. Maecenas finibus mi et interdum lacinia. Vivamus interdum eros sit amet justo maximus accumsan. Cras augue metus, finibus sed nunc in, tempor hendrerit nisl. Phasellus maximus id ex a tempor. Phasellus libero dui, efficitur non arcu vitae, hendrerit molestie nibh. Cras faucibus nisl lacus, in viverra magna aliquam a. Sed mauris nunc, efficitur quis libero quis, sagittis ultrices arcu. Nunc facilisis ornare malesuada. Nam id dolor a libero cursus efficitur. Aliquam varius, ante id dignissim bibendum, metus odio suscipit purus, in consequat massa est ac ex. In sit amet felis sed ipsum consequat pulvinar quis in ante. Sed ultricies tristique porta. Curabitur at leo dui. Donec sit amet lectus sed purus egestas aliquam. Nulla consectetur neque consectetur urna sollicitudin, fermentum auctor felis ultricies. Donec laoreet pellentesque nulla, nec faucibus sem. Ut id pharetra diam. Proin pharetra faucibus tellus at cursus. Maecenas interdum eleifend risus, non cursus orci malesuada vel. Nam eget congue odio, quis rutrum est. In mollis neque vitae ultricies feugiat. Sed finibus eget nisl quis consectetur. Maecenas a interdum ex, a fermentum augue. Nulla facilisi. In at ultrices ex. Nulla dapibus auctor odio quis fringilla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus accumsan, nisl ac viverra consequat, dui ex rutrum dolor, et volutpat ante enim in mauris. Suspendisse nec molestie ligula, sit amet porttitor magna. Ut suscipit purus eu justo ornare, suscipit rhoncus turpis pharetra. Maecenas imperdiet ullamcorper ligula sed lacinia. Praesent gravida suscipit enim. Praesent feugiat ornare augue eget pretium. Ut aliquam, diam vitae pulvinar convallis, tortor est hendrerit risus, eget porta felis mauris ac neque. Curabitur in faucibus elit. Suspendisse volutpat egestas tristique. Quisque dictum odio non hendrerit ultricies. Aenean eget lacus lacus. Sed efficitur est quis purus mollis rutrum. Suspendisse turpis ex, placerat eget tincidunt quis, hendrerit sit amet ex. Vestibulum id vehicula dui. Cras vel arcu sed nisi mattis interdum. In semper et tellus eget gravida. Integer accumsan feugiat metus. Aenean ut erat velit. Integer mollis porttitor lectus sed efficitur. Proin at malesuada mi. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque velit massa, porttitor maximus volutpat cursus, convallis vel nisl. Proin blandit elit in magna interdum pretium. Curabitur eu leo est. Duis a magna mauris. Proin aliquam venenatis erat ac dictum. Donec sit amet orci eu mauris posuere sagittis nec accumsan orci. Donec eget magna malesuada, ultrices odio et, semper ligula. Mauris non maximus velit. Curabitur sit amet lorem eu arcu vulputate ultricies et non eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse odio justo, imperdiet vel ipsum at, volutpat auctor lacus. Mauris dui felis, mollis vel scelerisque ut, pulvinar in metus. Cras lectus libero, feugiat eget vulputate non, consectetur sit amet urna. Fusce laoreet tincidunt lorem vitae aliquet. Vestibulum libero felis, facilisis sollicitudin ullamcorper eget, varius eu diam. Morbi sit amet elementum lacus. Donec suscipit vitae ipsum quis feugiat. In nec ante turpis. Nunc quis ipsum non tellus fermentum feugiat in quis est. Vestibulum tristique et quam non euismod. Curabitur dapibus nisl vitae porttitor sodales. Quisque mi felis, elementum ac massa in, mollis commodo quam. Vivamus justo turpis, aliquet at sem in, placerat interdum orci. In accumsan elit sapien, non venenatis diam efficitur elementum. Nunc quis ligula quis nulla fermentum ornare. Phasellus facilisis hendrerit quam id volutpat. Integer tincidunt dolor sed eros cursus, vel tincidunt nunc facilisis. Mauris non auctor massa. Nam dui nibh, commodo sed laoreet et, ultricies nec urna. Curabitur dapibus porta nibh sed efficitur. Donec pretium ornare felis a hendrerit. Cras varius lobortis nisi viverra aliquam. Vivamus rutrum turpis id dui vestibulum cursus. In velit elit, tincidunt sed risus quis, tristique vestibulum purus. In eu arcu id nibh congue posuere. Sed vel erat in nisi volutpat ullamcorper.";
    $start = rand(0, strlen($text) - $length);
    return ucfirst(substr($text, $start, $length));
}

function first_name_generator()
{
    $first_names = array(
        "Katelyn", "Cierra", "Farrell", "Gil", "Isaac", "Beverly", "Len", "Kayleah", "Rica", "Candida", "Bonita", "Raquel", "Sonia", "Geraldo", "Otto", "Esperanza", "Cassidy", "Katrina", "Lilian", "Lark", "Josepha", "Bethney", "Kole", "Jessika", "Aiden", "Andrew", "Darian", "Braden", "Alexina", "Gray", "Linden", "Shanae", "Marlin", "Chayanne", "June", "Egbert", "Rica", "Jenny", "Marjorie", "Wynne", "Hilaria", "Janine", "Ilean", "Brynne", "Ilbert", "Kiersten", "Annmarie", "Serine", "Wilbur", "Bram", "Angelina", "Roydon", "Paulina"
    );
    return $first_names[array_rand($first_names, 1)];
}

function last_name_generator()
{
    $last_names = array(
        "Payne", "Ramos", "Tasker", "Beckham", "Gutierrez", "Elwin", "Thomas", "Grey", "Howe", "Darrell", "Giles", "Thacker", "Georgeson", "Washington", "Hartford", "Akerman", "Summerfield", "Suarez", "Marley", "Tollemache", "Mills", "Queen", "Haggard", "Andrewson", "Gosse", "Rodriguez", "Rhodes", "Sevege", "Guerra", "Christians", "Southgate", "Bateson", "Parish", "Brock", "Nicolson", "Garrod", "Slater", "Huxley", "Spearing", "Rivero", "Kynaston", "Warner", "Molina", "Danell", "Romilly", "Marley", "Samuel", "Marquez", "Sims", "Henryson", "Thorpe", "Rose", "Norman"
    );
    return $last_names[array_rand($last_names, 1)];
}

function test_create_new_reservation($instance_id, $user_id)
{
    global $global_conn, $merchant_class;
    $results = db_query($global_conn, "SELECT * FROM purchasable_seat_instance WHERE show_instance_id = " . db_escape($instance_id) . " AND seat_status = " . db_escape("SEAT_STATUS_AVAILABLE") . ";");
    $user = new user($user_id);
    $seat_count = rand(1, 4);
    $starting_row = max(count($results) - $seat_count - 1, $seat_count);
    if ($starting_row < 0) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sold Out <br />"; //ob_flush(); flush();
        return false;
    }
    $show_instance = new show_instance($instance_id);
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reservation for " . $seat_count . " on " . $show_instance->datetime . " "; //ob_flush(); flush();
    $cart = new cart();
    $cart->user_id = $user_id;
    $cart->ip = "test";
    $cart->accessed = date("Y-m-d H:i:s");
    $cart->modified = date("Y-m-d H:i:s");
    $cart->created = date("Y-m-d H:i:s");
    $cart->paid = "0000-00-00 00:00:00";
    $cart->set_cart();
    for ($i = 0; $i < $seat_count; $i++) {
        if (isset($results[$seat_count + $i])) {
            $purchasable_seat_instance_id = $results[$seat_count + $i]['purchasable_seat_instance_id'];
            $cart->cart_item_new("purchasable_seat_instance", $purchasable_seat_instance_id, 1, false);
        }
    }

    $amount = $cart->cart_sub_total();
    $merchant = new $merchant_class();
    $merchant->amount = $amount;
    $merchant->card_number = "4242424242424242";
    $merchant->exp_month = "0" . rand(1, 9);
    $merchant->exp_year = rand(18, 23);
    $merchant->cvc = '';
    $merchant->invoice_id = random_string(19);
    $merchant->last_name = $user->user_name_last;
    $merchant->first_name = $user->user_name_first;
    $merchant->address = $user->user_address_line1;
    $merchant->zip = $user->user_zip;

    $merchant->charge();
    $transaction_id = $cart->cart_checkout_actualize();
    $merchant->create_finacial_details($transaction_id);
    echo "$" . $merchant->amount . " DONE <br />"; //ob_flush(); flush();
}

function test_create_new_registration($instance_id, $user_id)
{
    global $merchant_class;
    set_time_limit(120);
    $user = new user($user_id);
    $instance = new purchasable_registration_instance($instance_id);

    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Registration for on " . $instance->purchasable_registration_instance_datetime;
    $cart = new cart();
    $cart->user_id = $user_id;
    $cart->ip = "test";
    $cart->accessed = date("Y-m-d H:i:s");
    $cart->modified = date("Y-m-d H:i:s");
    $cart->created = date("Y-m-d H:i:s");
    $cart->paid = "0000-00-00 00:00:00";
    $cart->set_cart();

    $cart->cart_item_new("purchasable_registration_instance", $instance->purchasable_registration_instance_id, 1, true);

    $amount = $cart->cart_sub_total();
    $merchant = new $merchant_class();
    $merchant->amount = $amount;
    $merchant->card_number = "4242424242424242";
    $merchant->exp_month = "0" . rand(1, 9);
    $merchant->exp_year = rand(18, 23);
    $merchant->cvc = '';
    $merchant->invoice_id = random_string(19);
    $merchant->last_name = $user->user_name_last;
    $merchant->first_name = $user->user_name_first;
    $merchant->address = $user->user_address_line1;
    $merchant->zip = $user->user_zip;

    $merchant->charge();
    $transaction_id = $cart->cart_checkout_actualize();
    $merchant->create_finacial_details($transaction_id);
    echo "$" . $merchant->amount . " DONE <br />";
}