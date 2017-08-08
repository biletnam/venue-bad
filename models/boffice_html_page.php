<?php

/**
 * Description of boffice_html_page
 *
 * @author lepercon
 */
class boffice_html_page
{
    public $boffice_html_page_id;
    public $boffice_html_page_title;
    public $boffice_html_page_url;
    public $boffice_nav_class_id;

    public $boffice_html_groups;

    private $needs_all_page_data;
    private $has_all_page_data;
    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'boffice_html_page', 'boffice_html_page', 'boffice_html_page_id');
        $this->db_interface->class_property_exclusions = array('boffice_html_groups', 'needs_all_page_data', 'has_all_page_data');
        $this->needs_all_page_data = false;
        $this->has_all_page_data = false;
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        if ($this->needs_all_page_data) {
            $this->get_page_data();
        }
    }

    public function get_page_data()
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM boffice_html_group WHERE boffice_html_page_id = " . db_escape($this->boffice_html_page_id) . " ORDER BY boffice_html_group_order ASC");
        $this->boffice_html_groups = array();
        foreach ($results as $row) {
            $this->boffice_html_groups[] = new boffice_html_group($row['boffice_html_group_id']);
        }
        $this->has_all_page_data = true;
        $this->order_groups();
    }

    public function order_groups()
    {
        $new_order = array();
        foreach ($this->boffice_html_groups as $group) {
            while (isset($new_order[$group->boffice_html_group_order])) {
                $group->boffice_html_group_order++;
            }
            $new_order[$group->boffice_html_group_order] = $group;
        }
        ksort($new_order);
        $this->boffice_html_groups = $new_order;
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function say()
    {
        if ($this->has_all_page_data === false) {
            $this->needs_all_page_data = true;
            $this->get_page_data();
        }

        $user = user::current_user();
        if ($user->user_is_finacial_admin OR $user->user_is_office_admin OR $user->user_is_show_admin) {
            $string = "<div class='boffice_html_page' page_id='" . $this->boffice_html_page_id . "' >";
            boffice_html::$uses_cms_editability = true;
            boffice_html::$standard_ckeditor = true;
        } else {
            $string = "<div>";
        }
        foreach ($this->boffice_html_groups as $group) {
            $string .= $group->say();
        }
        $string .= "</div>";
        return boffice_template_simple(htmlspecialchars($this->boffice_html_page_title), $string);
    }

    /**
     *
     * @global type $global_conn
     * @param string $url
     * @return boffice_html_page
     */
    public static function get_page_by_url($url)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM boffice_html_page WHERE LOWER(boffice_html_page_url) = " . db_escape(strtolower($url), $global_conn) . " LIMIT 1;");
        if (count($results) === 0) {
            return new boffice_html_page(0);
        } else {
            return new boffice_html_page($results[0]['boffice_html_page_id']);
        }
    }
}
