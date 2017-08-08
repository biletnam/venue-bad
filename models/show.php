<?php

class show
{
    public $show_id;
    public $title;
    public $url_name;
    public $description;
    public $cover_image_url;
    public $seating_chart_id;
    public $seating_chart;
    public $seating_chart_general_count; //for general seating
    public $stage_id;
    public $show_base_price;

    /**
     * @var string Name of the class that implements show_seat_price_model.
     * Vary reserved seating by patron type: show_seat_price_model_patron_type
     * Vary general  seating by patron type: show_seat_price_model_general_seating_patron_type
     */
    public $show_seat_price_model;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, 'show', 'shows', 'show_id');
        $this->db_interface->class_property_exclusions = array('seating_chart', 'stage', 'show_display_state');
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

    /**
     * @return \seating_chart
     */
    public function seating_chart()
    {
        if ($this->seating_chart === null AND $this->seating_chart_id > 0) {
            $this->seating_chart = new seating_chart($this->seating_chart_id);
        }
        return $this->seating_chart;
    }

    /**
     * Instanciate a new showtime at a new $datetime
     * @global PDO $global_conn
     * @param string $datetime in the form YYYY-mm-dd HH:ii:ss
     * @return \show_instance
     */
    public function create_instance($datetime)
    {
        global $global_conn;
        if (!$this->show_id) {
            new boffice_error("Cannot create new instances on a show that is null. Run show->get first", false);
        }

        $new_show_instance = new show_instance();
        $new_show_instance->datetime = $datetime;
        $new_show_instance->instance_sale_status = boffice_property("shows_default_sales_status");
        $new_show_instance->show_id = $this->show_id;
        $new_show_instance->set();
        $this->seating_chart();

        if ($this->seating_chart_id > 0) {
            //Reserved Seating
            foreach ($this->seating_chart->seats as $seat) {
                db_exec($global_conn, "INSERT INTO purchasable_seat_instance SET 
		    purchasable_seat_id = " . db_escape($seat->purchasable_seat_id) . ", 
		    show_instance_id = " . db_escape($new_show_instance->show_instance_id) . ", 
		    seat_status = 'SEAT_STATUS_AVAILABLE' "
                );
            }
        } else {
            //General Seating
            $new_general_seating = new purchasable_seating_general();
            $new_general_seating->show_instance = $new_show_instance;
            $new_general_seating->show_instance_id = $new_show_instance->show_instance_id;
            $new_general_seating->purchasable_seating_general_quantity_total = $this->seating_chart_general_count;
            $new_general_seating->purchasable_seating_general_status = purchasable::ITEM_STATUS_AVAILABLE;
            $new_general_seating->set();
        }

        show_instance_cache::get($new_show_instance->show_instance_id);
        return $new_show_instance;
    }

    public function admin_instances_list($href = '')
    {
        $string = "<ul class='admin-instances-list'>";
        foreach ($this->get_instances($this->show_id) as $instance) {
            $string .= "<li><a href='$href?instance_id=" . $instance->show_instance_id . "' show_instance_id='" . $instance->show_instance_id . "'>" . date("M jS - g:ia", strtotime($instance->datetime)) . "</a></li>";
        }
        return $string .= "</ul>";
    }

    public function admin_edit_form()
    {
        global $global_conn;

        $elements = array(
            new f_data_element('Name', 'title', 'text'),
            new f_data_element('Base Price', 'show_base_price', 'text'),
            new f_data_element('Description', 'description', 'wysiwyg'),
            new f_data_element('cover_image_url', 'cover_image_url', 'file'),
            'max_seats' => new f_data_element('Maximum Seats', 'seating_chart_general_count', 'text', '', '0', '', false, 'For general seating only'),
            new f_data_element('Pricing', 'show_seat_price_model', 'select', array(
                    'show_seat_price_model_patron_type' => 'Patron type determines price',
                )
            ),
        );

        $seating_chart_options = array('0' => 'General Seating (no seating chart)');
        foreach (db_query($global_conn, "SELECT * FROM seating_chart") as $item) {
            $seating_chart_options[$item['seating_chart_id']] = $item['seating_chart_name_internal'];
        }
        $elements[] = new f_data_element('SeatingChartId', 'seating_chart_id', 'select', $seating_chart_options);

        $stage_options = array();
        foreach (db_query($global_conn, "SELECT * FROM stage") as $item) {
            $stage_options[$item['stage_id']] = $item['stage_name'];
        }
        $elements[] = new f_data_element('StageId', 'stage_id', 'select', $stage_options);

        $f_data = new f_data($global_conn, 'shows', 'show_id', $elements, $this->show_id);
        $f_data->on_success_action = f_data::F_DATA_ON_SUCCESS_UPDATE_FORM;
        if (intval($this->seating_chart_id) === 0) {
            $f_data->hook_postupdate = "update_max_seats";
        }

        function update_max_seats($e)
        {
            $seat_count_element = $e->elements['max_seats'];
            $new_seat_count = $seat_count_element->get_user_value(INPUT_POST);
            foreach (show::get_instances($e->row_id, true) as $instance) {
                $purchasable_seating_general = $instance->get_purchasable_seating_general();
                $purchasable_seating_general->purchasable_seating_general_quantity_total = $new_seat_count;
                $purchasable_seating_general->set();
            }
        }

        $f_data->hook_postprocess = "capture_row_id";
        $f_data->hook_postinsert = "create_url_name";
        $capture_row_id_on_insert = 0;
        function capture_row_id($e)
        {
            global $capture_row_id_on_insert;
            $capture_row_id_on_insert = $e->conn->lastInsertId();
        }

        function create_url_name($e)
        {
            global $capture_row_id_on_insert, $global_conn;
            $show = new show($capture_row_id_on_insert);
            $url_safe_title = $string = boffice_classy($show->title);
            db_exec($global_conn, "UPDATE shows SET url_name = " . db_escape($url_safe_title) . " WHERE show_id = " . db_escape($capture_row_id_on_insert));
        }

        return $f_data->start();
    }

    /**
     * This is a limited version of admin_edit_form for boxoffice staffers
     * @global PDO $global_conn
     * @return string
     */
    public function admin_edit_form_boxoffice()
    {
        global $global_conn;
        $elements = array(
            new f_data_element('Base Price', 'show_base_price', 'text'),
            'max_seats' => new f_data_element('Maximum Seats', 'seating_chart_general_count', 'text', '', '0', '', false, 'For general seating only'),
            new f_data_element('Pricing', 'show_seat_price_model', 'select', array(
                    'show_seat_price_model_patron_type' => 'Patron type determines price',
                )
            ),
        );

        $seating_chart_options = array('0' => 'General Seating (no seating chart)');
        foreach (db_query($global_conn, "SELECT * FROM seating_chart") as $item) {
            $seating_chart_options[$item['seating_chart_id']] = $item['seating_chart_name_internal'];
        }
        $elements[] = new f_data_element('SeatingChartId', 'seating_chart_id', 'select', $seating_chart_options);

        $stage_options = array();
        foreach (db_query($global_conn, "SELECT * FROM stage") as $item) {
            $stage_options[$item['stage_id']] = $item['stage_name'];
        }
        $elements[] = new f_data_element('StageId', 'stage_id', 'select', $stage_options);

        $f_data = new f_data($global_conn, 'shows', 'show_id', $elements, $this->show_id);
        $f_data->on_success_action = f_data::F_DATA_ON_SUCCESS_UPDATE_FORM;
        if (intval($this->seating_chart_id) === 0) {
            $f_data->hook_postupdate = "update_max_seats_boxoffice";
        }

        function update_max_seats_boxoffice($e)
        {
            $seat_count_element = $e->elements['max_seats'];
            $new_seat_count = $seat_count_element->get_user_value(INPUT_POST);
            foreach (show::get_instances($e->row_id, true) as $instance) {
                $purchasable_seating_general = $instance->get_purchasable_seating_general();
                $purchasable_seating_general->purchasable_seating_general_quantity_total = $new_seat_count;
                $purchasable_seating_general->set();
            }
        }

        return $f_data->start();
    }


    public function admin_create_instance($href = '')
    {
        boffice_html::$standard_datetime_picker = true;
        if (filter_input(INPUT_POST, 'new_datetime') !== null) {
            $this->create_instance(date('Y-m-d H:i:s', strtotime(filter_input(INPUT_POST, 'new_datetime'))));
        }
        boffice_html::$js_internal .= "$(document).ready(function () { $('#new_datetime').datetimepicker({step:30, formatTime:'g:ia'}); });";
        return "<form action='$href' method='POST'><label for='new_dateteime'>New ShowTime</label><input type='text' id='new_datetime' name='new_datetime' ><button type='SUBMIT'>Create</button></form>";
    }

    static public function admin_list_shows($href = '')
    {
        global $global_conn;

        $results = db_query($global_conn, "SELECT * FROM shows ORDER BY title ASC");
        $string = "<ul class='admin-list'>
	    <li><a show_id='0' href='$href'>New Show</a></li>";
        foreach ($results as $item) {
            $date_range = show::get_date_range($item['show_id']);
            $string .= "<li><span class='date'>" . date("Y-m-d", $date_range[0]) . "</span> <span class='title'><a show_id='" . $item['show_id'] . "' href='$href?show_id=" . $item['show_id'] . "'>" . $item['title'] . "</a></span></li>";
        }
        return $string . "</ul>";
    }

    /**
     * @global PDO $global_conn
     * @param string $url_name
     * @return show
     */
    static public function get_show_by_url_name($url_name)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM shows WHERE url_name = " . db_escape($url_name));
        if (count($results)) {
            return new show($results[0]['show_id']);
        } else {
            return null;
        }
    }

    static public function get_instances($show_id, $past_shows = true, $limit = 0)
    {
        global $global_conn;
        $extra_where = "";
        if (!$past_shows) {
            $now = date("Y-m-d H:i:s", strtotime("-1 hour"));
            $extra_where .= " AND datetime > '$now' ";
        }
        $limit_q = "";
        if ($limit > 0) {
            $limit_q = " LIMIT " . intval($limit) . " ";
        }
        $instances = array();
        foreach (db_query($global_conn, "SELECT show_instance_id FROM show_instance WHERE show_id = " . db_escape($show_id) . " $extra_where ORDER BY datetime ASC $limit_q ") as $item) {
            $instances[] = new show_instance($item['show_instance_id']);
        }
        return $instances;
    }

    static public function get_date_range($show_id)
    {
        $instances = show::get_instances($show_id);
        if (count($instances) > 1) {
            $date_start = strtotime($instances[0]->datetime);
            $date_end = strtotime($instances[count($instances) - 1]->datetime);
        } else if (count($instances) === 1) {
            $date_start = strtotime($instances[0]->datetime);
            $date_end = strtotime($instances[0]->datetime);
        } else {
            new boffice_error("Cannot draw showtimes for show without instances", false);
        }
        return array($date_start, $date_end);
    }

    public function display_feature($h = 2)
    {
        global $site_domain, $site_path;
        $date_range = show::get_date_range($this->show_id);
        $string = "
	    <div class='show feature'>
		<div class='background' style='background-image:url(" . $this->cover_image_url . ");'></div>
		
		<h$h>" . $this->title . "</h$h>
		<div class='description'>
		    <p class='date-range'>" . date("M jS", $date_range[0]) . " - " . date("M jS, Y", $date_range[1]) . "</p>
		    " . $this->description;
        $show_people = show_people::get_all_for_show($this->show_id);
        if (count($show_people) > 0) {
            $string .= "<div class='user-roles'><ul>";
            foreach ($show_people as $show_person) {
                $user = new user($show_person->user_id);
                $string .= "<li><a href='//" . $site_domain . $site_path . "person/" . $user->user_id . "'>" . $user->user_name_first . " " . $user->user_name_last . "</a> (" . $show_person->show_people_role . ")</li>";
            }
            $string .= "</ul></div>";
        }
        $string .= "
		</div>
		
	    </div>
	";
        return $string;
    }

    public function get_url()
    {
        global $site_path;
        return $site_path . "show/" . $this->url_name;
    }

    public function show_times()
    {
        $string = "<ul class='show-times'>";
        foreach ($this->get_instances($this->show_id, false) as $show) {
            if ($show->get_instance_status()) {
                $string .= "<li><a href='" . $this->get_url() . "/" . $show->show_instance_id . "'>" . date("M jS, g:ia", strtotime($show->datetime)) . "</a> " . $show->get_instance_status_string() . "</li>";
            } else {
                $string .= "<li>" . date("M jS, g:ia", strtotime($show->datetime)) . " - " . $show->get_instance_status_string() . "</li>";
            }

        }
        $string .= "</u>";
        return $string;
    }

    public function schema()
    {
        //scheme.org
        $string = '
	    <script type="application/ld+json">
	    [{
		"@context" : "http://schema.org",
		"@type" : "MusicEvent",
		"name" : "B.B. King with Jonathon \"Boogie\" Long",
		"image" : "http://www.bbking.com/gallery/b-b-king-live.jpg",
		"url" : "http://www.bbking.com/events/apr12-providence.html",
		"startDate" : "2014-04-12T19:30",
		"doorTime" : "18:30",
		"endDate" : "2014-04-12T22:00",
		"location" : {
		    "@type" : "Place",
		    "name" : "Lupos Heartbreak Hotel",
		    "sameAs" : "http://lupos.com/",
		    "address" : {
			"@type" : "PostalAddress",
			"streetAddress" : "79 Washington St.",
			"addressLocality" : "Providence",
			"addressRegion" : "RI",
			"postalCode" : "02903",
			"addressCountry" : "US"
		    }
		},
		"offers" : [ 
		    {
			"@type" : "Offer",
			"name" : "General Admission",
			"price" : "$63.25",
			"availability" : "SoldOut",
			"url" : "http://www.ticketmaster.com/event/17004C29"
		    },{
			"@type" : "Offer",
			"name" : "VIP Experience",
			"url" : "http://www.example.com/Abcde12345",
			"price" : "$299.00",
			"validFrom" : "2014-02-05T10:00",
			"validThrough" : "2014-03-19T23:59"
		    } 
		],
		"performer" : [ 
		    {
			"@type" : "MusicGroup",
			"name" : "B.B. King",
			"sameAs" : "http://en.wikipedia.org/wiki/B.B._King"
		    },{
			"@type" : "MusicGroup",
			"name" : "Jonathon \"Boogie\" Long",
			"sameAs" : "http://jonathonboogielong.com/"
		    } 
		],
		"typicalAgeRange" : "18+"
	    }]
	    </script>
	';
        return $string;
    }

    /**
     * Get the show id for the show with the next showtime
     * @global PDO $global_conn
     * @param int $stage_id
     * @return show The show, null for no upcoming show
     */
    static public function get_current_show($stage_id = false)
    {
        global $global_conn;
        $where = "";
        if ($stage_id) {
            $where .= " AND stage_id = " . db_escape($stage_id);
        }
        $now = date("Y-m-d H:i:s", strtotime("-1 hour"));
        $results = db_query($global_conn, "SELECT * FROM show_instance LEFT JOIN shows USING (show_id) WHERE datetime > '$now' $where ORDER BY datetime ASC LIMIT 1");
        if ($results) {
            return new show(intval($results[0]['show_id']));
        } else {
            return null;
        }
    }

    /**
     * Get the show_instance for the show with the next showtime
     * @global PDO $global_conn
     * @param int $stage_id
     * @return show_instance null for no upcoming show
     */
    static public function get_current_show_instance($stage_id = false)
    {
        global $global_conn;
        $where = "";
        if ($stage_id) {
            $where .= " AND stage_id = " . db_escape($stage_id);
        }
        $now = date("Y-m-d H:i:s", strtotime("-1 hour"));
        $results = db_query($global_conn, "SELECT * FROM show_instance LEFT JOIN shows USING (show_id) WHERE datetime > '$now' $where ORDER BY datetime ASC LIMIT 1");
        if ($results) {
            return new show_instance(intval($results[0]['show_instance_id']));
        } else {
            return null;
        }
    }

    static public function get_upcoming_shows($count = 5, $stage_id = false)
    {
        global $global_conn;
        $where = "show_instance.datetime > NOW()";
        if ($stage_id) {
            $where .= " AND stage_id = " . db_escape($stage_id);
        }
        $query = "
	    SELECT * FROM shows
	    LEFT JOIN show_instance USING (show_id)
	    WHERE $where
	    GROUP BY show_id
	    ORDER BY show_instance.datetime ASC
	    LIMIT $count
	";
        $return = array();
        foreach (db_query($global_conn, $query) as $row) {
            $return[] = new show($row['show_id']);
        }
        return $return;
    }
}
