<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of boffice_standard_interface
 *
 * @author lepercon
 */
class boffice_standard_interface
{
    private $table_name;
    private $key_column;
    private $class_instance;
    private $class_name;
    public $class_property_exclusions;
    public $hook_postinsert;
    public $hook_postupdate;

    public function __construct($class_instance, $class_name, $table_name, $key_column)
    {
        $this->class_instance = $class_instance;
        $this->class_name = $class_name;
        $this->table_name = $table_name;
        $this->key_column = $key_column;
        $this->class_property_exclusions = array();
    }

    public function get($id)
    {
        $parameters_to_db_columns = db_class_parameters_to_data_columns_array_builder($this->class_name, $this->class_property_exclusions);
        if (db_get_class_data($this->class_instance, $parameters_to_db_columns, $this->table_name, $this->key_column, $id)) {
            return true;
        } else {
            new boffice_error("Couldn't get data row. Class: " . get_class($this->class_instance) . ", id= $id");
            //throw new Exception("", "");
            //echo db_get_class_data_query($parameters_to_db_columns, $this->table_name, $this->key_column, $id);
            return false;
        }
    }

    public function set()
    {
        $key_column = $this->key_column;
        $paramters_to_db_columns = db_class_parameters_to_data_columns_array_builder($this->class_name, $this->class_property_exclusions);
        if (isset($this->class_instance->$key_column) && $this->class_instance->$key_column > 0) {
            $return = db_set_class_data($this->class_instance, $paramters_to_db_columns, $this->table_name, $this->key_column, $this->class_instance->$key_column);
            if ($this->hook_postupdate !== null) {
                $function = $this->hook_postupdate;
                $function($this);
            }
            return $return;
        } else {
            //insert
            $new_id = db_set_class_data($this->class_instance, $paramters_to_db_columns, $this->table_name, $this->key_column);
            if ($new_id) {
                $this->class_instance->$key_column = $new_id;
                if ($this->hook_postinsert !== null) {
                    $function = $this->hook_postinsert;
                    $function($this);
                }
                return true;
            } else {
                new boffice_error("Couldn't insert data row. Class:" . get_class($this->class_instance));
                return false;
            }
        }
    }

    public function delete($id)
    {
        global $global_conn;
        $key_column = $this->key_column;
        if (isset($this->class_instance->$key_column) AND $id > 0) {
            $result = db_exec($global_conn, "DELETE FROM " . $this->table_name . " WHERE " . $this->key_column . " = " . db_escape($id));
            return $result['rows_changed'] > 0;
        } else {
            return false;
        }
    }
}
