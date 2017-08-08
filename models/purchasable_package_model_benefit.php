<?php

/**
 * Description of package_model
 *
 * @author lepercon
 */

class purchasable_package_model_benefit
{
    public $package_model_benefit_id;
    public $package_model_id;
    public $package_model_benefit_label;
    public $package_model_benefit_type_class;
    public $package_model_benefit_value;

    const PACKAGE_MODEL_BENEFIT_TYPE_TICKET = "PACKAGE_MODEL_BENEFIT_TYPE_TICKET";
    const PACKAGE_MODEL_BENEFIT_TYPE_CONCESSION = "PACKAGE_MODEL_BENEFIT_TYPE_CONCESSION";
    const PACKAGE_MODEL_BENEFIT_TYPE_MERCHANDISE = "PACKAGE_MODEL_BENEFIT_TYPE_MERCHANDISE";
    public $package_model_benefit_type;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_package_model_benefit', 'purchasable_package_model_benefit', 'package_model_benefit_id');
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
        return $this->db_interface->delete($this->package_model_id);
    }


    /**
     * @param cart_item $cart_item
     */
    public function reacts_with($cart_item)
    {
        if ($this->package_model_benefit_type_class === $cart_item->purchasable_class) {
            //Not sure this goes here. See: brain
        }
    }

}
