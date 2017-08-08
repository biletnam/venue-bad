<?php


/**
 * Description of boffice_html
 *
 * @author lepercon
 */
class boffice_html
{
    public static $standard_jquery = true;
    public static $standard_jquery_ui = true;
    public static $standard_ckeditor = false;
    public static $standard_datetime_picker = false;

    public static $uses_seating_chart = false;
    public static $uses_cms_editability = false;

    /**
     * @var string String value of the javascript to be executed not including the <script></script> tag. Always use $var .= "..." to prevent overrides
     */
    public static $js_internal = "";

    public static function get_js_internal()
    {
        $string = "";
        if (strlen(boffice_html::$js_internal) > 0) {
            $string .= "\t<script type='text/javascript'>\n\t\t" . boffice_html::$js_internal . "\n\t</script>\n";
        }
        if (boffice_html::$standard_ckeditor) {
            $string .= "\t<script type='text/javascript'>$(document).ready(function () { $('textarea.wysiwyg').ckeditor(); });</script>";
        }
        return $string;
    }

    /**
     * @var array An array of source urls for javascript inclusion
     */
    public static $js_external_src = array();

    public static function get_js_external()
    {
        global $site_domain, $site_path;
        $string = "";
        if (boffice_html::$standard_jquery) {
            $string .= "\t" . '<script src="' . $site_path . '_resources/external/jquery/jquery-1.11.1.min.js"></script>' . "\n";
        }
        if (boffice_html::$standard_jquery_ui) {
            $string .= "\t" . '<script src="' . $site_path . '_resources/external/jquery-ui/js/jquery-ui-1.10.4.custom.min.js"></script>' . "\n";
        }
        if (boffice_html::$uses_cms_editability) {
            $string .= "\t" . '<script src="' . $site_path . '_resources/boffice_cms_controller.js"></script>' . "\n";
            $string .= "\t" . '<script src="' . $site_path . '_resources/boffice_cms_editability.js"></script>' . "\n";
            boffice_html::$standard_ckeditor = true;
            boffice_html::$js_internal .= " var path_to_current_api = \"//" . $site_domain . $site_path . "_resources/boffice_cms_api.php\"; ";
        }
        if (boffice_html::$standard_ckeditor) {
            $string .= "\t" . '<script src="' . $site_path . '_resources/external/ckeditor/ckeditor.js"></script><script src="' . $site_path . '_resources/external/ckeditor/adapters/jquery.js"></script>' . "\n";
        }
        if (boffice_html::$standard_datetime_picker) {
            $string .= "\t" . '<script src="' . $site_path . '_resources/external/datetimepicker-master/jquery.datetimepicker.js"></script>' . "\n";
        }
        $string .= "\t" . '<script src="' . $site_path . '_resources/boffice_master.js"></script>' . "\n";
        if (boffice_html::$uses_seating_chart) {
            $string .= "\t" . '<script src="' . $site_path . '_resources/seating_chart.js"></script>' . "\n";
        }

        foreach (boffice_html::$js_external_src as $src) {
            $string .= "\t" . '<script src="' . $src . '"></script>' . "\n";
        }
        return $string;
    }

    /**
     * @var string A string value of CSS definitions excluding the <style></style> tag. Always use $var .= "..." to prevent overrides
     */
    public static $css_internal = "";

    public static function get_css_internal()
    {
        if (strlen(boffice_html::$css_internal) > 0) {
            return "\t<style>\n\t\t" . boffice_html::$css_internal . "\n\t</style>\n";
        } else {
            return "";
        }
    }

    /**
     * @var array An array of source urls for css inclusion
     */
    public static $css_external_src = array();

    public static function get_css_external()
    {
        global $site_path;
        $string = "";
        if (boffice_html::$standard_jquery_ui) {
            $string .= "\t<link rel='stylesheet' href='" . $site_path . "_resources/external/jquery-ui/css/overcast/jquery-ui-1.10.4.custom.min.css' >\n";
        }
        if (boffice_html::$standard_datetime_picker) {
            $string .= "\t<link rel='stylesheet' href='" . $site_path . "_resources/external/datetimepicker-master/jquery.datetimepicker.css' >\n";
        }
        if (boffice_html::$uses_seating_chart) {
            $string .= "\t<link rel='stylesheet' href='" . $site_path . "_resources/seating_chart.css' >\n";
        }
        $string .= "<link rel='stylesheet' href='" . $site_path . "_resources/boffice_master.css' >";

        foreach (boffice_html::$css_external_src as $src) {
            $string .= "\t<link rel='stylesheet' href='$src' >\n";
        }

        return $string;
    }

