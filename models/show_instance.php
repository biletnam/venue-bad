<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of show_instance
 *
 * @author lepercon
 */
class show_instance extends show
{
    public $show_id; //parent

    public $show_instance_id;
    public $datetime;
    public $instance_sale_enabled;
    /**
     * @var purchasable_seating_general
     */
    public $purchasable_seating_general;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'show_instance', 'show_instance', 'show_instance_id');
        $this->db_interface->class_property_exclusions = array("purchasable_seating_general");
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function admin_edit_form($href = '')
    {
        global $global_conn;
        if ($this->show_instance_id) {
            $elements = array(
                //new f_data_element('Date/Time','datetime','datetime'),
                new f_data_element('Sales Enabled', 'instance_sale_enabled', 'select', array('0' => 'Sales are disabled', '1' => 'Sales are enabled'))
            );
            $f = new f_data($global_conn, 'show_instance', 'show_instance_id', $elements, $this->show_instance_id);
            $f->form_action = $href;
            return $f->start();
        } else {
            new boffice_error("Cannot created editable form for new instances. Instances can only be edited. Chicken V Egg.", false);
        }
    }


    public function seating_chart_instance_data()
    {
        global $global_conn;
        $arr = array('seats' => array(), 'extras' => array());
        foreach (db_query($global_conn, "SELECT * FROM purchasable_seat_instance WHERE show_instance_id = " . db_escape($this->show_instance_id)) as $item) {
            $arr['seats'][] = new purchasable_seat_instance($item['purchasable_seat_instance_id']);
        }
        foreach (db_query($global_conn, "SELECT * FROM seating_chart_extras_instance WHERE seating_chart_id = " . db_escape($this->seating_chart_id)) as $extra) {
            $arr['extras'][] = new seating_chart_extras_instance($extra['seating_chart_extras_instance_id']);
        }
        return $arr;
    }

    private function seating_chart_generate_html($icon_size = 32)
    {
        //db_exec($global_conn,"SET global tmp_table_size= 1000000000;");
        //db_exec($global_conn,"SET global max_heap_table_size = 1000000000");
        set_time_limit(9999);

        $data = $this->seating_chart_instance_data();
        $string = "";
        $max_y = 0;
        $max_x = 0;
        foreach ($data['seats'] as $s) {
            $string .= "<img class='object seat " . $s->seat_status;
            if ($s->seat_status === purchasable_seat_instance::SEAT_STATUS_RESERVED) {
                $cart_item = cart_item::get_cart_item_from_class_id("purchasable_seat_instance", $s->purchasable_seat_instance_id);
                $string .= " rid" . $cart_item->resultant_class_id . "' reservation_id='" . $cart_item->resultant_class_id . "' ";
            } else {
                $string .= "' ";
            }
            $string .= "
		    style=' left:" . ($icon_size * $s->position_x) . "px; top:" . ($icon_size * $s->position_y) . "px; '
		    src='" . $s->get_current_icon() . "' 
		    position_x='" . $s->position_x . "' 
		    position_y='" . $s->position_y . "'
		    rotation='" . $s->rotation . "' 
		    purchasable_seat_instance_id='" . $s->purchasable_seat_instance_id . "'
		    display_price='" . $s->get_price(user::current_user()) . "' 
		    display_name='" . $s->purchasable_seat_abstract_name . "' 
		/>";
            $max_x = max(array($s->position_x * $icon_size, $max_x));
            $max_y = max(array($s->position_y * $icon_size, $max_y));
        }
        foreach ($data['extras'] as $e) {
            $string .= "
		<img class='object extra' 
		    style=' left:" . ($icon_size * $e->seating_chart_extras_instance_x) . "px; top:" . ($icon_size * $e->seating_chart_extras_instance_y) . "px; '
		    src='" . $e->seating_chart_extra_icon_url . "' 
		    position_x='" . $e->seating_chart_extras_instance_x . "' 
		    position_y='" . $e->seating_chart_extras_instance_y . "'
		    rotation='" . $e->seating_chart_extras_instance_rotation . "' 
		    seating_chart_extras_instance_id='" . $e->seating_chart_extras_instance_id . "'
		/>";
            $max_x = max(array($e->seating_chart_extras_instance_x * $icon_size, $max_x));
            $max_y = max(array($e->seating_chart_extras_instance_y * $icon_size, $max_y));
        }
        return "<div id='seating-chart' style='height:" . ($max_y + $icon_size) . "px; width:" . ($max_x + $icon_size) . "px;'>" . $string . "</div>";
    }

    public function seating_chart_html()
    {
        global $global_conn;
        if ($this->seating_chart_id > 0) {
            //Reserved Seating
            $results = db_query($global_conn, "SELECT * FROM show_instance WHERE show_instance_id = " . db_escape($this->show_instance_id));

            //The div wrapper for an empty seating chart is 63characters
            if (strlen($results[0]['seating_chart_html_cache']) > 99) {
                return $results[0]['seating_chart_html_cache'];
            } else {
                $this->seating_chart_html_update();
                return $this->seating_chart_generate_html();
            }
        } else {
            //General seating does not require fancy html
            return "";
        }
    }

    public function seating_chart_html_update($icon_size = 32)
    {
        global $global_conn;
        $html = $this->seating_chart_generate_html($icon_size);
        db_exec($global_conn, "UPDATE show_instance SET seating_chart_html_cache = " . db_escape($html) . " WHERE show_instance_id = " . db_escape($this->show_instance_id));
    }

    public function get_reservations()
    {
        global $global_conn;
        if ($this->seating_chart_id > 0) {
            $query = "
		SELECT resultant_class_id AS reservation_id FROM purchasable_seat_instance 
		LEFT JOIN cart_item ON (purchasable_seat_instance.purchasable_seat_instance_id = cart_item.purchasable_class_id)
		WHERE seat_status = " . db_escape(purchasable_seat_instance::SEAT_STATUS_RESERVED) . "
		    AND show_instance_id = " . db_escape($this->show_instance_id) . "
		    AND cart_item.purchasable_class = 'purchasable_seat_instance'
		GROUP BY resultant_class_id";
        } else {
            $query = "
		SELECT resultant_class_id AS reservation_id FROM cart_item 
		LEFT JOIN purchasable_seating_general ON (purchasable_seating_general.purchasable_seating_general_id = cart_item.purchasable_class_id)
		WHERE cart_item.purchasable_class = 'purchasable_seating_general' 
		    AND show_instance_id = " . db_escape($this->show_instance_id) . "
		GROUP BY resultant_class_id";
        }
        $return = array();
        foreach (db_query($global_conn, $query) as $row) {
            if ($row['reservation_id'] > 0) {
                $return[] = new reservation($row['reservation_id']);
            }
        }
        return $return;
    }

    public function willcall()
    {
        global $global_conn;
        $string = "<ul>";
        if ($this->seating_chart_id > 0) {
            //Reserved Seating
            $results = db_query($global_conn, "
		SELECT *, count(cart_item_id) as cnt FROM purchasable_seat_instance 
		LEFT JOIN cart_item ON (purchasable_seat_instance.purchasable_seat_instance_id = cart_item.purchasable_class_id)
		LEFT JOIN transaction USING (cart_id)
		LEFT JOIN users USING (user_id)
		WHERE seat_status = " . db_escape(purchasable_seat_instance::SEAT_STATUS_RESERVED) . " 
		    AND show_instance_id = " . db_escape($this->show_instance_id) . "
		    AND cart_item.purchasable_class = 'purchasable_seat_instance'
		 GROUP BY cart_id");

            foreach ($results as $row) {
                $string .= "\n<li class='reservation' reservation_id = '" . $row['resultant_class_id'] . "' purchasable_instance_id='" . $row['purchasable_seat_instance_id'] . "'>" . ucfirst($row['user_name_last']) . ", " . ucfirst($row['user_name_first']) . " (" . $row['cnt'] . ")</li>";
            }

        } else {
            //General Seating
            $this->get_purchasable_seating_general();
            $results = db_query($global_conn, "
		SELECT * FROM cart_item 
		LEFT JOIN reservation ON (cart_item.resultant_class_id = reservation.reservation_id)
		LEFT JOIN transaction USING (cart_id)
		LEFT JOIN users USING (user_id)
		WHERE cart_item.purchasable_class = 'purchasable_seating_general' 
		    AND cart_item.purchasable_class_id = " . db_escape($this->purchasable_seating_general->purchasable_seating_general_id) . "
		    AND reservation.reservation_status = 'ACTIVE'
		");
            foreach ($results as $row) {

                $string .= "\n<li reservation_id = '" . $row['resultant_class_id'] . "' purchasable_instance_id='" . $row['purchasable_class_id'] . "'>" . ucfirst($row['user_name_last']) . ", " . ucfirst($row['user_name_first']) . " (" . $row['quantity'] . ")</li>";
            }
        }
        $string .= "</ul>";
        return $string;
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        parent::get($this->show_id);
    }

    public function set()
    {
        $this->db_interface->set();
        if ($this->seating_chart_id > 0) {
            $this->seating_chart_html_update();
        }
        show_instance_cache::update($this->show_instance_id);
    }

    public function get_purchasable_seating_general()
    {
        if (intval($this->seating_chart_id) === 0) {
            $this->purchasable_seating_general = purchasable_seating_general::get_by_show_instance_id($this->show_instance_id);
            return $this->purchasable_seating_general;
        } else {
            return null;
        }
    }

    /**
     *
     * @param purchasable_seat_instance $seat_instance
     * @param user $user
     */
    public function get_price($seat_instance, $user)
    {
        if ($this->seating_chart_id) {
            //Reserved Seating
            return $seat_instance->get_price($user);
        } else {
            $this->purchasable_seating_general = purchasable_seating_general::get_by_show_instance_id($this->show_instance_id);
            return $this->purchasable_seating_general->get_price($user);
        }
    }

    public function get_prices_html()
    {
        global $global_conn;
        $string = "<div class='show-prices'><span class='prices-title'>Prices</span>";
        if ($this->show_seat_price_model === "show_seat_price_model_patron_type") {
            $string .= "<ul>";
            if ($this->seating_chart_id > 0) {
                $results = db_query($global_conn, "SELECT purchasable_seat_instance_id FROM purchasable_seat_instance WHERE show_instance_id = " . db_escape($this->show_instance_id) . " LIMIT 1;");
                $purchasable = new purchasable_seat_instance($results[0]['purchasable_seat_instance_id']);
            } else {
                $purchasable = purchasable_seating_general::get_by_show_instance_id($this->show_instance_id);
            }
            foreach (db_query($global_conn, "SELECT * FROM seat_price_by_general_seating_by_patron_type LEFT JOIN patron_types USING (patron_type_id) ORDER BY patron_type_id ASC") as $row) {
                $user = new user();
                $user->patron_type_id = $row['patron_type_id'];
                $price = show_seat_price_model_patron_type::get_price($user, $purchasable);
                $string .= "<li>" . $row['patron_type_label'] . " - $" . money($price) . ". " . $row['patron_type_description'] . "</li>";
            }
            $string .= "</ul>";
        } else {
            $string .= "<span class='error'>We don't know how to price this show</div>";
        }
        $string .= "</div>";
        return $string;
    }

    static public function get_cache($show_instance_id)
    {
        return show_instance_cache::get($show_instance_id);
    }


    public function get_quantity_total()
    {
        if ($this->seating_chart_id > 0) {
            //Reserved Seating
            $data = $this->seating_chart_instance_data();
            $count = count($data['seats']);
        } else {
            $this->get_purchasable_seating_general();
            $count = $this->purchasable_seating_general->purchasable_seating_general_quantity_total;
        }
        return $count;
    }

    public function get_quantity_available()
    {
        if ($this->seating_chart_id > 0) {
            //Reserved Seating
            $data = $this->seating_chart_instance_data();
            $count = 0;
            foreach ($data['seats'] as $seat) {
                if ($seat->seat_status === purchasable_seat_instance::SEAT_STATUS_AVAILABLE) {
                    $count++;
                }
            }
        } else {
            $this->get_purchasable_seating_general();
            $count = $this->purchasable_seating_general->get_quantity();
        }
        return $count;
    }

    public function get_quantity_reserved()
    {
        if ($this->seating_chart_id > 0) {
            $count = $this->get_quantity_total() - $this->get_quantity_available();
        } else {
            $count = $this->get_quantity_total() - $this->get_quantity_available();
        }
        return $count;
    }

    static public function get_datetime_of_last_transaction($show_instance_id)
    {
        global $global_conn;
        if ($this->seating_chart_id > 0) {
            $results = db_query($global_conn, "
		SELECT transaction.datetime FROM purchasable_seat_instance 
		    LEFT JOIN cart_item ON (cart_item.purchasable_class_id = purchasable_seat_instance.purchasable_seat_instance_id)
		    LEFT JOIN transaction USING (cart_id)
		WHERE purchasable_seat_instance.show_instance_id = " . db_escape($show_instance_id) . " 
		    AND cart_item.purchasable_class = 'purchasable_seat_instance'
		ORDER BY transaction.datetime DESC
		LIMIT 1
		");
        } else {
            $results = db_query($global_conn, "
		SELECT transaction.datetime FROM purchasable_seating_general 
		    LEFT JOIN cart_item ON (cart_item.purchasable_class_id = purchasable_seating_general.purchasable_seating_general_id)
		    LEFT JOIN transaction USING (cart_id)
		WHERE purchasable_seating_general.show_instance_id = " . db_escape($show_instance_id) . "
		    AND cart_item.purchasable_class = 'purchasable_seating_general'
		ORDER BY transaction.datetime DESC
		LIMIT 1");
        }
        if (count($results)) {
            return $results[0]['datetime'];
        } else {
            return false; //show instance has no transactions
        }
    }

    public function get_instance_status()
    {
        $enabled = $this->instance_sale_enabled;

        $window_open_blocked = false;
        if (boffice_property("shows_default_sales_window_open") !== '0') {
            $date_range = show::get_date_range($this->show_id);
            $time_open = strtotime("-" . boffice_property("shows_default_sales_window_open") . " hours", $date_range[0]);
            if ($time_open > time()) {
                $window_open_blocked = true;
            }
        }

        $window_closed_blocked = false;
        $time_close = strtotime("-" . boffice_property("shows_default_sales_window_close") . " minutes", strtotime($this->datetime));
        if ($time_close < time()) {
            $window_closed_blocked = true;
        }
        return $enabled AND (!$window_open_blocked) AND (!$window_closed_blocked);
    }

    public function get_instance_status_string()
    {
        if (!$this->instance_sale_enabled) {
            return boffice_property("shows_default_message_disabled");
        }
        if (strtotime($this->datetime) < time()) {
            return boffice_property("shows_default_message_past_show");
        }

        $threshold = max(0, intval(boffice_property("shows_default_soldout_threshold")));
        $seats_available = $this->get_quantity_available();
        if ($seats_available <= $threshold) {
            return boffice_property("shows_default_message_sold_out");
        }

        $time_close = strtotime("-" . boffice_property("shows_default_sales_window_close") . " minutes", strtotime($this->datetime));
        if ($time_close < time()) {
            return boffice_property("shows_default_message_onlines_sales_cutoff");
        }
        if (boffice_property("shows_default_sales_window_open") !== '0') {
            $date_range = show::get_date_range($this->show_id);
            $time_open = strtotime("-" . boffice_property("shows_default_sales_window_open") . " hours", $date_range[0]);
            if ($time_open > time()) {
                return "Sales for this show will start on " . date("M jS \a\\t g:ia", $time_open) . ".";
            }
        }
        return $seats_available . "seats available";
    }


    public function get_workers_all()
    {
        global $global_conn;
        $workers = array();
        foreach (db_query($global_conn, "SELECT * FROM show_instance_workers WHERE show_instance_id = " . db_escape($this->show_instance_id)) as $row) {
            $workers[] = new show_instance_worker($row['show_instance_worker_id']);
        }
        return $workers;
    }

    public function get_workers_unfilled()
    {
        $all = $this->get_workers_all();
        $unfilled_workers = array();
        foreach ($all as $worker) {
            if ($worker->show_instance_worker_status === show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_UNFILLED) {
                $unfilled_workers[] = $worker;
            }
        }
        return $unfilled_workers;
    }

    public function get_workers_filled()
    {
        $all = $this->get_workers_all();
        $filled_workers = array();
        foreach ($all as $worker) {
            if ($worker->show_instance_worker_status === show_instance_worker::SHOW_INSTANCE_WORKER_STATUS_FILLED) {
                $filled_workers[] = $worker;
            }
        }
        return $filled_workers;
    }

    public function get_workers_admin()
    {
        $string = "<ul class='workers'>";
        foreach ($this->get_workers_filled() as $worker) {
            $string .= "
		<li class='worker filled'><button show_instance_worker_id='" . $worker->show_instance_worker_id . "'>&nbsp;</button>" .
                $worker->show_instance_worker_type->show_instance_worker_type_name .
                " - " . $worker->user->user_name_first . " " . $worker->user->user_name_last .
                "</li>";
        }
        foreach ($this->get_workers_unfilled() as $worker) {
            $string .= "
		<li class='worker unfilled'><button show_instance_worker_id='" . $worker->show_instance_worker_id . "'>&nbsp;</button>" .
                $worker->show_instance_worker_type->show_instance_worker_type_name .
                "</li>";
        }
        $string .= "</ul>
	    <button class='add'>Create Job</button>
	    ";
        return $string;
    }
}
