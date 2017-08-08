<?php


function is_empty($var)
{
    return empty($var);
}

function get_var($var, $method = 'POST', $should_be_an_array = 0)
{
    switch (strtoupper($method)) { // Set the superglobal array that should be used
        case 'POST':
            $array = $_POST;
            break;
        case 'REQUEST':
            $array = $_REQUEST;
            break;
        case 'GET':
            $array = $_GET;
            break;
        case 'COOKIE':
            $array = $_COOKIE;
            break;
        case 'SESSION':
            if (isset($_SESSION)) {
                $array = $_SESSION[$GLOBALS['session_name']];
            }
            break;
        case 'SERVER':
            $array = $_SERVER;
            break;
        default:
            $array = $GLOBALS[$method];
    }
    if (isset($array[$var])) { // If that variable exists in that superglobal return it, else return the not found response
        $return = $array[$var];
    } else {
        $return = NULL;
    }
    if ($should_be_an_array) {
        if (is_array($return)) {
            return $return;
        } else {
            return Array($return);
        }
    } else {
        return $return;
    }

}

/**
 * This function takes a multi line string and condenses it to one line to prepare it for Javascript
 * @author Craig Spurrier
 * @version 0.5 Nov 8 2011 16:16EDT
 * @param string $input the string to format
 * @return string Returns a single line
 **/
function javascript_multi_line($input)
{
    return str_replace(Array("\n", '&quot;', "\r"), Array(" ", '"', " "), $input);
}

/**
 * This function takes a file name and returns the extension
 * @author Craig Spurrier
 * @version 0.4 Sep 24 2011 16:16EDT
 * @param string $file_name the file name
 * @return string Returns the file extension
 **/
function file_extension($file_name)
{
    $path_info = pathinfo($file_name);
    if (isset($path_info['extension'])) {
        return strtolower($path_info['extension']);
    } else {
        return '';
    }
}


/**
 * This function takes a file extension and provides an image
 * @author Craig Spurrier
 * @param string $ext the file extension
 * @return string An HTML img tag
 **/
function file_icon($ext)
{
    if ($ext == 'pdf') {
        $icon = 'pdf.png';
    } elseif ($ext == 'txt') {
        $icon = 'txt.png';
    } elseif ($ext == 'doc' OR $ext == 'docx' OR $ext == 'rtf') {
        $icon = 'doc.png';
    } elseif ($ext == 'html' OR $ext == 'htm') {
        $icon = 'html.png';
    }
    echo "<img src='icons/mime_types/$icon' alt='$ext' width='48' height='48' />";
}

/**
 * This function sends an e-mail with swift_mailer
 * @author Craig Spurrier
 **/
function send_email($to, $from, $subject = '', $text = '', $html = '')
{
    global $swift_mailer_path;
    if (function_exists("boffice_property") AND boffice_property('user_email_live') === '0') {
        return 1;
    }

    require_once($swift_mailer_path); // Mailer library 
    if (is_array($to)) {
        foreach ($to AS $key => $value) {
            if (is_empty($value)) {
                unset($to[$key]);
            }
        }
    } else {
        if (!is_empty($to)) {
            $to = Array($to);
        }
    }

    if (count($to) > 0) {
        $transport = Swift_SendmailTransport::newInstance();

        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($text);
        if (is_empty($html)) {
            $html = $text;
        }
        $message->addPart(
            "<?xml version='1.0' encoding='utf-8'?> 
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'> 
            <html xmlns='http://www.w3.org/1999/xhtml'> 
            <head> 
            <title>$subject</title> 
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' /> 
            </head> 
            <body>" . $html . "</body> 
            </html>",
            'text/html');

        $result = $mailer->send($message);
        return $result;
    }
}

/**
 * Sends a message with swiftmailer through google mail
 * @param type $to
 * @param type $subject
 * @param type $message
 */
function send_gmail($to, $subject, $message)
{
    global $google_username, $google_password, $swift_mailer_path, $email_signature;
    require_once($swift_mailer_path); // Mailer library 

    $transporter = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
        ->setUsername($google_username)
        ->setPassword($google_password);

    $mailer = Swift_Mailer::newInstance($transporter);
    $msg = Swift_Message::newInstance();
    $msg->setSubject($subject);
    $msg->setFrom($google_username . "@gmail.com");
    $msg->setTo($to);
    $msg->setBody($message . "<p>$email_signature</p>", 'text/html');

    $mailer->send($msg);
}

