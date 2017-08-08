<?php


/**
 * Description of purchasable_seat_instance
 *
 * @author lepercon
 */
class purchasable_seat_instance extends purchasable_seat
{
    public $purchasable_seat_id; //parent

    public $purchasable_seat_instance_id;
    public $show_instance_id;
    public $show_instance;

    public $seat_status;
    const SEAT_STATUS_AVAILABLE = "SEAT_STATUS_AVAILABLE";
    const SEAT_STATUS_RESERVED = "SEAT_STATUS_RESERVED";
    const SEAT_STATUS_UNAVAILABLE = "SEAT_STATUS_UNAVAILABLE";


    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_seat_instance', 'purchasable_seat_instance', "purchasable_seat_instance_id");
        $this->db_interface->class_property_exclusions = array('show_instance');
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id, $subsequent_classes = true)
    {
        $this->db_interface->get($id);
        $this->show_instance = new show_instance($this->show_instance_id);
        if ($subsequent_classes) {
            if ($this->show_instance_id > 0) {
                $this->show_instance = new show_instance($this->show_instance_id);
            }
        }
        parent::get($this->purchasable_seat_id, false);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get_price($user)
    {
        if ($this->show_instance === null) {
            new boffice_error("Cannot access get_price of purchasable_seat_instance that has no show_instance assigned", true);
        }
        $model_name = $this->show_instance->show_seat_price_model;
        $model = new $model_name();
        $cart_item = cart_item::get_cart_item_from_class_id("purchasable_seat_instance", $this->purchasable_seat_instance_id);
        return $model->get_price($user, $this, $cart_item);
    }

    public function get_current_icon()
    {
        if ($this->seat_status === purchasable_seat_instance::SEAT_STATUS_AVAILABLE) {
            $url = $this->purchasable_seat_abstract_icon_available_url;
        } else {
            $url = $this->purchasable_seat_abstract_icon_unavailable_url;
        }
        return $url;
    }

    public function get_readible_name($include_show_name = true, $short_seat_statement = false)
    {
        global $site_domain, $site_path;
        $string = "";
        if ($include_show_name) {
            $string .= "<img src='//" . $site_domain . $site_path . "_resources/images/cal.png' />
		" . date("M jS, g:ia", strtotime($this->show_instance->datetime)) . ". <span class='show-title'>" . $this->show_instance->title . "</span><br />";
        }
        $this->show_instance->seating_chart();
        return $string . $this->show_instance->seating_chart->readible_seat_name($this, $short_seat_statement);
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
                /* @var $cart_object purchasable_seat_instance */
                $cart_object = $item->get_cart_object();

                if (isset($cart_object->show_instance)
                    AND $cart_object->show_instance->show_instance_id === $this->show_instance->show_instance_id
                    AND intval($item->resultant_class_id) === 0
                ) {
                    $item->resultant_class_id = $reservation->reservation_id;
                    $item->cart_item_id = cart_item::get_cart_item_id($item);
                    $item->set_cart_item();
                    reservation_ticket::create($item->purchasable_class_id, $reservation->reservation_id);
                }
            }
        }

        $this->seat_status = purchasable_seat_instance::SEAT_STATUS_RESERVED;
        $this->set();

        if (!isset($_SESSION['performing_batch']) OR $_SESSION['performing_batch'] !== true) {
            $this->show_instance->seating_chart_html_update();
            show_instance_cache::update($this->show_instance->show_instance_id);
        }

        return true;
    }


}
