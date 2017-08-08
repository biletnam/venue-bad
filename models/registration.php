<?php

/**
 * Description of registration
 *
 * @author lepercon
 */
class registration extends transaction
{
    public $transaction_id; //parent
    public $registration_id;

    public $registration_status;
    const REGISTRATION_STATUS_ACTIVE = "ACTIVE";
    const REGISTRATION_STATUS_CANCELLED = "CANCELLED";

    private $db_interface;

    public function __construct($registration_id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'registration', 'registrations', 'registration_id');
        parent::__construct();
        if ($registration_id !== null) {
            $this->get($registration_id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        parent::get_transaction($this->transaction_id);
    }

    public function set()
    {
        $this->db_interface->set();
    }
}
