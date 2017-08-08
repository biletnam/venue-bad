<?php

/**
 * HTML blocks that require dynamic code are stored in /models/dynamic/
 * the file should be named the sub_class name with the .php extension
 * the class should implement the base dynamic class
 *
 * @author lepercon
 */
class boffice_html_dynamic
{
    public $boffice_html_dynamic_id;
    public $boffice_html_dynamic_name;
    public $boffice_html_dynamic_sub_class;
    public $boffice_html_order;
    public $boffice_html_dynamic_parameters;
    public $boffice_html_dynamic_parameters_label;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "boffice_html_dynamic", "boffice_html_dynamic", "boffice_html_dynamic_id");
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
    }

    public function say()
    {
        require_once 'dynamic/' . $this->boffice_html_dynamic_sub_class . '.php';
        $class_name = $this->boffice_html_dynamic_sub_class;
        $class = new $class_name($this->boffice_html_dynamic_parameters);
        $user = user::current_user();

        $boe = new boe("div", $class->say());
        $boe->class .= $class->parent_css_class;
        if ($user->user_has_any_elevated_privileges()) {
            $boe->attributes['boffice_html_dynamic_id'] = $this->boffice_html_dynamic_id;
            $boe->class .= " boffice_html_dynamic";
        }
        return $boe->say();
    }

    public function admin_edit_form()
    {
        global $global_conn;
        $string = "<p>These are the options for this " . $this->boffice_html_dynamic_name . ".</p><p>" . $this->boffice_html_dynamic_parameters_label . ".</p>";
        $elements = array(
            new f_data_element('Parameters', 'boffice_html_dynamic_parameters', 'text')
        );
        $form = new f_data($global_conn, 'boffice_html_dynamic', 'boffice_html_dynamic_id', $elements, $this->boffice_html_dynamic_id);
        $form->allow_delete = false;
        return $string . $form->start();
    }
}