/**
 * This function fetches a URL using CURL
 * @author Craig Spurrier
 **/
function fetch_url($url, $cookies = 0)
{
    $ch = curl_init($url);
    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, cookie_path());
        curl_setopt($ch, CURLOPT_COOKIEJAR, cookie_path());

    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    return ($res);
}

/**
 * This function builds a sensible cookiepath and keeps track of it
 * @author Craig Spurrier
 **/
function cookie_path()
{
    if (!is_empty(get_var('cookie_path', 'session'))) {
        return get_var('cookie_path', 'session');
    } else {
        $ckfile = tempnam("/tmp", "CURLCOOKIE");
        $_SESSION[$GLOBALS['session_name']]['cookie_path'] = $ckfile;
        return $ckfile;
    }
}

/**
 * This function posts to a URL using CURL
 * @author Craig Spurrier
 **/
function post_to_url($url, $fields, $cookies = 0)
{
    $fields_string = '';
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    $ch = curl_init($url);
    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, cookie_path());
        curl_setopt($ch, CURLOPT_COOKIEJAR, cookie_path());
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return ($res);
}

/**
 * xml_to_array() will convert the given XML text to an array in the XML structure.
 * Link: http://www.bin-co.com/php/scripts/xml2array/
 * Arguments : $contents - The XML text
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
 */
