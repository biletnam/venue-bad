<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of seating_chart_extras_instance
 *
 * @author lepercon
 */
class seating_chart_extras_instance extends seating_chart_extras
{
    public $seating_chart_extra_id; //parent

    public $seating_chart_extras_instance_id;
    public $seating_chart_id;
    public $seating_chart_extras_instance_x;
    public $seating_chart_extras_instance_y;
    public $seating_chart_extras_instance_rotation;

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }

    }

    public function get($id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM seating_chart_extras_instance WHERE seating_chart_extras_instance_id = " . db_escape($id, $global_conn));
        $this->seating_chart_extras_instance_id = $results[0]['seating_chart_extras_instance_id'];
        $this->seating_chart_extras_instance_x = $results[0]['seating_chart_extras_instance_x'];
        $this->seating_chart_extras_instance_y = $results[0]['seating_chart_extras_instance_y'];
        $this->seating_chart_extras_instance_rotation = $results[0]['seating_chart_extras_instance_rotation'];
        parent::get($results[0]['seating_chart_extra_id']);
    }

    public function set()
    {
        global $global_conn;
        $values = array(
            'seating_chart_extras_instance_x' => $this->seating_chart_extras_instance_x,
            'seating_chart_extras_instance_y' => $this->seating_chart_extras_instance_y,
            'seating_chart_extras_instance_rotation' => $this->seating_chart_extras_instance_rotation,
        );
        if ($this->seating_chart_extras_instance_id !== null AND $this->seating_chart_extras_instance_id > 0) {
            $result = db_exec($global_conn, build_update_query($global_conn, 'seating_chart_extras_instance', $values, "seating_chart_extras_instance_id = " . db_escape($this->seating_chart_extras_instance_id)));
        } else {
            $values['seating_chart_extra_id'] = $this->seating_chart_extra_id;
            $values['seating_chart_id'] = $this->seating_chart_id;
            $result = db_exec($global_conn, build_insert_query($global_conn, 'seating_chart_extras_instance', $values));
            if ($result) {
                $this->seating_chart_extras_instance_id = $global_conn->lastInsertId();
            }
        }
        return $result;
    }

    public function delete()
    {
        global $global_conn;
        return db_exec($global_conn, "DELETE FROM seating_chart_extras_instance WHERE seating_chart_extras_instance_id = " . db_escape($this->seating_chart_extras_instance_id));
    }
}
