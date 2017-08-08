<?php

/**
 * Description of purchasable_registration
 *
 * @author lepercon
 */
class purchasable_registration extends purchasable
{
    public $purchasable_registration_id;
    public $purchasable_id; //parent
    public $reg_name;
    public $reg_description;
    public $reg_img_url;
    public $reg_price;
    public $reg_quantity;
    public $reg_date_start;
    public $reg_date_end;
    public $reg_date_sales_start;
    public $reg_date_sales_end;
    public $reg_sales_available;


    /**
     * @var purchasable_registration_category
     */
    public $reg_category;
    public $purchasable_registration_category_id;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_registration', 'purchasable_registration', 'purchasable_registration_id');
        $this->db_interface->class_property_exclusions = array('reg_category', 'purchasable_id', 'purchasable_item_type');
        $this->purchasable_item_type = purchasable::ITEM_TYPE_REGISTRATION;
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        $this->reg_category = new purchasable_registration_category($this->purchasable_registration_category_id);
        //I'm not getting the parent 'purchasable' class info cause I don't think i'll need
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get_price($user = null)
    {
        $user = null;
        return $this->reg_price;
    }

    public function get_quantity()
    {
        global $global_conn;
        $results = db_query($global_conn, "
	    SELECT SUM(quantity) as answer FROM 
		cart 
		LEFT JOIN cart_item USING (cart_id)
		LEFT JOIN transaction USING (cart_id)
		LEFT JOIN registrations USING (transaction_id)
	    WHERE 
		purchasable_class = 'purchasable_registration' 
		AND purchasable_class_id = " . db_escape($this->purchasable_registration_id) . " 
		AND registration_status = 'ACTIVE'");
        return $this->reg_quantity - intval($results[0]['answer']);
    }

    public function get_tax_rate()
    {
        return 0;
    }

    public function get_readible_name()
    {
        return $this->reg_name;
    }

    public function react_with_items($items, $is_test = false)
    {
        parent::react_with_items($items, $is_test);
    }

    public function do_precheckout_processing()
    {
        //registration form
    }

    public function admin_edit_form()
    {
        global $global_conn;
        $cat_options = array();
        foreach (db_query($global_conn, 'SELECT * FROM purchasable_registration_category') as $cat) {
            $cat_options[$cat['purchasable_registration_category_id']] = $cat['purchasable_registration_category_name'];
        }
        $elements = array(
            new f_data_element('Event Name', 'reg_name', 'text'),
            new f_data_element('Category', 'purchasable_registration_category_id', 'select', $cat_options),
            new f_data_element('Price', 'reg_price', 'text'),
            new f_data_element('Sales Available', 'reg_sales_available', 'select', array(
                'ITEM_STATUS_AVAILABLE' => 'Not restricted',
                'ITEM_STATUS_UNAVAILABLE' => 'Locked Off',
                'ITEM_STATUS_SOLDOUT' => 'Regardless of actual quantity, reports as sold out',
            )),
            new f_data_element('Image', 'reg_img_url', 'file'),
            new f_data_element('Quantity', 'reg_quantity', 'text'),
            new f_data_element('Date Start', 'reg_date_start', 'datetime'),
            new f_data_element('Date End', 'reg_date_end', 'datetime'),
            new f_data_element('Sales Start', 'reg_date_sales_start', 'datetime'),
            new f_data_element('Sales End', 'reg_date_sales_end', 'datetime'),
            new f_data_element('Description', 'reg_description', 'wysiwyg'),
        );
        $form = new f_data($global_conn, 'purchasable_registration', 'purchasable_registration_id', $elements, $this->purchasable_registration_id);
        boffice_html::$standard_datetime_picker = true;
        return $form->start() . purchasable_registration_instance::admin_edit_list_group($this->purchasable_registration_id);
    }

    public function admin_list($href)
    {
        global $global_conn;
        $string = "<ul><li><a href='$href'>New registrationable</a></li>";
        foreach (db_query($global_conn, "SELECT * FROM purchasable_registration LEFT JOIN purchasable_registration_category USING (purchasable_registration_category_id) ORDER BY purchasable_registration_category_name, reg_name ASC") as $item) {
            $string .= "<li><a href='" . $href . "?row_id=" . $item['purchasable_registration_id'] . "'>" . $item['purchasable_registration_category_name'] . " - " . $item['reg_name'] . "</a></li>";
        }
        $string .= "</ul>";
        return $string;
    }

    public function instances()
    {
        global $global_conn;
        $results = array();
        foreach (db_query($global_conn, "SELECT * FROM purchasable_registration_instance WHERE purchasable_registration_id = " . db_escape($this->purchasable_registration_id)) as $item) {
            $results[] = new purchasable_registration_instance($item['purchasable_registration_instance_id']);
        }
        return $results;
    }

    public function get_instances()
    {
        //alias of instances
        return $this->instances();
    }

    public function is_available()
    {
        return $this->reg_quantity > 0 AND $this->reg_sales_available === purchasable::ITEM_STATUS_AVAILABLE;
    }

    public function get_date_range()
    {
        $instances = $this->get_instances();
        $new_order = array();
        if (count($instances)) {
            foreach ($instances as $instance) {
                $new_order[strtotime($instance->purchasable_registration_instance_datetime)] = $instance;
            }
            sort($new_order);
            $first = current($new_order);
            $return = array(strtotime($first->purchasable_registration_instance_datetime));
            end($new_order);
            $end = current($new_order);
            $return[] = strtotime($end->purchasable_registration_instance_datetime);
            return $return;
        } else {
            return array(strtotime($this->reg_date_start), strtotime($this->reg_date_end));
        }
    }

    public function display($h = 2, $as_feature = true)
    {
        global $site_path;
        $date_range = $this->get_date_range();
        $instances = $this->get_instances();
        $string = "
	    <div class='purchasable-registration display " . ($as_feature ? 'feature' : 'summary') . "'>
		";
        if ($this->reg_img_url !== "") {
            $string .= "<img class='featured-image' src='" . $this->reg_img_url . "' />";
        } else {
            $string .= "<img class='featured-image generic' src='" . $site_path . "icons/generic_event.png' />";
        }
        $string .= "
		<h$h>" . $this->reg_name . "</h$h>
		<div class='date-range'>" . display_date_range($date_range[0], $date_range[1]) . "</div>
		<div class='price'>$" . money($this->get_price()) . "</div>
		<div class='status'>" . $this->get_status_statement(false) . "<div>";
        if ($as_feature) {
            $string .= "<div class='description'>" . $this->reg_description . "</div>";
        }

        if ($this->is_available()) {
            if (count($instances) === 0 AND $as_feature) {
                $string = "
			<div class='add-to-cart available'><form action='" . $site_path . "register/" . $this->purchasable_registration_id . "/0' method='POST' >
			    <label for='quantity'>Quantity</label><select name='quantity' id='quantity'>";
                for ($i = 0; $i < min(array($this->get_quantity(), 9)); $i++) {
                    "<option value='$i'>$i</option>";
                }
                $string .= "
			    </select>
			    <button type='SUBMIT' value='Add to cart'>Add to Cart</button>
			</form></div>";
            } else if (count($instances) > 0 AND $as_feature) {
                foreach ($instances as $instance) {
                    $string = "
			    <div class='add-to-cart available'>
				<div class='date-time'>" . date("D, M jS, Y", strtotime($instance->purchasable_registration_instance_datetime)) . "</div>
				<form action='" . $site_path . "register/" . $this->purchasable_registration_id . "/" . $instance->purchasable_registration_instance_id . "' method='POST' >
				<label for='quantity'>Quantity</label><select name='quantity' id='quantity'>";
                    for ($i = 0; $i < min(array($this->get_quantity(), 9)); $i++) {
                        "<option value='$i'>$i</option>";
                    }
                    $string .= "
				</select>
				<button type='SUBMIT' value='Add to cart'>Add to Cart</button>
			    </form></div>";
                }
            } else {
                if (count($instances) > 0) {
                    $string .= "<ul class='purchasable-registration-instance'>";
                    foreach ($instances as $instance) {
                        if ($instance->get_quantity() > 0) {
                            $string .= "<li><a href='" . $site_path . "register/" . $this->purchasable_registration_id . "/" . $instance->purchasable_registration_instance_id . "'>" . date("D M jS, g:ia", strtotime($instance->purchasable_registration_instance_datetime)) . "</a></li>";
                        } else {
                            $string .= "<li>" . date("D M jS, g:ia", strtotime($instance->purchasable_registration_instance_datetime)) . " - Sold Out</li>";
                        }

                    }
                    $string .= "</ul>";
                }
                //$string .= "<div class='get-instances'><a href='".$site_path."register/".$this->purchasable_registration_id."/0'>View Availability</a></div>";
            }

        }
        $string .= "
	    </div>
	";
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
                /* @var $cart_object purchasable_registration */
                $cart_object = $item->get_cart_object();
                if ($cart_object->purchasable_item_type === $this->purchasable_item_type
                    AND $cart_object->purchasable_registration_id === $this->purchasable_registration_id
                    AND intval($item->resultant_class_id) === 0
                ) {
                    $item->resultant_class_id = $registration->registration_id;
                    $item->cart_item_id = cart_item::get_cart_item_id($item);
                    $item->set_cart_item();
                }
            }
        }

