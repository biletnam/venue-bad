<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of purchasable_seat
 *
 * @author lepercon
 */
class purchasable_seat extends purchasable_seat_abstract
{
    public $purchasable_seat_abstract_id; //parent

    public $purchasable_seat_id;
    public $show_seating_chart_id;
    public $show_seat_name;
    public $position_x;
    public $position_y;
    public $rotation;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_seat', 'purchasable_seat', 'purchasable_seat_id');
        $this->db_interface->class_property_exclusions = array('show_seating_chart');
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        parent::get($this->purchasable_seat_abstract_id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function delete()
    {
        global $global_conn;
        $instance_results = db_query($global_conn, "SELECT show_instance_id FROM purchasable_seat_instance WHERE purchasable_seat_id = " . db_escape($this->purchasable_seat_id));
        $results = db_query($global_conn, "
	    SELECT * FROM purchasable_seat_instance 
	    WHERE 
		purchasable_seat_id = " . db_escape($this->purchasable_seat_id) . " 
		AND seat_status = " . db_escape(purchasable_seat_instance::SEAT_STATUS_RESERVED));
        if (count($results) > 0) {
            return '-1';
        } else {
            return $this->db_interface->delete($this->purchasable_seat_id);
        }
    }

    public function get_price($user)
    {
        $user->user_id; //keeps nb from bitching
        new boffice_error("Cannot access price of purchasable_seat. Use purchasable_seat_instance as it has a show_instance attached.", false);
    }

    /**
     * Determines of two seats are on top of each other. Can also be used to see if seat_a has moved.
     * @param purchasable_seat $seat_a
     * @param purchasable_seat $seat_b
     */
    static public function seats_overlap($seat_a, $seat_b)
    {
        return $seat_a->position_x === $seat_b->position_x AND $seat_a->position_y === $seat_b->position_y;
    }

    public function do_precheckout_processing()
    {
        //There's no required preprocessing for seats.
        return false;
    }

    public function get_readible_name()
    {
        return $this->show_seat_name;
    }
}
