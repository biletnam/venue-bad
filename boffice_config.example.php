<?php


/**
 * Database Connection Information
 * ===============================
 * Below are two database connection areas. One is for the boffice system, the
 * other is for the ulogin system. These should use two DIFFERENT accounts
 * to minimize security risks.
 */
$boffice_database_connection_type = "mysql";
$boffice_database_connection_host_address = "localhost";
$boffice_database_name = "boffice";
$boffice_database_connection_username = "root";
$boffice_database_connection_password = "";

$ulogin_database_connection_type = "mysql";
$ulogin_database_connection_host_address = "localhost";
$ulogin_database_name = "ulogin";
$ulogin_database_connection_username = "";
$ulogin_database_connection_password = "";


/**
 * Site Definitions
 * ================
 */
$site_name = "My Theater";
$site_domain = "localhost";
$site_path = "/boffice/";    //default is "/" meaning, the default directory for this system is the root directory - domain.com/
$site_files_path = "file/";        //default is "file/", meaning domain.com/[path/]file/
$site_physical_path = "C:\Wamp\www\boffice\\";
$site_login_url = "login.php";    //default is "login.php", meaning domain.com/path/login.php
$site_logout_url = "logout.php";
$site_account_url = "myaccount.php";
$site_count_seats_right_to_left = true;
$site_image_empty_headshot = "//" . $site_domain . $site_path . "_resources/images/headshot_generic_female.jpg";
setlocale(LC_MONETARY, 'en_US');
$swift_mailer_path = "";

/**
 * Merchant Definitions
 */
$merchant_class = "merchant_authorize_dot_net";
require_once 'shared_functions/anet_php_sdk/AuthorizeNet.php'; //require dependencies
$merchant_authorize_dot_net_id = '';
$merchant_authorize_dot_net_key = '';
$merchant_authorize_dot_net_sandbox = true;


/**
 * Manditory Includes
 * ========
 * Probably shouldn't be edited by non-developers.
 * CSS/JS are injected elsewhere.
 */
require_once 'shared_functions/functions.core.php';
require_once 'shared_functions/functions.db.php';
require_once 'shared_functions/functions.form.php';
require_once 'models/boffice_error.php';
require_once 'models/boffice_html.php';
require_once 'models/boffice_html_dynamic.php';
require_once 'models/boffice_html_dynamic_base.php';
require_once 'models/boffice_html_element.php';
require_once 'models/boffice_html_group.php';
require_once 'models/boffice_html_page.php';
require_once 'models/boffice_html_region.php';
require_once 'models/boffice_html_static.php';
require_once 'models/boffice_notice.php';
require_once 'models/boffice_standard_interface.php';
require_once 'models/cart.php';
require_once 'models/cart_item.php';
require_once 'models/package.php';
require_once 'models/package_usage.php';
require_once 'models/payment_finacial_details.php';
require_once 'models/patron_types.php';
require_once 'models/purchasable.php';
require_once 'models/purchasable_donation.php';
require_once 'models/purchasable_giftcard.php';
require_once 'models/purchasable_giftcard_instance.php';
require_once 'models/purchasable_null.php';
require_once 'models/purchasable_package_model.php';
require_once 'models/purchasable_package_model_benefit.php';
require_once 'models/purchasable_seat_abstract.php';
require_once 'models/purchasable_seat.php';
require_once 'models/purchasable_seat_instance.php';
require_once 'models/purchasable_seating_general.php';
require_once 'models/transaction.php';
require_once 'models/registration.php';
require_once 'models/reservation.php';
require_once 'models/reservation_ticket.php';
require_once 'models/seating_chart.php';
require_once 'models/seating_chart_extras.php';
require_once 'models/seating_chart_extras_instance.php';
require_once 'models/show.php';
require_once 'models/show_instance.php';
require_once 'models/show_instance_cache.php';
require_once 'models/show_instance_worker_type.php';
require_once 'models/show_instance_worker.php';
require_once 'models/show_people.php';
require_once 'models/show_seat_price_model.php';
require_once 'models/show_seat_price_model_patron_type.php';
require_once 'models/stage.php';
require_once 'models/ticket.php';
require_once 'models/transaction.php';
require_once 'models/user.php';
require_once 'models/purchasable_registration_category.php';
require_once 'models/purchasable_registration.php';
require_once 'models/purchasable_registration_instance.php';
require_once 'models/merchant_interface.php';
require_once 'models/merchant_authorize_dot_net.php';
require_once 'boffice_functions/boffice.core.php';
require_once 'boffice_functions/boffice.database.php';
require_once 'boffice_functions/boffice.initialize.php';
require_once 'boffice_functions/boffice.statistics.php';
require_once 'boffice_functions/boffice.template.php';


