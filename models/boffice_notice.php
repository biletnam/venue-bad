<?php

/**
 * Description of boffice_notice
 *
 * @author lepercon
 */
class boffice_notice
{
    public $boffice_notice_id;
    public $boffice_notice_acknowledged;
    public $boffice_notice_datetime;
    public $boffice_notice_relates_user_id;
    public $boffice_notice_relates_reservation_id;
    public $boffice_notice_message;

    public $boffice_notice_severity;
    CONST BOFFICE_NOTICE_SEVERITY_LOW = "LOW";
    CONST BOFFICE_NOTICE_SEVERITY_NORMAL = "NORMAL";
    CONST BOFFICE_NOTICE_SEVERITY_HIGH = "HIGH";

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "boffice_notice", "boffice_notice", "boffice_notice_id");
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

    static public function create($message, $severity = boffice_notice::BOFFICE_NOTICE_SEVERITY_NORMAL, $user_id = null, $reservation_id = null)
    {
        $notice = new boffice_notice();
        $notice->boffice_notice_message = $message;
        $notice->boffice_notice_acknowledged = '0';
        $notice->boffice_notice_datetime = date("Y-m-d H:i:s");
        $notice->boffice_notice_relates_reservation_id = ($reservation_id === null ? '0' : $reservation_id);
        $notice->boffice_notice_relates_user_id = ($user_id === null ? '0' : $user_id);
        $notice->boffice_notice_severity = $severity;
        $notice->set();
        return $notice;
    }

    public function acknowledge()
    {
        $this->boffice_notice_acknowledged = '1';
        $this->set();
    }

    static public function list_unacknowledged()
    {
        global $global_conn;
        $return = array();
        $results = db_query($global_conn, "SELECT boffice_notice_id FROM boffice_notice WHERE boffice_notice_acknowledged = '0' ORDER BY boffice_notice_datetime ASC;");
        foreach ($results as $row) {
            $return[] = new boffice_notice($row['boffice_notice_id']);
        }
        return $return;
    }
}
