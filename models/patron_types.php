<?php

/**
 * Description of patron_types
 *
 * @author lepercon
 */

class patron_types
{
    public $patron_type_id;
    public $patron_type_label;


    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'patron_types', 'patron_types', 'patron_type_id');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        return $this->db_interface->get($id);
    }

    public function set()
    {
        return $this->db_interface->set();
    }

    public function delete()
    {
        return $this->db_interface->delete($this->patron_type_id);
    }

    static public function patron_type_id_to_label($patron_type_id)
    {
        $p = new patron_types($patron_type_id);
        return $p->patron_type_label;
    }
}