        //nothing left to do
        return true;
    }


    public function admin_office_list()
    {
        $string = "<span class='new link'>Create a new meeting time for " . $this->reg_name . "</span><div class='accordion'>";
        foreach ($this->instances() as $instance) {
            $string .= "
		<h3>" . $instance->get_readible_name() . " (" . ($this->reg_quantity - $instance->get_quantity()) . " of " . $this->reg_quantity . ")</h3>
		<div purchasable_registration_instance_id='" . $instance->purchasable_registration_instance_id . "'>
		<h4>Roster</h4>
		<ul>";
            /* @var $registration registration */
            $registrations = $instance->get_registrations();
            if (count($registrations)) {
                foreach ($instance->get_registrations() as $registration) {
                    $user = new user($registration->user_id);
                    $string .= "
			    <li>
				<span class='user link' user_id='" . $user->user_id . "'>" . ucfirst($user->user_name_last) . ", " . ucfirst($user->user_name_first) . "</span>
				<span class='reservation_status link " . $registration->registration_status . "'>" . $registration->registration_status . "</span>
				<span class='transaction-details link' transaction_id='" . $registration->transaction_id . "'>Transaction Details</span>
			    </li>";
                }
            }
            $string .= "</ul><br />
		    <span class='new-registration link' purchasable_registration_instance_id='" . $instance->purchasable_registration_instance_id . "'>Create a new registration</span>
		    </div>	
		";
        }
        return $string .= "</div>";
    }

}
