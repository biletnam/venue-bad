<?php

/**
 * Description of reservation
 *
 * @author lepercon
 */
class package_usage extends package
{
    public $package_usage_id;
    public $package_id; //parent
    public $transaction_id;
    public $package_usage_deduction;
    public $benefit_id;


    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'package_usage', 'package_usage', 'package_usage_id');
        parent::__construct();
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

    public function get_benefit($benefit_type)
    {
        $benefit_type = "not used";
        return new purchasable_package_model_benefit($this->benefit_id);
    }

}
