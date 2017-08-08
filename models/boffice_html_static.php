<?php

/**
 * Description of boffice_html_static
 *
 * @author lepercon
 */
class boffice_html_static
{
    public $boffice_html_static_id;
    public $boffice_html_static_content;
    public $boffice_html_static_name;
    public $boffice_html_static_class;
    public $boffice_html_order;
    public $boffice_html_static_last_edited;
    public $boffice_html_static_last_edited_by;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'boffice_html_static', 'boffice_html_static', 'boffice_html_static_id');
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

    public function say()
    {
        $string = "<div";
        if (strlen($this->boffice_html_static_name)) {
            $string .= " id=\"" . classy($this->boffice_html_static_name) . "\"";
        }
        $user = user::current_user();
        if ($user->user_is_finacial_admin OR $user->user_is_office_admin OR $user->user_is_show_admin) {
            $string .= " boffice_html_static_id='" . $this->boffice_html_static_id . "'";
            if (strlen($this->boffice_html_static_class)) {
                $string .= " class=\"" . classy($this->boffice_html_static_class) . " boffice_html_static\"";
            } else {
                $string .= " class=\"boffice_html_static\"";
            }
        } else if (strlen($this->boffice_html_static_class)) {
            $string .= " class=\"" . classy($this->boffice_html_static_class) . "\"";
        }
        $string .= ">" . $this->boffice_html_static_content . "</div>";
        return $string;
    }

    public function admin_edit_form($on_complete = null)
    {
        global $global_conn;
        $user = user::current_user();
        $elements = array(
            new f_data_element('CSS Class', 'boffice_html_static_class', 'text'),
            new f_data_element('CSS id', 'boffice_html_static_name', 'text'),
            new f_data_element('Content', 'boffice_html_static_content', 'wysiwyg'),
            new f_data_element('Last Edited', 'boffice_html_static_last_edited', 'hidden', date("Y-m-d H:i:s")),
            new f_data_element("Last Edited by", 'boffice_html_static_last_edited_by', 'hidden', $user->user_id),
        );
        $form = new f_data($global_conn, 'boffice_html_static', 'boffice_html_static_id', $elements, $this->boffice_html_static_id);
        boffice_html::$standard_ckeditor = true;
        $form->allow_delete = false;
        $form->hook_postinsert = $on_complete;
        $form->hook_postupdate = $on_complete;
        return $form->start();
    }
}
