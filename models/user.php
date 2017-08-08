<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user
 *
 * @author lepercon
 */
class user
{
    public $user_id;
    public $ulogin_id;
    public $user_name_last;
    public $user_name_first;
    public $user_email;
    public $user_reservation_reminders = '1';
    public $user_email_list = '0';
    public $user_address_line1;
    public $user_address_line2;
    public $user_city;
    public $user_state;
    public $user_zip;
    public $user_note;
    public $patron_type_id;
    public $user_account_value;
    public $user_bio;
    public $user_img_url;

    public $user_last_login;

    public $user_is_company = '0';
    public $user_is_volunteer = '0';
    public $user_is_office_admin = '0';
    public $user_is_show_admin = '0';
    public $user_is_finacial_admin = '0';
    public $user_is_class_admin = '0';

    public $user_is_donor = '0';

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'user', 'users', 'user_id');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    static public function current_user()
    {
        if (isset($_SESSION['boffice']['uid'], $_SESSION['boffice']['logged_in'])) {
            return user::get_user_from_ulogin_id($_SESSION['boffice']['uid']);
        } else {
            return new user();
        }
    }

    public function admin_edit_form()
    {
        global $global_conn;
        $options = array();
        foreach (db_query($global_conn, "SELECT * FROM patron_types ORDER BY patron_type_label ASC") as $type) {
            $options[$type['patron_type_id']] = $type['patron_type_label'];
        }
        $elements = array(
            new f_data_element('ulogin_id', 'ulogin_id', 'text'),
            new f_data_element('Last Name', 'user_name_last', 'text'),
            new f_data_element('First Name', 'user_name_first', 'text'),
            new f_data_element('Email', 'user_email', 'text'),
            new f_data_element('Patron Type', 'patron_type_id', 'select', $options),
            new f_data_element('Net Value', 'user_account_value', 'text'),
            new f_data_element('Note', 'user_note', 'wysiwyg'),
            new f_data_element('Donor?', 'user_is_donor', 'select', array("0" => "Not a donor", "1" => "Is a Donor")),
            new f_data_element('Permissions - Volunteer', 'user_is_volunteer', 'select', array("0" => "Cannot Volunteer", "1" => "Can Volunteer")),
            new f_data_element('Permissions - Company', 'user_is_company', 'select', array("0" => "Is not company", "1" => "Is part of the company")),
            new f_data_element('Permissions - Box Office', 'user_is_office_admin', 'select', array("0" => "Does not have access to the box office", "1" => "Manages the box office")),
            new f_data_element('Permissions - Shows', 'user_is_show_admin', 'select', array("0" => "Does not edit show information", "1" => "Manages show information")),
            new f_data_element('Permissions - Finances', 'user_is_finacial_admin', 'select', array("0" => "Does not manage finacial informaiton", "1" => "Manages finacial information")),
            new f_data_element('Uses', 'user_is_selectable', 'select', array("0" => "Is a simple patron", "1" => "Can be cast, hired, used, tasked, or volunteered.")),
            new f_data_element('Uses - Headshot', 'user_img_url', 'file'),
            new f_data_element('Uses - Bio', 'user_bio', 'wysiwyg'),
        );
        $f_data = new f_data($global_conn, 'users', 'user_id', $elements, $this->user_id);
        $f_data->allow_delete = false;
        return $f_data->start();
    }

    public function admin_list_users($href)
    {
        global $global_conn;
        $string = "<ul>";
        foreach (db_query($global_conn, "SELECT * FROM users ORDER BY user_name_last, user_name_first ASC") as $item) {
            $string .= "<li><a href='$href?row_id=" . $item['user_id'] . "' />" . $item['user_name_last'] . ", " . $item['user_name_first'] . "</a></li>";
        }
        $string .= "</ul>";
        return $string;
    }

    public function user_has_any_elevated_privileges()
    {
        return $this->user_is_finacial_admin OR $this->user_is_office_admin OR $this->user_is_show_admin OR $this->user_is_class_admin;
    }

    static public function new_user_public_interface($target_url = false)
    {
        $string = "";
        if (filter_input(INPUT_POST, 'new_user_form') === '1') {
            user::new_user_form_validate();
            if (boffice_error::has_form_errors() === false) {
                $ulogin_uid = user::new_ulogin_user(filter_input(INPUT_POST, 'username'), filter_input(INPUT_POST, 'password'));
                $new_user = new user();
                $new_user->ulogin_id = $ulogin_uid;
                $new_user->user_email = filter_input(INPUT_POST, 'username');
                $new_user->user_email_list = filter_input(INPUT_POST, 'email_list') ? '1' : '0';
                $new_user->user_reservation_reminders = filter_input(INPUT_POST, 'reservation_reminders') ? '1' : '0';
                $new_user->user_name_first = filter_input(INPUT_POST, 'name_first');
                $new_user->user_name_last = filter_input(INPUT_POST, 'name_last');
                $new_user->set();
            }
        }

        if (!filter_input(INPUT_POST, 'new_user_form') OR boffice_error::has_form_errors()) {
            $string .= user::new_user_form($target_url);
        }
        return $string;
    }

    static public function new_ulogin_user($username, $password)
    {
        $ulogin = new uLogin();
        if ($ulogin->Uid($username) > 0) {
            new boffice_error("email already registered", false);
        }
        if (!ulPassword::IsValid($password)) {
            new boffice_error("invalid password", false);
        }
        if ($ulogin->CreateUser($username, $password)) {
            return $ulogin->Uid($username);
        } else {
            new boffice_error("error creating new user", false);
        }
    }

    static private function new_user_form_validate()
    {
        $new_email = filter_input(INPUT_POST, 'username');
        $new_email_confirm = filter_input(INPUT_POST, 'email_confirm');
        if ($new_email !== $new_email_confirm) {
            new boffice_error("The emails entered do not match. Please enter your email and re-enter it in the Confirm Email area. We only use your email for information about your reservations and purchases. For more information, please read our privacy statement.", true, 'email_confirm');
        }
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL) === false) {
            new boffice_error("The email your entered is not valid. Please enter your email and re-enter it in the Confirm Email area. We only use your email for information about your reservations and purchases. For more information, please read our privacy statement.", true, 'username');
        }
        $ulogin = new uLogin();
        if ($ulogin->Uid($new_email) > 0) {
            new boffice_error("The email you provided has already been registered. Please sign in with your email or use our password reset tool.", true, 'username');
        }
        if (filter_input(INPUT_POST, 'password') === null) {
            new boffice_error("Please provide a password.", true, 'password');
        } else if (strlen(filter_input(INPUT_POST, 'password')) < 5) {
            new boffice_error("Your password must be longer than 5 characters.", true, 'password');
        } else if (strlen(filter_input(INPUT_POST, 'password')) > 55) {
            new boffice_error("You password must be less than 55 characters.", true, 'password');
        }
        if (!filter_input(INPUT_POST, 'name_last')) {
            new boffice_error("Please provide your last name.", true, 'name_last');
        }
        if (!filter_input(INPUT_POST, 'name_first')) {
            new boffice_error("Please provide your first name.", true, 'name_first');
        }
    }

    static private function new_user_form($target_url = false)
    {
        $string = "
	    <form id='new_user_form' action='#' method='POST'>
		<label for='name_first'>First Name</label><input type='text' id='name_first' name='name_first' value='%s' required />
		<label for='name_last'>Last Name</label><input type='text' id='name_last' name='name_last' value='%s' required />		
		<label for='username'>Email</label><input type='text' id='username' name='username' value='%s' required />
		<label for='email_confirm'>Confirm Email</label><input type='text id='email' name='email_confirm' value='%s' required />
		<label for='password'>Password</label><input type='password' name='password' id='password' value='%s' required />
		<label for='reservation_reminders'>Email Reservation Reminders</label><input type='checkbox' value='1' checked name='reservation_reminders' id='reservation_reminders' />
		<label for='email_list'>Email Newsletters</label><input type='checkbox' value='1' name='email_list' id='email_list' />
		<button type='sumbit' value='Create Account'>Create Account</button>
		<input type='hidden' name='new_user_form' value='1' />
		<input type='hidden' name='nounce' value='" . ulNonce::Create('new_user') . "' />
		";
        if ($target_url) {
            $string .= "<input type='hidden' name='target_url' value='$target_url' />";
        }
        $string .= "
	    </form>
	    ";
        if (filter_input(INPUT_POST, 'new_user_form')) {
            $string = sprintf($string, filter_input_array(INPUT_POST, array(
                'name_first' => FILTER_SANITIZE_STRING,
                'name_last' => FILTER_SANITIZE_STRING,
                'username' => FILTER_SANITIZE_STRING,
                'email_confirm' => FILTER_SANITIZE_STRING,
                'password' => FILTER_SANITIZE_STRING
            )));
        } else {
            $string = str_replace("%s", "", $string);
        }
        return $string;
    }

    static public function user_login_public_interface($target_url = false)
    {
        global $site_account_url, $site_domain, $site_path;
        $string = "";
        $result = null;
        if (!boffice_logged_in()) {
            if (filter_input(INPUT_POST, 'username') !== null AND filter_input(INPUT_POST, 'password') !== null) {
                if (ulNonce::Verify('login', filter_input(INPUT_POST, 'nounce'))) {
                    $result = boffice_authenticate(filter_input(INPUT_POST, 'username'), filter_input(INPUT_POST, 'password'), filter_input(INPUT_POST, 'remember_me') == '1');
                } else {
                    new boffice_error("There was an error processing your log in, the page had expired. Please try again.");
                }
            }
            if (isset($result) AND $result === true) {
                if ($target_url === false OR $target_url === null) {
                    $target_url = filter_input(INPUT_POST, 'target_url');
                }
                if ($target_url == false OR $target_url == null) {
                    $target_url = $site_domain . $site_path . $site_account_url;
                }
                $user = user::current_user();
                cart::deactivate_all_carts_for_user($user->user_id);
                $user->update_user_last_login();
                header("location: $target_url");
            }

            if ($result === NULL OR $result === false OR boffice_error::has_form_errors()) {
                $string .= user::user_login_public_form($target_url);
            }
        }
        return $string;
    }

    static private function user_login_public_form($target_url = false)
    {
        global $site_path;
        $string = "
	<form id='login' name='login' action='' method='POST'>
	    <label for='username'>Email</label><input type='text' name='username' id='username' />
	    <label for='password'>Password</label><input type='password' name='password' id='password' />
	    <label for='remember_me'>Remember Me</label><input type='checkbox' name='remember_me' id='remember_me' value='1' />
	    <input type='hidden' name='login_form' value='1' />
	    <input type='hidden' name='nounce' value='" . ulNonce::Create('login') . "' />";
        if ($target_url) {
            $string .= "<input type='hidden' name='target_url' value='" . $target_url . "' />";
        }
        $string .= " 
	    <button type='submit' value='Log in'>Log in</button>
	</form>";
        return $string;
    }

    public function update_user_last_login()
    {
        global $global_conn;
        $this->user_last_login = date("Y-m-d H:i:s");
        db_exec($global_conn, "UPDATE users SET user_last_login = " . db_escape($this->user_last_login) . " WHERE user_id = " . db_escape($this->user_id));
    }

    static public function get_bid_from_username($username)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM users WHERE user_email = " . db_escape($username));
        if ($results) {
            return $results[0]['user_id'];
        } else {
            return false;
        }
    }

    static public function get_bid_from_ulogin_id($uid)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM users WHERE ulogin_id = " . db_escape($uid));
        if ($results) {
            return $results[0]['user_id'];
        } else {
            return false;
        }
    }

    static public function get_user_from_ulogin_id($uid)
    {
        $boffice_user_id = user::get_bid_from_ulogin_id($uid);
        if ($boffice_user_id) {
            return new user($boffice_user_id);
        } else {
            return new user();
        }

    }

    static public function login_required($target_url)
    {
        $user = user::current_user();
        if ($user === null OR $user->user_id === null) {
            new boffice_error("<div class='notice'>Gotta be logged in first.</div>", true);
            boffice_html::$html_body_regions[] = new boffice_html_region(user::user_login_public_interface($target_url), boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE);
            echo boffice_template_simple("Login");
            die();
        }
    }

    public function reservations($include_past_reservations)
    {
        global $global_conn;
        $q = "SELECT * FROM reservation
	    LEFT JOIN transaction USING (transaction_id)
	    LEFT JOIN cart USING (cart_id)
	    LEFT JOIN cart_item USING (cart_id)
	    WHERE transaction.user_id = " . db_escape($this->user_id) . " AND reservation_id > 0 AND purchasable_class_id > 0
	    GROUP BY reservation_id
	";
        $return = array();
        foreach (db_query($global_conn, $q) as $cart_item) {
            $cls = $cart_item['purchasable_class'];
            $seat = new $cls($cart_item['purchasable_class_id']);
            $instance = new show_instance($seat->show_instance_id);
            if ($include_past_reservations OR strtotime($instance->datetime) > time()) {
                $return[] = new reservation($cart_item['reservation_id']);
            }
        }
        return $return;
    }

    public function display()
    {
        global $site_image_empty_headshot;
        return "
	    <div class='user'>
		<img src='" . ($this->user_img_url === '' ? $site_image_empty_headshot : $this->user_img_url) . "' class='headshot' />
		<div class='meta'>
		    <span class='name'>" . htmlspecialchars($this->user_name_first . " " . $this->user_name_last) . "</span>
		    <div class='bio'>" . $this->user_bio . "</bio>
		    " . $this->get_performance_roles_string() . "
		</div>
	    </div>";
    }

    public function get_performance_roles_string()
    {
        global $global_conn, $site_domain, $site_path;
        $years = array();
        foreach (db_query($global_conn, "SELECT * FROM show_people WHERE user_id = " . $this->user_id) as $row) {
            $daterange = show::get_date_range($row['show_id']);
            $year = date("Y", $daterange[0]);
            if (!isset($years[$year])) {
                $years[$year] = "";
            }
            $show = new show($row['show_id']);
            $years[$year] .= "<li>" . $row['show_people_role'] . ", <a href='//" . $site_domain . $site_path . "show/" . $show->url_name . "'>" . $show->title . "</a> $year</li>";
        }
        return "<ul class='user-roles'>" . join(" ", $years) . "</ul>";
    }

    public function show_instance_worker_status()
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT show_instance_workers.* FROM show_instance_workers LEFT JOIN show_instance USING (show_instance_id) WHERE user_id = " . db_escape($this->user_id) . " OR datetime > NOW(); ");
        $workers = array();
        foreach ($results as $row) {
            $workers[] = new show_instance_worker($row['show_instance_worker_id']);
        }
        return $workers;
    }

    public function show_instance_worker_status_string()
    {
        global $global_conn;
        $current_volunteering = array();
        foreach (db_query($global_conn, "SELECT * FROM show_instance_workers WHERE user_id = " . db_escape($this->user_id)) as $row) {
            $current_volunteering[$row['show_instance_id']] = new show_instance_worker($row['show_instance_worker_id']);
        }
        $string = "<ul class='worker-oppurtunities'>";

        foreach (show::get_upcoming_shows(90) as $show) {
            $show_string = "";
            $show_workers_count_total = 0;
            foreach (show::get_instances($show->show_id, true) as $instance) {
                $workers = $instance->get_workers_unfilled();
                $total = count($instance->get_workers_all());
                $show_workers_count_total += $total;
                if (isset($current_volunteering[$instance->show_instance_id])) {
                    $show_string .= "<li class='worker you' show_instance_id='" . $instance->show_instance_id . "'>" . date("Y-m-d g:ia", strtotime($instance->datetime)) . " - You are signed up to volunteer! <button class='cancel'>Cancel</button></li>";
                } else if (count($workers)) {
                    $show_string .= "<li class='need-workers'>" . date("Y-m-d g:ia", strtotime($instance->datetime)) . " - " . count($workers) . " of " . $total . " available</li><ul>";
                    foreach ($workers as $worker) {
                        $show_string .= "<li class='worker " . $worker->show_instance_worker_status . "' show_instance_worker_id='" . $worker->show_instance_worker_id . "'>" . $worker->show_instance_worker_type->show_instance_worker_type_name . "</li>";
                    }
                    $show_string .= "</ul></li>";
                } else if ($total === 0) {
                    $show_string .= "<li class='no-workers'>" . date("Y-m-d g:ia", strtotime($instance->datetime)) . " - Not Yet Posted</li>";
                } else {
                    $show_string .= "<li class='no-workers'>" . date("Y-m-d g:ia", strtotime($instance->datetime)) . " - $total Filled</li>";
                }
            }
            if ($show_workers_count_total > 0) {
                $string .= "<li>" . $show->title . "</li><ul>" . $show_string . "</ul></li>";
            } else {
                $string .= "<li>" . $show->title . " - Not yet posted</li>";
            }
        }
        $string .= "</ul>";

        boffice_html::$js_internal .= "
	    $(document).ready(function() {
		$('li.worker').not('.you').button({icons:{primary:'ui-icon-triangle-1-e'}}).click(function(e){
		    var selector = new_dialog_public('Volunteering Information','loading...',400,400);
		    call_and_response_public({'command':'volunteer_information','show_instance_worker_id':$(e.currentTarget).attr('show_instance_worker_id')},response);
		    function response(f) {
			$(selector).html(f.responseText);
			post_to_ajax_public(selector + ' form', 'volunteer_signup', signup_response);
			function signup_response(h) {
			    refresh();
			}
		    }
		});
		$('li.worker.you button.cancel').button({icons:{primary:'ui-icon-trash'}}).click(function(e){
		    overlay_block('Processing Request...');
		    console.log($(e.currentTarget).closest('li.worker.you').attr('show_instance_id'));
		    call_and_response_public({'command':'volunteer_cancel','show_instance_id':$(e.currentTarget).closest('li.worker.you').attr('show_instance_id')},response);
		    function response(f) {
			console.log(f.responseText);
			if(f.responseText === '1') {
			    refresh();
			} else {
			    overlay_block_remove();
			    toast(f.responseText);
			}
		    }
		});
	    });
	    ";

        return $string;
    }
}
