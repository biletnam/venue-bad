<?php

/**
 * Description of purchasable_seat
 *
 * @author lepercon
 */
class purchasable_seat_abstract extends purchasable
{
    public $purchasable_id;

    public $purchasable_seat_abstract_id;
    public $purchasable_seat_abstract_name;
    public $purchasable_seat_abstract_icon_available_url;
    public $purchasable_seat_abstract_icon_unavailable_url;
    public $purchasable_seat_abstract_price_multiplier;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->item_type = purchasable::ITEM_TYPE_SEAT;
        $this->db_interface = new boffice_standard_interface($this, 'purchasable_seat_abstract', 'purchasable_seat_abstract', 'purchasable_seat_abstract_id');
        parent::__construct();
        if ($id !== null) {
            $this->get($id);
        }
    }

    public function get($id)
    {
        $this->db_interface->get($id);
        $this->purchasable_id = 1;
    }

    public function get_parent_explicitly()
    {
        parent::get($this->purchasable_id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get_price($user)
    {
        $user->user_id;
        new boffice_error('cannot determine price without knowing the show_instance_id');
        return false;
    }

    public function admin_edit_form()
    {
        global $global_conn;
        $elements = array(
            new f_data_element('Name', 'purchasable_seat_abstract_name', 'text'),
            new f_data_element('Available Icon', 'purchasable_seat_abstract_icon_available_url', 'file'),
            new f_data_element('Unavailable Icon', 'purchasable_seat_abstract_icon_unavailable_url', 'file'),
            new f_data_element('Price Multiplier', 'purchasable_seat_abstract_price_multiplier', 'text'),
        );
        if ($this->purchasable_seat_abstract_id !== null AND $this->purchasable_seat_abstract_id > 0) {
            $f = new f_data($global_conn, 'purchasable_seat_abstract', 'purchasable_seat_abstract_id', $elements, $this->purchasable_seat_abstract_id);
        } else {
            $f = new f_data($global_conn, 'purchasable_seat_abstract', 'purchasable_seat_abstract_id', $elements, false);
        }
        $string = $f->start();
        $this->generate_price_by_patron_types();
        //Patron Seat Multiplier Form
        foreach (db_query($global_conn, "SELECT * FROM patron_types LEFT JOIN seat_price_by_patron_type USING (patron_type_id) WHERE seat_price_by_patron_type.purchasable_seat_abstract_id = " . db_escape($this->purchasable_seat_abstract_id) . " ORDER BY patron_type_label ASC") as $type) {
            if (filter_input(INPUT_POST, 'patron_type_id_' . $type['patron_type_id']) !== null) {
                db_exec($global_conn, build_update_query($global_conn, 'seat_price_by_patron_type', array('price_multiplier' => filter_input(INPUT_POST, 'price_multiplier')), "patron_type_id = " . db_escape($type['patron_type_id']) . " AND purchasable_seat_abstract_id = " . db_escape($this->purchasable_seat_abstract_id)));
                $string .= "<div> Updated </div>";
            } else {
                $string .= "<form method='POST'>
		    <input type='hidden' name='patron_type_id_" . $type['patron_type_id'] . "' value='1' />
		    <div class='form-row'>
			<label for='price_multiplier'>Multiplier for " . $type['patron_type_label'] . "</label><input type='text' name='price_multiplier' value='" . $type['price_multiplier'] . "' />
			<button type='submit'>update</button>
		   </div>
		</form>";
            }
        }
        return $string;
    }

    private function generate_price_by_patron_types()
    {
        global $global_conn;
        $patron_types = db_query($global_conn, "SELECT * FROM patron_types");
        $seat_prices = db_query($global_conn, "SELECT * FROM seat_price_by_patron_type WHERE purchasable_seat_abstract_id = " . db_escape($this->purchasable_seat_abstract_id));
        if (count($patron_types) <= count($seat_prices)) {
            return true;
        }

        foreach ($patron_types as $type) {
            $results = db_query($global_conn, "SELECT * FROM seat_price_by_patron_type WHERE patron_type_id = " . db_escape($type['patron_type_id']) . " AND purchasable_seat_abstract_id = " . db_escape($this->purchasable_seat_abstract_id));
            if (count($results) === 0) {
                db_exec($global_conn, "INSERT INTO seat_price_by_patron_type SET patron_type_id = " . db_escape($type['patron_type_id']) . ", purchasable_seat_abstract_id = " . db_escape($this->purchasable_seat_abstract_id));
            }
        }
    }

    public function admin_list($href)
    {
        global $global_conn;
        $string = "<ul class='admin-list'><li><a href='$href'>New seat type</a></li>";
        foreach (db_query($global_conn, "SELECT * FROM purchasable_seat_abstract ORDER BY purchasable_seat_abstract_name ASC") as $item) {
            $string .= "<li><a href='" . $href . "?row_id=" . $item['purchasable_seat_abstract_id'] . "'>" . $item['purchasable_seat_abstract_name'] . "</a></li>";
        }
        return $string . "</ul>";
    }

    public function get_readible_name()
    {
        return "Abstract Seat";
    }
}