function xml_to_array($contents, $get_attributes = 1, $priority = 'tag')
{
    if (!$contents) return array();

    if (!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";  
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work  
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss  
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if (!$xml_values) return;//Hmm...

    //Initializations  
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference  

    //Go through the tags.  
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array  
    foreach ($xml_values as $data) {
        unset($attributes, $value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope  
        // tag(string), type(string), level(int), attributes(array).  
        extract($data);//We could use the array by itself, but this cooler.  

        $result = array();
        $attributes_data = array();

        if (isset($value)) {
            if ($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode  
        }

        //Set the attributes too.  
        if (isset($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'  
            }
        }

        //See tag status and do the needed.  
        if ($type == "open") {//The starting of the tag '<tag>'
            $parent[$level - 1] = &$current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name  

                if (isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together  
                    $current[$tag] = array($current[$tag], $result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag . '_' . $level] = 2;

                    if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset($current[$tag . '_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.  
            if (!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)  
                if (isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.  
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;

                } else { //If it is not an array...  
                    $current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well

                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }

                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }

        } elseif ($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level - 1];
        }
    }

    return ($xml_array);
}

/**
 * This function will provide a sub-string up to a desired length without breaking up words
 * "..." is added if result do not reach original string length
 * From http://www.php.net/manual/en/function.substr.php#93963
 **/

function substr_words($str, $length, $minword = 3)
{
    $sub = '';
    $len = 0;

    foreach (explode(' ', $str) as $word) {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);

        if (strlen($word) > $minword && strlen($sub) >= $length) {
            break;
        }
    }

    return $sub . (($len < strlen($str)) ? ' ...' : '');
}

/**
 * This function does a recursive in_array search
 **/
function in_array_recursive($needle, $haystack)
{
    foreach ($haystack as $v => $e) {
        if ($needle == $v) {
            return true;
        } elseif (is_array($e)) {
            return in_array_recursive($needle, $e);
        }
    }
    return false;
}

/**
 * This function sorts an array by columns
 * From http://www.php.net/manual/en/function.array-multisort.php#105115
 * @ Copyright Ichier2003
 **/
function multi_sort()
{
    $i = 0;
    $args = func_get_args();
    $marray = array_shift($args);
    $msortline = 'return(array_multisort(';
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            foreach ($marray as $row) {
                $sortarr[$i][] = $row[$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        $msortline .= '$sortarr[' . $i . '],';
    }
    $msortline .= '$marray));';
    eval($msortline);
    return $marray;

}

/**
 * This function converts smart qoutes
 * @author Craig Spurrier
 **/
function convert_smart_quotes($string)
{
    return str_replace(
        array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6", chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
        array("'", "'", '"', '"', '-', '--', '...', "'", "'", '"', '"', '-', '--', '...'),
        $string);
}

/**
 * This function gets url data between a start and end string and checks for certain contents. We are assuming that start and end strings are unique in the target file
 * @author Jason Steelman
 * @param string $url The url to fetch
 * @param string $start the unique html string to search for to start the region
 * @param string $end the unique html string to search for to end region region
 * @param array $checks an array of unique html strings that we'll look for to make sure we got the right region
 * @return string|false
 */
function screen_scrape($url, $start, $end, $checks, $operate_on_html_special_chars = true, $inclusive = true)
{
    $raw = file_get_contents($url);
    $newlines = array("\t", "\n", "\r", "\x20\x20", "\0", "\x0B");
    if ($operate_on_html_special_chars) {
        $test = str_replace($newlines, "", html_entity_decode($raw));
        $start_pos = strpos($test, $start);
        $end_pos = strpos($test, $end, $start_pos);
        $content = substr($test, $start_pos, $end_pos - $start_pos);
    } else {
        $test = str_replace($newlines, "", htmlentities($raw));
        $start_pos = strpos($test, $start);
        $end_pos = strpos($test, $end, $start_pos);
        $content = html_entity_decode(substr($test, $start_pos, $end_pos - $start_pos));
    }

    foreach ($checks as $string) {
        if (!strpos($content, $string)) return false;
    }
    if (!$inclusive) {
        if ($operate_on_html_special_chars) {
            $strlen = strlen($start);
        } else {
            $strlen = strlen(html_entity_decode($start));
        }
        $content = substr($content, $strlen);
    }

    return $content;
}

/**
 * This function rewites relative URLS to absolute urls
 * @author Jason Steelman
 * @param string $str the string to operate on
 * @param string $domain the domain we're going to make the urls absolute to (e.g. "sc.edu")
 * @return string
 */
function rerwite_relative_urls_in_str($str, $domain)
{
    $i = 0;
    while ($i < strlen($str)) {
        $href_start = strpos($str, "href", $i);
        if ($href_start) {
            $href_start += 6;
            $href_end = strpos($str, "\"", $href_start);
            $href = substr($str, $href_start, $href_end - $href_start);
            if (substr($href, 0, 4) != "http" && substr($href, 0, 4) != "feed") {
                if (substr($href, 0, 1) != "/") $href = "/" . $href;
                $str = substr($str, 0, $href_start - 6) . "href=\"http://" . $domain . $href . substr($str, $href_end);
            }
            flush();
            $i = $href_start + 1;
        } else {
            break;
        }
    }
    $i = 0;
    while ($i < strlen($str)) {
        $href_start = strpos($str, "src", $i);
        if ($href_start) {
            $href_start += 6;
            $href_end = strpos($str, "\"", $href_start);
            $href = substr($str, $href_start, $href_end - $href_start);
            if (substr($href, 0, 4) != "http" && substr($href, 0, 4) != "feed") {
                if (substr($href, 0, 1) != "/") $href = "/" . $href;
                $str = substr($str, 0, $href_start - 6) . "src=\"http://" . $domain . $href . substr($str, $href_end);
            }
            flush();
            $i = $href_start + 1;
        } else {
            break;
        }
    }
    return $str;
}

/**
 * Random String Generator
 * http://stackoverflow.com/questions/4356289/php-random-string-generator
 * @author Stephen Watkins http://stackoverflow.com/users/151382/stephen-watkins
 * @var int $length The length of desired string.
 * @return string A randomly generated string
 */
function random_string($length = 10, $allow_non_alpha_numeric_characters = false, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    if ($allow_non_alpha_numeric_characters) {
        $characters .= "!@#$&*()[]<>/?.,`~";
    }
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * A super strict string sterilizing procudure useful pretty much ONLY for
 * making a string consistantly pure for html ids and classes
 * @author Jason Steelman <uscart@gmail.com>
 * @param string $string The string to be steralized
 * @return string The steralized string
 */
function classy($string)
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

/**
 * Creates very short, very inconsistant file type names from a handful of
 * mimetypes
 * @param string $file_type The mime-type to be looked up
 * @return string The resulting human-readible filetype if one was found.
 */
function pretty_filetypes($file_type)
{
    $file_types = array(
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => 'Word',
        "application/vnd.ms-excel" => 'Excel 2010',
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "Excel 2007",
        "application/vnd.ms-powerpoint" => "Powerpoint",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "Powerpoint 2007",
        "application/pdf" => "PDF",
        "application/msexcel" => "Excel",
        "application/mspowerpoint" => "Powerpoint",
        "application/msword" => "Word",
    );
    if (isset($file_types[strtolower($file_type)])) {
        return $file_types[strtolower($file_type)];
    } else if (strpos($file_type, 'wordprocessing')) {
        return "Word 2010+";
    } else {
        $parts = explode('/', $file_type);
        if ($parts[0] == 'image') {
            return $parts[1];
        }
        return $file_type;
    }
}

function boffice_file($file_id, $size = false)
{
    global $global_conn;
    $conn = $global_conn;
    if ($size AND is_numeric($size)) {
        $cached_results = db_query($conn, "SELECT page_files_cache.*, page_files.page_file_name, page_files.page_file_type FROM page_files_cache LEFT JOIN page_files USING (page_file_id) WHERE page_files_cache.page_file_id = " . db_escape($file_id, $conn) . " AND page_file_cache_height = " . db_escape($size));
        if ($cached_results) {
            header('Content-type: ' . $cached_results[0]['page_file_type']);
            header('Content-Disposition: inline; filename="' . addslashes($cached_results[0]['page_file_name']) . '"');
            echo $cached_results[0]['page_file_cache_data'];
        } else {
            $results = db_query($conn, "SELECT * FROM page_files WHERE page_file_id = " . db_escape($file_id, $conn));
            if (!$results) {
                header("HTTP/1.0 404 Not Found");
                exit();
            } else {
                if (strpos($results[0]['page_file_type'], 'image') !== false) {
                    header('Content-type: ' . $results[0]['page_file_type']);
                    header('Content-Disposition: inline; filename="' . addslashes($results[0]['page_file_name']) . '"');
                    $resize = boffice_resize_image($results[0]['page_file_contents'], $size);
                    echo $resize;
                    db_exec($conn, build_insert_query($conn, 'page_files_cache', array(
                        'page_file_id' => $results[0]['page_file_id'],
                        'page_file_cache_height' => $size,
                        'page_file_cache_datetime' => date("Y-m-d H:i:s"),
                        'page_file_cache_data' => $resize
                    )));
                }
            }
        }
    } else {
        $results = db_query($conn, "SELECT * FROM page_files WHERE page_file_id = " . db_escape($file_id, $conn));
        if (!$results) {
            header("HTTP/1.0 404 Not Found");
            die();
        }
        header('Content-type: ' . $results[0]['page_file_type']);
        header('Content-Disposition: inline; filename="' . addslashes($results[0]['page_file_name']) . '"');
        if ($results[0]['page_file_size'] > 0) {
            header("Content-Length: " . $results[0]['page_file_size']);
        }
        echo $results[0]['page_file_contents'];
    }

}

function boffice_resize_image($image, $new_height)
{
    if (extension_loaded('imagick')) {
        $im = new Imagick();
        $im->readimageblob($image);
        $im->scaleImage(0, $new_height);
        $str = $im->getimageblob();

        $im->destroy();
        return $str;
    } else {
        return $image;
    }
}

function display_date_range($time_start, $time_end)
{
    $string = "";
    if (date('i', $time_start) == "00") {
        $time1 = date('g', $time_start);
    } else {
        $time1 = date("g:i", $time_start);
    }
    if (date('i', $time_end) == "00") {
        $time2 = date('g', $time_end);
    } else {
        $time2 = date("g:i", $time_end);
    }

    $meridian1 = date('a', $time_start);
    $meridian2 = date('a', $time_end);
    $day1 = date("D, M jS", $time_start);
    $day2 = date("D, M jS", $time_end);
    $month1 = date("M/Y", $time_start);
    $month2 = date("M/Y", $time_end);
    $year1 = date("Y", $time_start);
    $year2 = date("Y", $time_end);


    if ($time_start === $time_end) {
        $string = $day1 . ", " . $time1 . $meridian1;
    } else if ($day1 === $day2 AND $meridian1 === $meridian2 AND $year1 === $year2) {
        $string = $day1 . ", " . date('g:i') . " - " . $time2 . $meridian1;
    } else if ($day1 === $day2 AND $year1 === $year2) {
        $string = $day1 . ", " . $time1 . $meridian1 . " - " . $time2 . $meridian2;
    } else if ($month1 === $month2 AND $year1 === $year2) {
        $string = date("M jS", $time_start) . " - " . date("jS", $time_end);
    } else if ($year1 === $year2 AND $year1 === date("Y")) {
        $string = date("M js", $time_start) . " - " . date("M js", $time_end);
    } else {
        $string = date("M js, Y", $time_start) . " - " . date("M js, Y");
    }
    return $string;
}