/**
 * uLogin. Definitions are explained in detail in ulogin/config/main.inc.php
 */
define('UL_DOMAIN', 'localhost');
define('UL_INC_DIR', 'C:\wamp\www\boffice\shared_functions\ulogin');
define('UL_SITE_ROOT_DIR', 'C:\wamp\www\boffice');
define('UL_SITE_KEY', '');
define('UL_USES_AJAX', false);
define('UL_AUTH_BACKEND', 'ulPdoLoginBackend');
define('UL_HTTPS', false);
define('UL_HSTS', 0);
define('UL_PREVENT_CLICKJACK', true);
define('UL_PREVENT_REPLAY', false);
define('UL_LOGIN_DELAY', 5);
define('UL_NONCE_EXPIRE', 900);
define('UL_AUTOLOGIN_EXPIRE', 5356800);
define('UL_MAX_USERNAME_LENGTH', 100);
define('UL_USERNAME_CHECK', '');
define('UL_MAX_PASSWORD_LENGTH', 55);
define('UL_HMAC_FUNC', 'sha256');
define('UL_PWD_FUNC', '{BCRYPT}');
define('UL_PWD_ROUNDS', 13);
define('UL_PROXY_HEADER', '');
define('UL_DEBUG', true);  //@todo Change for production 
define('UL_GENERIC_ERROR_MSG', 'An authentication error occured.');
define('UL_SESSION_AUTOSTART', true);
define('UL_SESSION_EXPIRE', 3600); //1hr
define('UL_SESSION_REGEN_PROB', 30); //set to 0 if using AJAX
define('UL_SESSION_BACKEND', 'ulPdoSessionStorage');
define('UL_SESSION_CHECK_REFERER', true);
define('UL_SESSION_CHECK_IP', true);
define('UL_LOG', true);
define('UL_MAX_LOG_AGE', 5356800);
define('UL_MAX_LOG_RECORDS', 1000000);
define('UL_BF_WINDOW', 300);
define('UL_BF_IP_ATTEMPTS', 6);
define('UL_BF_IP_LOCKOUT', 18000);
define('UL_BF_USER_ATTEMPTS', 10);
define('UL_BF_USER_LOCKOUT', 18000);
define('UL_DATETIME_FORMAT', 'c');

// uLogin db connection variables. Detailed in ulogin/pdo/pdo.inc.php 
define('UL_PDO_CON_STRING', 'mysql:host=localhost;dbname=ulogin');
define('UL_PDO_CON_INIT_QUERY', "");
define('UL_PDO_AUTH_USER', 'ulogin_auth'); //select 
define('UL_PDO_AUTH_PWD', '');
define('UL_PDO_UPDATE_USER', 'ulogin_update'); //select insert update
define('UL_PDO_UPDATE_PWD', '');
define('UL_PDO_DELETE_USER', 'ulogin_delete'); //select delete
define('UL_PDO_DELETE_PWD', '');
define('UL_PDO_SESSIONS_USER', 'ulogin_sessions'); //[sessions table] select update insert delete
define('UL_PDO_SESSIONS_PWD', '');
define('UL_PDO_LOG_USER', 'ulogin_log'); //[log table] select update insert delete
define('UL_PDO_LOG_PWD', '');
require_once('shared_functions/ulogin/config/all.inc.php');
require_once('shared_functions/ulogin/main.inc.php');


boffice_initialize();