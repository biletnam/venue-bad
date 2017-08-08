<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of seating_chart_extras
 *
 * @author lepercon
 */
class seating_chart_extras
{
    public $seating_chart_extra_id;
    public $seating_chart_extra_name;
    public $seating_chart_extra_icon_url;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'seating_chart_extras', 'seating_chart_extras', 'seating_chart_extra_id');

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
            new f_data_element('Name', 'seating_chart_extra_name', 'text'),
            new f_data_element('Icon', 'seating_chart_extra_icon_url', 'file')
        );

        if ($this->seating_chart_extra_id !== null AND $this->seating_chart_extra_id > 0) {
            $f = new f_data($global_conn, 'seating_chart_extras', 'seating_chart_extra_id', $elements, $this->seating_chart_extra_id);
        } else {
            $f = new f_data($global_conn, 'seating_chart_extras', 'seating_chart_extra_id', $elements, false);
        }
        return $f->start();
    }

    public function admin_list($href = '')
    {
        global $global_conn;
        $string = "<ul class='admin-list'><li><a href='$href'>New seating chart extra</a></li>";
        foreach (db_query($global_conn, "SELECT * FROM seating_chart_extras ORDER BY seating_chart_extra_name ASC") as $item) {
            $string .= "<li><a href='" . $href . "?row_id=" . $item['seating_chart_extra_id'] . "'>" . $item['seating_chart_extra_name'] . "</a></li>";
        }
        return $string . "</ul>";
    }
}