    /**
     * @var string miscellanous head inclusions. Always use $var .= "..." to prevent overrides
     */
    public static $extra_head = "";

    /**
     * @var string HTML elements to include after the header and before body and body prepend. Always use $var .= "..." to prevent overrides
     */
    public static $html_header = "";

    public static function get_header()
    {
        global $site_name;
        if (boffice_html::$html_header === "") {
            return "<h1>" . $site_name . "</h1>";
        } else {
            return boffice_html::$html_header;
        }
    }

    public static $html_title = "";

    public static function get_title()
    {
        global $site_name;
        if (boffice_html::$html_title !== "") {
            return htmlspecialchars(boffice_html::$html_title);
        }
        if (boffice_html::$html_title === "" AND boffice_html::$html_header === "") {
            return htmlspecialchars($site_name);
        }
        return htmlspecialchars(boffice_html::$html_header);
    }


    public static $html_header_append = "";

    /**
     * @var string HTML elements to include after the header and before body and body prepend. Always use $var .= "..." to prevent overrides
     */
    public static $html_body_prepend = "";

    /**
     * @var array A list of html_body_regions.
     */
    public static $html_body_regions = array();

    public static function get_html_body_regions()
    {
        $string = "";
        foreach (boffice_html::$html_body_regions as $region) {
            $string .= $region->say();
        }
        return $string;
    }

    /**
     * @var string Always use $var .= "..." to prevent overrides
     */
    public static $html_body = "";

    /**
     * @var string Always use $var .= "..." to prevent overrides
     */
    public static $html_body_append = "";

    /**
     * @var string Always use $var .= "..." to prevent overrides
     */
    public static $html_footer_prepend = "";

    /**
     * @var string Always use $var .= "..." to prevent overrides
     */
    public static $html_footer_append = "";


    public static $html_nav_class_id = 1;

    public static function get_navigation()
    {
        global $global_conn;

        function child_elements(&$elements, $target_id)
        {
            global $site_domain, $site_path;
            $list_shows_in_nav = boffice_property("list_shows_in_nav");
            $list_shows_in_nav_count = boffice_property("list_shows_in_nav_count");
            $list_shows_in_nav_under_element_named = boffice_property("list_shows_in_nav_under_element_named");

            $return = "<ul>";
            foreach ($elements as $item) {
                if ($item['boffice_nav_parent_id'] === $target_id) {
                    $page = new boffice_html_page($item['boffice_html_page_id']);
                    $return .= "<li><a href=\"//" . $site_domain . $site_path . "page/" . $page->boffice_html_page_url . "\">" . $item['boffice_nav_display_name'] . "</a>";
                    if ($list_shows_in_nav === "1" AND $list_shows_in_nav_under_element_named === $item['boffice_nav_display_name']) {
                        $shows = show::get_upcoming_shows($list_shows_in_nav_count);
                        $return .= "<ul>";
                        foreach ($shows as $show) {
                            $return .= "<li><a href=\"//" . $site_domain . $site_path . "show/" . $show->url_name . "\">" . $show->title . "</a></li>";
                        }
                        $return .= "</ul>";
                    }
                    $return .= child_elements($elements, $item['boffice_nav_id']);
                    $return .= "</li>";
                    array_splice($elements, array_search($item, $elements), 1);
                }
            }
            if ($return === "<ul>") {
                return "";
            } else {
                return $return . "</ul>";
            }
        }

        $elements = db_query($global_conn, "SELECT * FROM boffice_nav WHERE boffice_nav_class_id = " . db_escape(boffice_html::$html_nav_class_id) . " ORDER BY boffice_nav_order ASC ");
        return child_elements($elements, "0");
    }

    public static function get_user_navigation()
    {
        global $site_domain, $site_path;
        $string = "";
        $user = user::current_user();
        if (!boffice_logged_in()) {
            $string .= "<a class='login' href='//" . $site_domain . $site_path . "login.php'>Login</a>";
        } else {
            $string .= "<a href='//" . $site_domain . $site_path . "myaccount.php'><button class='myaccount'>" . $user->user_name_first . "'s Account</button></a>";
            if ($user->user_has_any_elevated_privileges()) {
                $string .= "<a href='//" . $site_domain . $site_path . "office/' target='_blank'><button class='boffice'>Admin</button></a> | 
		    <span id='click-message'>Ctrl+Click <span class='status'></span></span>";
            }
            $string .= "<a href='//" . $site_domain . $site_path . "logout.php'><button class='logout'>Logout</button></a>";
        }
        return $string;
    }

}
