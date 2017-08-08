<?php


/**
 * Description of purchasable_seat_instance
 *
 * @author lepercon
 */
class purchasable_seating_general extends purchasable
{
    public $purchasable_seating_general_id;
    public $show_instance_id;
    public $purchasable_seating_general_quantity_total;
    public $purchasable_seating_general_status;

    /**
     * @var show_instance
     */
    public $show_instance;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_seating_general', 'purchasable_seating_general', "purchasable_seating_general_id");
        $this->db_interface->class_property_exclusions = array('show_instance');
        $this->purchasable_id = 4;
        parent::__construct();
        parent::get('4');
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        $this->show_instance = new show_instance($this->show_instance_id);
    }

    public static function get_by_show_instance_id($show_instance_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM purchasable_seating_general WHERE show_instance_id = " . db_escape($show_instance_id) . " LIMIT 1;");
        if (count($results) > 0) {
            return new purchasable_seating_general($results[0]['purchasable_seating_general_id']);
        } else {
            return new purchasable_seating_general();
        }
    }

    public function set()
    {
        $this->db_interface->set();
        if ($this->show_instance->show_instance_id !== $this->show_instance_id) {
            $this->show_instance = new show_instance($this->show_instance_id);
        }
    }

    public function get_price($user)
    {
        if ($this->show_instance === null) {
            new boffice_error("Cannot access get_price of purchasable_seating_general that has no show_instance assigned", true);
        }
        $model_name = $this->show_instance->show_seat_price_model;
        $model = new $model_name();
        $cart_item = cart_item::get_cart_item_from_class_id("purchasable_seating_general", $this->purchasable_seating_general_id);
        return $model->get_price($user, $this, $cart_item);
    }

    public function get_quantity()
    {
        global $global_conn;
        $results = db_query($global_conn, "
	    SELECT SUM(quantity) AS total FROM cart_item 
	    LEFT JOIN reservation ON (cart_item.resultant_class_id = reservation.reservation_id)
	    WHERE purchasable_class = 'purchasable_seating_general'
		AND purchasable_class_id = " . db_escape($this->purchasable_seating_general_id) . " 
		AND resultant_class_id > 0
		AND reservation_status = 'ACTIVE'
	    ;");
        return $this->purchasable_seating_general_quantity_total - intval($results[0]['total']);
    }

    public function get_readible_name($include_show_name = true)
    {
        global $site_domain, $site_path;
        $string = "";
        if ($include_show_name) {
            $string .= "<img src='//" . $site_domain . $site_path . "_resources/images/cal.png' />
		" . date("M jS, g:ia", strtotime($this->show_instance->datetime)) . ". <span class='show-title'>" . $this->show_instance->title . "</span>";
        } else {
            $string .= "seat";
        }
        return $string;
    }


    public function do_process($items, $cart_item, $transaction_id = 0)
    {
        if ($transaction_id > 0 AND intval($cart_item->resultant_class_id) === 0) {
            $reservation = new reservation();
            $reservation->reservation_status = reservation::RESERVATION_STATUS_ACTIVE;
            $reservation->transaction_id = $transaction_id;
            $reservation->set();

            /* @var $item cart_item */
            foreach ($items as $item) {
                /* @var $cart_object purchasable_seating_general */
                $cart_object = $item->get_cart_object();

                if (isset($cart_object->show_instance)
                    AND $cart_object->show_instance->show_instance_id === $this->show_instance->show_instance_id
                    AND intval($item->resultant_class_id) === 0
                ) {
                    if ($item->cart_item_id === null) {
                        $item->cart_item_id = cart_item::get_cart_item_id($item);
                    }
                    $item->resultant_class_id = $reservation->reservation_id;
                    $item->set_cart_item();

                }
            }
        }
        return true;
    }

    public function react_with_items($items, $is_test = false)
    {
        isset($items);
        isset($is_test); //warning suppression


        /**
         * I'm 99% sure package deductions for general and reserved seating is handled in the package class. but i should check that. @todo
         */
        return 0;
    }

}
