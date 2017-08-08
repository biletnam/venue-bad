<?php

/**
 * Description of purchasable_registration_category
 *
 * @author lepercon
 */
class purchasable_registration_category
{
    public $purchasable_registration_category_id;
    public $purchasable_registration_category_name;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_registration_category', 'purchasable_registration_category', 'purchasable_registration_category_id');
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

    public function admin_edit_form()
    {
        global $global_conn;
        $elements = array(
            new f_data_element('Category Name', 'purchasable_registration_category_name', 'text'),
        );
        $form = new f_data($global_conn, 'purchasable_registration_category', 'purchasable_registration_category_id', $elements, $this->purchasable_registration_category_id);
        return $form->start();
    }

    public function admin_list($href)
    {
        global $global_conn;
        $string = "<ul><li><a href='$href'>New</a></li>";

        foreach (db_query($global_conn, "SELECT * FROM purchasable_registration_category ORDER BY purchasable_registration_category_name ASC") as $item) {
            $string .= "<li><a href='$href?row_id=" . $item['purchasable_registration_category_id'] . "' />" . $item['purchasable_registration_category_name'] . "</a></li>";
        }
        $string .= "</ul>";
        boffice_html::$standard_datetime_picker = true;
        return $string;
    }

    static public function list_select()
    {
        global $global_conn;
        $string = "<select>";
        foreach (db_query($global_conn, "SELECT * FROM purchasable_registration_category ORDER BY purchasable_registration_category_name ASC") as $item) {
            $string .= "<option value='" . $item['purchasable_registration_category_id'] . "'>" . $item['purchasable_registration_category_name'] . "</option>";
        }
        $string .= "</select>";
        return $string;
    }
}
