<?php

/**
 * Description of show_instance_worker_type
 *
 * @author lepercon
 */
class show_instance_worker_type
{
    public $show_instance_worker_type_id;
    public $show_instance_worker_type_name;
    public $show_instance_worker_type_description;
    public $show_instance_worker_type_requirements;
    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "show_instance_worker_type", "show_instance_worker_types", "show_instance_worker_type_id");
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
