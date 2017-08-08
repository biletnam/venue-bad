<?php


function boffice_authenticate($username, $password, $set_auto_login = false)
{
    $ulogin = new uLogin();
    $uid = $ulogin->Uid($username);
    $ulogin->Authenticate($username, $password);
    if ($ulogin->AuthResult !== false AND $ulogin !== NULL) {
        boffice_log_in($username);
        if ($set_auto_login) {
            $ulogin->SetAutologin($username, true);
        }
        return true;
    } else {
        if ($uid > 0 AND $ulogin->IsUserBlocked($uid) AND $ulogin->IsUserBlocked($uid) > date_create('now')) {
            new boffice_error("Your account has been blocked due to repeated log in attempts.", false);
        } else if (ulIpBlocker::IpBlocked(ulUtils::GetRemoteIP()) AND ulIpBlocker::IpBlocked(ulUtils::GetRemoteIP()) > date_create('now')) {
            new boffice_error("Your account has been blocked due to repeated log in attempts.", false);
        } else {
            new boffice_error("Your email or password is incorrect", true, 'password');
            return false;
        }
    }
}

/**
 * Set the session variables for a logged in user.
 * Does NOT test for a valid password. That must be done elsewhere depending on the login mechanism.
 * @param string $username
 * @param int $uid
 */
function boffice_log_in($username, $uid = false)
{
    if (!boffice_logged_in()) {
        $_SESSION['boffice']['username'] = $username;
        $_SESSION['boffice']['logged_in'] = true;
        if ($uid) {
            $_SESSION['boffice']['uid'] = $uid;
        } else {
            $ulogin = new uLogin();
            $uid = $ulogin->Uid($username);
            $_SESSION['boffice']['uid'] = $uid;
        }
        $user = new user(user::get_bid_from_ulogin_id($uid));
        $_SESSION['boffice']['user'] = $user;
    }
}

/**
 * Is a user logged in?
 * @return boolean
 */
function boffice_logged_in()
{
    return isset($_SESSION['boffice']['uid'], $_SESSION['boffice']['username'], $_SESSION['boffice']['logged_in']) AND $_SESSION['boffice']['logged_in'] === true;
}

/**
 * Logs current user out of boffice
 */
function boffice_log_out()
{
    unset($_SESSION['boffice']['uid'], $_SESSION['boffice']['username'], $_SESSION['boffice']['logged_in']);
    unset($_SESSION['boffice']['user']);
}

function money($float)
{
    $string = (string)$float;
    if (strpos($string, ".")) {
        $parts = explode('.', $string);
        $fractional_part = $parts[1];
        if (strlen($fractional_part) === 2) {
            //
        } else if (strlen($fractional_part) === 1) {
            $string .= "0";
        } else {
            $string = $parts[0] . "." . substr($fractional_part, 0, 2);
        }
    } else {
        $string = $string . ".00";
    }
    return $string;
}


/* Luhn algorithm number checker - (c) 2005-2008 shaman - www.planzero.org *
 * This code has been released into the public domain, however please      *
 * give credit to the original author where possible.                      */

function luhn_check($number)
{

    // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
    $number = preg_replace('/\D/', '', $number);

    // Set the string length and parity
    $number_length = strlen($number);
    $parity = $number_length % 2;

    if ($number_length === 0) {
        return false;
    }

    // Loop through each digit and do the maths
    $total = 0;
    for ($i = 0; $i < $number_length; $i++) {
        $digit = $number[$i];
        // Multiply alternate digits by two
        if ($i % 2 == $parity) {
            $digit *= 2;
            // If the sum is two digits, add them together (in effect)
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        // Total up the digits
        $total += $digit;
    }

    // If the total mod 10 equals 0, the number is valid
    return ($total % 10 == 0) ? TRUE : FALSE;

}


/**
 * A super strict string sterilizing procudure useful pretty much ONLY for
 * making a string consistantly pure for html ids and classes
 * @author Jason Steelman <uscart@gmail.com>
 * @param string $string The string to be steralized
 * @return string The steralized string
 */
function boffice_classy($string)
{
    $valid_chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $new_string = "";
    for ($i = 0; $i < strlen($string); $i++) {
        if (in_array(strtolower(substr($string, $i, 1)), $valid_chars)) {
            $new_string .= substr($string, $i, 1);
        }
    }
    return $new_string;
}

function prepend_log($string, $type = 'query')
{
    $file = "C:\wamp\bofficelog.txt";
    $contents = file_get_contents($file, false, null, -1, 20000);
    file_put_contents($file, $contents . "\n" . time() . " | " . $string);
}

/**
 * Get the value of a global property stored in database
 * @global PDO $global_conn
 * @param string $property_name
 * @return string
 */
function boffice_property($property_name)
{
    global $global_conn;
    $results = db_query($global_conn, "SELECT * FROM boffice_properties WHERE boffice_property_name = " . db_escape($property_name));
    return $results[0]['boffice_property_value'];
}

/**
 * Sums the columns of a multideminsional array
 * @param array $array like array(array('0'=>'33','1'=>12), array('0'=>'8','2'=>'99'))
 * @return array
 */
function boffice_array_sub_sum($array)
{
    $return = array();
    foreach ($array as $sub_array) {
        foreach (array_keys($sub_array) as $key) {
            $return[$key] = array_sum(array_column($array, $key));
        }
    }
    return $return;
}

function boffice_array_fill($start_index, $count, $fill_with)
{
    if (!is_numeric($count)) {
        die("boffice_array_fill encountered a non number as count.");
    }
    if (!is_numeric($start_index)) {
        die("boffice_array_fill encountered a non number as start_index.");
    }
    if ($count < 0) {
        die("boffice_array_fill encountered a non positive number as count.");
    }
    $array = array();
    for ($i = 0; $i < $count; $i++) {
        $array[$start_index + $i] = $fill_with;
    }
    return $array;
}