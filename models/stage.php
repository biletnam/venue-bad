<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stage
 *
 * @author lepercon
 */
class stage
{
    public $stage_id;
    public $stage_name;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "stage", "stage", "stage_id");
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
}
