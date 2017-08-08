<?php

function web_show_editor()
{
    if (filter_input(INPUT_POST, 'show_id') !== '0') {
        $show = new show(filter_input(INPUT_POST, 'show_id'));
    } else {
        $show = new show();
    }
    return "<fieldset><legend>Show Properties</legend>" . $show->admin_edit_form() . "</fieldset>";
}

function web_shows_list()
{
    return "<div class='web-shows-list'>" . show::admin_list_shows() . "</div>";
}

function web_show_people()
{
    if (filter_input(INPUT_POST, 'show_id') < 1) {
        die("Need a show_id");
    }
    $string = "<h2 style='text-align:center;' >Cast and Crew</h2><ul class='show-people-list'>";
    foreach (show_people::get_all_for_show(filter_input(INPUT_POST, 'show_id')) as $show_person) {
        $user = new user($show_person->user_id);
        $string .= "<li><button class='person-minus-button' show_id='" . filter_input(INPUT_POST, 'show_id') . "' user_id='" . $user->user_id . "'>&nbsp;</button>" . $user->user_name_first . " " . $user->user_name_last . " (" . $show_person->show_people_role . ")</li>";
    }
    $string .= "</ul>";
    return $string;
}

function web_show_add_person()
{
    if (filter_input(INPUT_POST, 'show_id') < 1) {
        die("Need a show_id");
    }
    $show_id = filter_input(INPUT_POST, 'show_id');
    $user_id = filter_input(INPUT_POST, 'user_id');
    $show_people_role = filter_input(INPUT_POST, 'show_people_role');
    $show_people_role_type = filter_input(INPUT_POST, 'show_people_role_type');
    show_people::make($show_id, $user_id, $show_people_role, $show_people_role_type);
    return '1';
}

function web_show_remove_person()
{
    if (filter_input(INPUT_POST, 'show_id') < 1 OR filter_input(INPUT_POST, 'user_id') < 1) {
        die("Need a show_id and user_id");
    }
    $show_id = filter_input(INPUT_POST, 'show_id');
    $user_id = filter_input(INPUT_POST, 'user_id');
    show_people::remove($show_id, $user_id);
    return '1';
}

function web_show_instances()
{
    if (filter_input(INPUT_POST, 'show_id') < 1) {
        die("Need a show_id");
    }
    $string = "
	<fieldset id='new-instance'>
	    <legend>New Showtime</legend>
	    <div class='form-row'><label for='new_datetime'>New Time</label><br /><input type='text' class='datetime' id='new_datetime' /></div>
	    <div class='form-row'><button value='Create'>Add</button></div>
	</fieldset>
	<ul class='show-instances'>";
    foreach (show::get_instances(filter_input(INPUT_POST, 'show_id'), true) as $instance) {
        $string .= "<li>";
        if ($instance->get_quantity_reserved() > 0) {
            $string .= "<button class='locked' show_instance_id='" . $instance->show_instance_id . "'>&nbsp;</button>";
        } else {
            $string .= "<button class='delete' show_instance_id='" . $instance->show_instance_id . "'>&nbsp;</button>";
        }
        $string .= " " . date("M jS - g:ia", strtotime($instance->datetime)) . "</li>";
    }
    return $string . "</ul>";
}

function web_show_instance_delete()
{
    global $global_conn;
    if (filter_input(INPUT_POST, 'show_instance_id') < 1) {
        die("Need a show_instance_id");
    }

    $show_instance = new show_instance(filter_input(INPUT_POST, 'show_instance_id'));
    if ($show_instance->get_quantity_reserved() > 0 OR $show_instance->show_instance_id < 1) {
        die("Cannot delete a showtime that has active reservations.");
    }

    db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 0;");
    db_exec($global_conn, "DELETE FROM show_instance_cache WHERE show_instance_id = " . db_escape($show_instance->show_instance_id));
    db_exec($global_conn, "DELETE FROM purchasable_seat_instance WHERE show_instance_id = " . db_escape($show_instance->show_instance_id));
    db_exec($global_conn, "DELETE FROM purchasable_seating_general WHERE show_instance_id = " . db_escape($show_instance->show_instance_id));
    db_exec($global_conn, "DELETE FROM show_instance WHERE show_instance_id = " . db_escape($show_instance->show_instance_id));
    db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 1;");
    return 'Showtime Deleted.';
}

function web_show_instance_create()
{
    if (filter_input(INPUT_POST, 'show_id') < 1 OR filter_input(INPUT_POST, 'new_datetime') === null) {
        die("Need a show_id and new_datetime");
    }
    $show = new show(filter_input(INPUT_POST, 'show_id'));
    if (strtotime(filter_input(INPUT_POST, 'new_datetime')) === -1) {
        return "FAILED: requested datetime seems invalid.";
    }
    $show->create_instance(date("Y-m-d H:i:s", strtotime(filter_input(INPUT_POST, 'new_datetime'))));
    return "New showtime created.";
}

