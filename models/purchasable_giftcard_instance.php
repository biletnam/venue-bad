<?php

/**
 * Description of purchasable_giftcard_instance
 *
 * @author lepercon
 */
class purchasable_giftcard_instance extends purchasable_giftcard
{
    public $purchasable_giftcard_instance_id;
    public $purchasable_giftcard_instance_starting_value;
    public $purchasable_giftcard_instance_human_id;
    public $purchasable_giftcard_instance_human_key;
    public $purchasable_giftcard_instance_robot_url;
    public $purchasable_giftcard_instance_to;
    public $purchasable_giftcard_instance_from;
    public $purchasable_giftcard_instance_send_method;
    public $purchasable_giftcard_instance_send_data;
    public $purchasable_giftcard_instance_created;
    public $purchasable_giftcard_instance_activated;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "purchasable_giftcard_instance", "purchasable_giftcard_instance", "purchasable_giftcard_instance_id");
        if ($id !== null) {
            $this->get($id);
        }
        parent::__construct(null);
    }

    public function get($id)
    {
        $this->db_interface->get($id);
    }

    public function set()
    {
        $this->db_interface->set();
    }

    public function get_price($user)
    {
        isset($user);
        return $this->purchasable_giftcard_instance_starting_value;
    }

    public function get_quantity()
    {
        return 1;
    }

    public function get_used_value()
    {
        global $global_conn;
        $amount = 0;
        foreach (db_query($global_conn, "SELECT * FROM giftcard_usage WHERE purchasable_giftcard_instance_id = " . db_escape($this->purchasable_giftcard_instance_id) . " AND transaction_id > 0") as $row) {
            $amount += $row['giftcard_usage_amount'];
        }
        return $amount;
    }

    public function get_remaining_value()
    {
        return $this->purchasable_giftcard_instance_starting_value - $this->get_used_value();
    }

    public function create_usage($transaction_id, $amount)
    {
        global $global_conn;
        $results = db_exec($global_conn, build_insert_query($global_conn, "giftcard_usage", array(
            'purchasable_giftcard_instance_id' => $this->purchasable_giftcard_instance_id,
            'transaction_id' => $transaction_id,
            'giftcard_usage_amount' => $amount
        )));
        return $results['rows_changed'] > 0;
    }

    static public function is_valid($raw_id, $key)
    {
        global $global_conn;
        $id = str_replace(array(" ", "-", ",", ";", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"), "", $raw_id);
        $results = db_query($global_conn, "SELECT * FROM purchasable_giftcard_instance WHERE purchasable_giftcard_instance_human_id = " . db_escape($id) . " AND purchasable_giftcard_instance_human_key = " . db_escape(strtoupper($key)));
        if (count($results) === 1) {
            return true;
        } else {
            return false;
        }
    }

    static public function get_card_by_id($id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM purchasable_giftcard_instance WHERE purchasable_giftcard_instance_human_id = " . db_escape($id));
        if (count($results) === 1) {
            return new purchasable_giftcard_instance($results[0]['purchasable_giftcard_instance_id']);
        } else {
            return null;
        }
    }


    /** Cart things */
    public function react_with_items($items, $is_test = false)
    {
        isset($items);
        isset($is_test);
        return false;
    }

    /**
     * Validate the card
     * @param array $items not used
     * @param \cart_item $cart_item
     * @param int $transaction_id
     * @return boolean
     */
    public function do_process($items, $cart_item, $transaction_id = 0)
    {
        isset($items);
        if ($transaction_id > 0 AND intval($cart_item->resultant_class_id) === 0) {
            //create new
            $this->purchasable_giftcard_instance_activated = '1';
            $this->purchasable_giftcard_instance_human_id = purchasable_giftcard::generate_id();
            $this->purchasable_giftcard_instance_human_key = purchasable_giftcard::generate_key();
            $this->purchasable_giftcard_instance_robot_url = random_string(128);
            $this->set();
            $cart_item->resultant_class_id = $this->purchasable_giftcard_instance_id;
            $cart_item->set_cart_item();
        }
        return true;
    }

    public function image_data()
    {
        global $site_physical_path;
        header('Content-type: image/jpeg');
        $jpg_image = imagecreatefromjpeg($site_physical_path . '_resources/images/certficate_template.jpg');
        $white = imagecolorallocate($jpg_image, 200, 200, 200);
        $black = imagecolorallocate($jpg_image, 30, 30, 30);
        $font = $site_physical_path . '_resources/external/CENTAUR.TTF';
        $curior = $site_physical_path . '_resources/external/cour.ttf';

        $text1 = purchasable_giftcard::printable_id($this->purchasable_giftcard_instance_human_id) . "\n       " . $this->purchasable_giftcard_instance_human_key;
        imagettftext($jpg_image, 24, 0, 40, 320, $black, $curior, $text1);
        imagettftext($jpg_image, 20, 0, 130, 360, $black, $font, "key: ");


        $text_amount = "$" . money($this->purchasable_giftcard_instance_starting_value);
        imagettftext($jpg_image, 55, 0, 50, 240, $black, $font, $text_amount);

        $text_to = "";
        if ($this->purchasable_giftcard_instance_to !== "") {
            $text_to .= "To: \n" . $this->purchasable_giftcard_instance_to . "\n\n";
        }
        if ($this->purchasable_giftcard_instance_from !== "") {
            $text_to .= "From: \n" . $this->purchasable_giftcard_instance_from;
        }
        imagettftext($jpg_image, 20, 0, 340, 200, $white, $font, $text_to);

        imagejpeg($jpg_image);
        imagedestroy($jpg_image);
        die();
    }
}
