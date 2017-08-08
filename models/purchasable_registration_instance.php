<?php

/**
 * Description of purchasable_registration_instance
 *
 * @author lepercon
 */
class purchasable_registration_instance
{
    public $purchasable_registration_instance_id;
    public $purchasable_registration_instance_datetime;
    public $purchasable_registration_id;
    public $purchasable_item_type;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_registration_instance', 'purchasable_registration_instance', 'purchasable_registration_instance_id');
        $this->db_interface->class_property_exclusions = array('purchasable_item_type');
        $this->purchasable_item_type = purchasable::ITEM_TYPE_REGISTRATION;
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
    }

    public function get_price($user)
    {
        $reggie = new purchasable_registration($this->purchasable_registration_id);
        return $reggie->get_price($user);
    }

    public function get_registrations()
    {
        global $global_conn;
        $return = array();
        $results = db_query($global_conn, "
	    SELECT * FROM cart_item 
	    LEFT JOIN cart USING (cart_id)
	    LEFT JOIN registrations ON (cart_item.resultant_class_id = registrations.registration_id)
	    WHERE cart_item.purchasable_class = 'purchasable_registration_instance'
		AND purchasable_class_id =  " . db_escape($this->purchasable_registration_instance_id) . "
		AND cart.paid != '0000-00-00 00:00:00'
	    ");
        foreach ($results as $row) {
            $return[] = new registration($row['registration_id']);
        }
        return $return;
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get_quantity()
    {
        global $global_conn;
        $reggie = new purchasable_registration($this->purchasable_registration_id);
        $results = db_query($global_conn, "
	    SELECT SUM(quantity) as answer FROM 
		cart 
		LEFT JOIN cart_item USING (cart_id)
		LEFT JOIN transaction USING (cart_id)
		LEFT JOIN registrations USING (transaction_id)
	    WHERE 
		purchasable_class = 'purchasable_registration_instance' 
		AND purchasable_class_id = " . db_escape($this->purchasable_registration_instance_id) . " 
		AND registration_status = 'ACTIVE'");

        return $reggie->get_quantity() - intval($results[0]['answer']);
    }

    public function do_precheckout_processing()
    {
        return false;
    }

    public function react_with_items($items)
    {
        $items = null;
        return false;
    }

    public function get_readible_name()
    {
        $reggie = new purchasable_registration($this->purchasable_registration_id);
        return "<span class='purchasable-title'>" . $reggie->reg_name . "</span> " . date('D M jS, g:ia', strtotime($this->purchasable_registration_instance_datetime));
    }

    public function admin_edit_form($Reg_id)
    {
        global $global_conn;
        $elements = array(
            new f_data_element('Date time', 'purchasable_registration_instance_datetime', 'datetime'),
            new f_data_element('asdf', 'purchasable_registration_id', 'hidden', $Reg_id),
        );
        $form = new f_data($global_conn, 'purchasable_registration_instance', 'purchasable_registration_instance_id', $elements, filter_input(INPUT_GET, 'row_id'));
        return $form->start();
    }

    static public function admin_edit_list_group($purchasable_registration_id)
    {
        global $global_conn, $site_path;
        $href = $site_path . "admin/registration_instances.php";
        $string = "<ul><li><a href='$href?reg_id=$purchasable_registration_id'>New datetime</a></li>";
        foreach (db_query($global_conn, "SELECT * FROM purchasable_registration_instance WHERE purchasable_registration_id = " . db_escape($purchasable_registration_id) . " ORDER BY purchasable_registration_instance_datetime ASC") as $instance) {
            $string .= "<li><a href='" . $href . "?row_id=" . $instance['purchasable_registration_instance_id'] . "&Reg_id=$purchasable_registration_id'>" . date("M jS, Y, g:ia", strtotime($instance['purchasable_registration_instance_datetime'])) . "</a></li>";
        }
        return $string;
    }

    public function display($h = 2)
    {
        global $site_path;
        $reggie = new purchasable_registration($this->purchasable_registration_id);
        $string = $reggie->display($h, false);
        $string .= "
	    <div class='purchasable-registration-instance'>
		" . date("D M jS, g:ia", strtotime($this->purchasable_registration_instance_datetime)) . " - " . $this->get_quantity() . " seats available
		<form id='purchasable-registration-instance-quantity' action='" . $site_path . "register/" . $this->purchasable_registration_id . "/" . $this->purchasable_registration_instance_id . "' method='POST' >
		    <label for='quantity'>Quantity</label><select id='quantity' name='quantity'>
			";
        for ($i = 1; $i < min(array(10, $this->get_quantity())); $i++) {
            $string .= "<option value='$i'>$i</option>";
        }
        $string .= "
		    </select>
		    <button type='submit'>Add to Cart</button>
		</form>
	    </div>";
        return $string;
    }

    public function shop()
    {
        global $site_path;
        user::login_required($site_path . "register/" . $this->purchasable_registration_id . "/" . $this->purchasable_registration_instance_id);
        if (filter_input(INPUT_POST, 'purchasable_registration_instance') === 'true') {

        }

        if (boffice_error::has_form_errors() OR filter_input(INPUT_POST, 'purchasable_registration_instance') !== 'true') {
            return $this->shopping_form();
        }
    }

    private function shopping_form()
    {
        global $site_path;
        $reggie = new purchasable_registration($this->purchasable_registration_id);
        $string = "<form action='" . $site_path . "register/" . $this->purchasable_registration_id . "/" . $this->purchasable_registration_instance_id . "' method='POST' >
	    <label for='quantity'>Quantity</label><select id='quantity' name='quantity'>";
        for ($i = 0; $i < min(array(9, $this->get_quantity())); $i++) {
            $string .= "<option value='$i'>$i - $" . money($reggie->purchasable_price * $i) . "</option>";
        }
        $string .= "</select>
	    <button type='SUBMIT' value='Add to cart'>Add to cart</button>
	    <input type='hidden' name='purchasable_registration_instance' value='true' />
	    </form>";
        return $string;
    }

    public function do_process($items, $cart_item, $transaction_id = 0)
    {
        if ($transaction_id > 0 AND intval($cart_item->resultant_class_id) === 0) {
            $registration = new registration();
            $registration->registration_status = registration::REGISTRATION_STATUS_ACTIVE;
            $registration->transaction_id = $transaction_id;
            $registration->set();

            /* @var $item cart_item */
            foreach ($items as $item) {
                /* @var $cart_object purchasable_registration_instance */
                $cart_object = $item->get_cart_object();
                if ($cart_object->purchasable_item_type === $this->purchasable_item_type
                    AND $cart_object->purchasable_registration_instance_id === $this->purchasable_registration_instance_id
                    AND intval($item->resultant_class_id) === 0
                ) {
                    $item->resultant_class_id = $registration->registration_id;
                    $item->set_cart_item();
                }
            }
        }

        //nothing left to do
        return true;
    }

}