function web_prop_list()
{
    global $global_conn;
    $results = db_query($global_conn, "SELECT * FROM boffice_properties ORDER BY boffice_property_name ASC;");
    $field_i = web_prop_name_first_two_words(str_replace("_", " ", $results[0]['boffice_property_name']));
    $string = "<div id='web-prop-list'><fieldset><legend>" . ucwords($field_i) . "</legend>";
    foreach ($results as $row) {
        $test_i = web_prop_name_first_two_words(str_replace("_", " ", $row['boffice_property_name']));
        if ($field_i !== $test_i) {
            $field_i = $test_i;
            $string .= "</fieldset><fieldset><legend>" . ucwords($field_i) . "</legend>";
        }
        $string .= "
	    <div class='form-row'>
		<label for='property_" . $row['boffice_property_name'] . "' style='width:18em;' >" . str_replace("_", " ", $row['boffice_property_name']) . "</label>
		<input style='width: 6em;' type='text' class='property' id='property_" . $row['boffice_property_name'] . "' boffice_property_name='" . $row['boffice_property_name'] . "' value='" . $row['boffice_property_value'] . "' />
		<button class='save'>Save</button>
		<span class='description' style='font-style: italic;'> " . $row['boffice_property_description'] . "</span>
	    </div>";
    }
    return $string . "</fieldset></div>";
}

function web_prop_name_first_two_words($name)
{
    $words = explode(" ", $name);
    if (isset($words[1])) {
        return $words[0] . " " . $words[1];
    } else {
        return $words[0];
    }
}

function web_prop_update()
{
    global $global_conn;
    $value = filter_input(INPUT_POST, 'boffice_property_value');
    $name = filter_input(INPUT_POST, 'boffice_property_name');
    db_exec($global_conn, "UPDATE boffice_properties SET boffice_property_value = " . db_escape($value) . " WHERE boffice_property_name = " . db_escape($name) . " LIMIT 1;");
    return '1';
}


function web_show_images_list()
{
    global $global_conn, $site_domain, $site_path, $site_files_path;
    if (filter_input(INPUT_POST, 'show_id') < 1) {
        die("Need a show_id");
    }
    $show_id = filter_input(INPUT_POST, 'show_id');
    $string = "
	<fieldset><legend>Images</legend>
	<fieldset id='webadmin-show-image-upload'>
	    <legend>Add Image</legend>
	    <form>
		<div class='form-row'><label for='image_type'>Type</label><select name='image_type' id='image_type' ><option disabled selected value='0'> -- select an image type -- </option>";
    foreach (db_query($global_conn, "SELECT * FROM show_image_types") as $row) {
        $string .= "<option value='" . $row['show_image_type_id'] . "'><span class='name'>" . $row['show_image_type_name'] . "</span> - <span class='description'>" . $row['show_image_type_description'] . "</span></option>";
    }
    $string .= "</select></div>
		<div class='form-row'><label for='image_file'>File</label><input type='file' name='image_file' id='image_file' /></div>
		<input type='hidden' name='show_id' value='$show_id' />
		<button type='SUBMIT' value='Upload'>Upload</button>
	    </form>
	</fieldset>";
    $results = db_query($global_conn, "SELECT * FROM show_image_assignments LEFT JOIN show_image_types USING (show_image_type_id) WHERE show_id = " . db_escape($show_id));
    foreach ($results as $row) {
        $url = "//" . $site_domain . $site_path . $site_files_path . $row['page_file_id'];
        $string .= "<div class='show-image'><button class='delete' page_file_id='" . $row['page_file_id'] . "'>&nbsp;</button><img class='image' src='$url?size=150' alt=\"" . htmlspecialchars($row['show_image_type_name']) . "\" /></div>";
    }
    return $string . "</fieldset>";
}

function web_show_image_upload()
{
    global $global_conn;
    $show_id = intval(filter_input(INPUT_POST, 'show_id'));
    if ($show_id === null or $show_id < 1) {
        die("Need a show_id");
    }
    $show = new show($show_id);
    $upload_table_array = array(
        'name' => 'page_file_name',
        'contents' => 'page_file_contents',
        'size' => 'page_file_size',
        'type' => 'page_file_type',
        'restricted' => 'page_file_access_restricted',
        'title' => 'page_file_title',
        'updated' => 'updated',
        'user' => 'user'
    );

    $result = f_upload_file($upload_table_array, $_FILES['image_file'], $global_conn, 'page_files', 'page_file_id', false, false, $show->title);
    if ($result === false) {
        return '0';
    } else {
        $last_id = $global_conn->lastInsertId();
        db_exec($global_conn, build_insert_query($global_conn, "show_image_assignments", array(
            'show_id' => $show_id,
            'show_image_type_id' => filter_input(INPUT_POST, 'image_type'),
            'page_file_id' => $last_id,
        )));
        return '1';
    }
}

function web_show_image_delete()
{
    global $global_conn;
    $page_file_id = filter_input(INPUT_POST, 'page_file_id');
    if ($page_file_id === null) {
        die("Need a page_file_id.");
    }
    db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 0;");
    db_exec($global_conn, "DELETE FROM page_files WHERE page_file_id = " . db_escape($page_file_id));
    db_exec($global_conn, "DELETE FROM page_files_cache WHERE page_file_id = " . db_escape($page_file_id));
    db_exec($global_conn, "DELETE FROM show_image_assignments WHERE page_file_id = " . db_escape($page_file_id));
    db_exec($global_conn, "SET FOREIGN_KEY_CHECKS = 1;");
    return 'Image Deleted';
}