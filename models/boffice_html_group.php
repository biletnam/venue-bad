<?php


/**
 * Description of boffice_html_group
 *
 * @author lepercon
 */
class boffice_html_group
{
    public $boffice_html_group_id;
    public $boffice_html_group_functions_as;
    public $boffice_html_page_id;
    public $boffice_html_group_order;

    public $boffice_html_group_assignments;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'boffice_html_group', 'boffice_html_group', 'boffice_html_group_id');
        $this->db_interface->class_property_exclusions = array('boffice_html_group_assignments');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        global $global_conn;
        $this->db_interface->get($id);
        $results = db_query($global_conn, "SELECT * FROM boffice_html_group_assignments WHERE boffice_html_group_id = " . db_escape($this->boffice_html_group_id));
        $this->boffice_html_group_assignments = array();
        foreach ($results as $row) {
            if (intval($row['boffice_html_static_id']) > 0) {
                $this->boffice_html_group_assignments[] = new boffice_html_static($row['boffice_html_static_id']);
            }
            if (intval($row['boffice_html_dynamic_id']) > 0) {
                $this->boffice_html_group_assignments[] = new boffice_html_dynamic($row['boffice_html_dynamic_id']);
            }
        }
        $this->order_assignments();
    }

    public function order_assignments()
    {
        $new_order = array();

        foreach ($this->boffice_html_group_assignments as $assignment) {
            $debug_statement = $assignment->boffice_html_order;
            while (isset($new_order[$assignment->boffice_html_order])) {
                $assignment->boffice_html_order++;
                $debug_statement .= " +1 ";
            }
            $debug_statement .= " final " . $assignment->boffice_html_order;
            $new_order[$assignment->boffice_html_order] = $assignment;
        }
        ksort($new_order);
        $this->boffice_html_group_assignments = $new_order;
    }

    public function say()
    {
        $user = user::current_user();
        $boe = new boe("div", "");
        $boe->class = $this->boffice_html_group_functions_as;
        if ($user->user_is_finacial_admin OR $user->user_is_office_admin OR $user->user_is_show_admin) {
            $boe->class .= " boffice_html_group";
            $boe->id = $this->boffice_html_group_id;
        }

        foreach ($this->boffice_html_group_assignments as $element) {
            $boe->content .= $element->say();
        }
        return $boe->say();
    }
}
