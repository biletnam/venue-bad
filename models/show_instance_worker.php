<?php

/**
 * Description of show_instance_worker
 *
 * @author lepercon
 */
class show_instance_worker
{
    public $show_instance_worker_id;
    public $show_instance_worker_type_id;
    public $show_instance_id;
    public $user_id;
    public $show_instance_worker_plus_one;
    public $show_instance_worker_status;

    CONST SHOW_INSTANCE_WORKER_STATUS_FILLED = "FILLED";
    CONST SHOW_INSTANCE_WORKER_STATUS_UNFILLED = "UNFILLED";

    /**
     * @var \user
     */
    public $user;
    /**
     * @var \show_instance
     */
    public $show_instance;
    /**
     * @var \show_instance_worker_type
     */
    public $show_instance_worker_type;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "show_instance_worker", "show_instance_workers", "show_instance_worker_id");
        $this->db_interface->class_property_exclusions = array('user', 'show_instance', 'show_instance_worker_type');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        if ($this->user_id > 0) {
            $this->user = new user($this->user_id);
        }
        $this->show_instance = new show_instance($this->show_instance_id);
        $this->show_instance_worker_type = new show_instance_worker_type($this->show_instance_worker_type_id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    static public function fill($show_instance_worker_id, $user_id, $plus_one = '0')
    {
        $show_instance_worker = new show_instance_worker($show_instance_worker_id);
        if ($show_instance_worker->user_id > 0 OR $show_instance_worker->show_instance_worker_status !== show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_UNFILLED) {
            return false;
        }
        $show_instance_worker->user_id = $user_id;
        $show_instance_worker->show_instance_worker_plus_one = $plus_one;
        $show_instance_worker->show_instance_worker_status = show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_FILLED;
        $show_instance_worker->set();

        $show_instance = new show_instance($show_instance_worker->show_instance_id);
        $show_instance_worker_type = new show_instance_worker_type($show_instance_worker->show_instance_worker_type_id);
        $user = new user($user_id);
        $html = "<p>You have been assigned to working " . $show_instance->title . " at " . date("Y-m-d H:i:s", strtotime($show_instance->datetime)) . " as '" . $show_instance_worker_type->show_instance_worker_type_name . "'</p>";
        if ($plus_one !== '0') {
            $html .= "<p>We have also reserved a volunteer spot for your plus one</p>";
        }
        send_email($user->user_email, "no-reply@trustus.org", "Show work assignment", strip_tags($html), $html);
    }

    static public function unfill($show_instance_worker_id)
    {
        $show_instance_worker = new show_instance_worker($show_instance_worker_id);
        $user = $show_instance_worker->user;
        $show_instance = $show_instance_worker->show_instance;
        $show_instance_worker_type = $show_instance_worker->show_instance_worker_type;
        $plus_one = $show_instance_worker->show_instance_worker_plus_one;
        $show_instance_worker->user_id = 0;
        $show_instance_worker->show_instance_worker_plus_one = '0';
        $show_instance_worker->show_instance_worker_status = show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_UNFILLED;
        $show_instance_worker->set();

        $html = "<p>You have been unassigned from working " . $show_instance->title . " at " . date("Y-m-d H:i:s", strtotime($show_instance->datetime)) . " as '" . $show_instance_worker_type->show_instance_worker_type_name . "'</p>";
        if ($plus_one !== "0") {
            $html .= "<p>We have also unassigned your plus one.</p>";
        }
        send_email($user->user_email, "no-reply@trustus.org", "Show work cancellation", strip_tags($html), $html);
    }

    public function admin_edit_form_create()
    {
        global $global_conn;
        $type_options = array();
        foreach (db_query($global_conn, "SELECT * FROM show_instance_worker_types ORDER BY show_instance_worker_type_name ASC") as $row) {
            $type_options[$row['show_instance_worker_type_id']] = $row['show_instance_worker_type_name'];
        }

        $elements = array(
            new f_data_element('Job Type', 'show_instance_worker_type_id', 'select', $type_options)
        );

        $form = new f_data($global_conn, 'show_instance_workers', 'show_instance_worker_id', $elements, $this->show_instance_worker_id);
        return $form->start();
    }
}
