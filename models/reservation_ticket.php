<?php

/**
 * Description of reservation_ticket
 *
 * @author lepercon
 */
class reservation_ticket
{
    public $reservation_ticket_id;
    public $purchasable_seat_instance_id; //either this
    public $purchasable_seating_general;  // or this
    public $reservation_ticket_robot_url;
    public $reservation_ticket_robot_barcode;
    public $reservation_id;
    public $reservation_ticket_created;
    public $reservation_ticket_updated;

    const RESERVATION_TICKET_STATUS_ACTIVE = "active";
    const RESERVATION_TICKET_STATUS_LOST = "lost";
    const RESERVATION_TICKET_STATUS_STOLEN = "stolen";
    const RESERVATION_TICKET_STATUS_CANCELLED = "cancelled";
    const RESERVATION_TICKET_STATUS_CHECKED_IN = "checked_in";
    public $reservation_ticket_status;

    private $db_interface;

    public function __construct($id = null)
    {
        $this->db_interface = new boffice_standard_interface($this, "reservation_ticket", "reservation_ticket", "reservation_ticket_id");
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

    public function check_in()
    {
        $this->reservation_ticket_status = reservation_ticket::RESERVATION_TICKET_STATUS_CHECKED_IN;
        $this->reservation_ticket_updated = date("Y-m-d H:i:s");
        $this->set();
    }


    static public function create($instance_id, $reservation_id)
    {
        $ticket = new reservation_ticket();
        $ticket->reservation_id = $reservation_id;
        $ticket->purchasable_seat_instance_id = $instance_id;
        $ticket->reservation_ticket_created = date("Y-m-d H:i:s");
        $ticket->reservation_ticket_status = reservation_ticket::RESERVATION_TICKET_STATUS_ACTIVE;

        $allowable = 'ABCDEFGHJKMNPQRSTUVWXYZ2345689';
        $ticket->reservation_ticket_robot_barcode = random_string(16, false, $allowable); //29^16 = 200,000,000,000,000,000,000 combinations
        $ticket->reservation_ticket_robot_url = random_string(255, false);          //36^255 = Overflow Error
        //29^16 * 36^255 = the number or url checks
        $ticket->set();
        return $ticket;
    }


    /**
     * Verify the three components of a ticket url are valid. We're not verifying the
     * ticket status as we kinda want stolen tickets to be reprinted and tried to be used
     * so we can catch/embarres dumb theives.
     * @global PDO $global_conn
     * @param string $robot_url
     * @param string $robot_id
     * @param string $seat_instance_id
     * @return bool
     */
    static public function verify_ticket_url($robot_url, $robot_barcode, $seat_instance_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "
	    SELECT reservation_ticket_id 
	    FROM reservation_ticket 
	    WHERE purchasable_seat_instance_id = " . db_escape($seat_instance_id) . " 
		AND reservation_ticket_robot_url = " . db_escape($robot_url) . "
		AND reservation_ticket_robot_barcode = " . db_escape($robot_barcode) . "
	    LIMIT 1;");
        return count($results) === 1;
    }

    /**
     * Create a reservation_ticket object from url variables. We should run verify beforehand.
     * @global PDO $global_conn
     * @param string $robot_barcode
     * @return \reservation_ticket
     */
    static public function get_ticket_from_barcode($robot_barcode)
    {
        global $global_conn;
        $results = db_query($global_conn, "
	    SELECT reservation_ticket_id 
	    FROM reservation_ticket 
	    WHERE reservation_ticket_robot_barcode = " . db_escape($robot_barcode) . "
	    LIMIT 1;");
        if (count($results) === 1) {
            return new reservation_ticket($results[0]['reservation_ticket_id']);
        } else {
            return null;
        }
    }

    /**
     * Create a reservation_ticket object from an instance id. Used to show a patron their ticket assuming the patron is logged in.
     * @global PDO $global_conn
     * @param int $instance_id
     * @return \reservation_ticket
     */
    static public function get_latest_valid_ticket_from_seat_instance_id($instance_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT reservation_ticket_id FROM reservation_ticket WHERE purchasable_seat_instance_id = " . db_escape($instance_id) . " AND reservation_ticket_status = " . db_escape(reservation_ticket::RESERVATION_TICKET_STATUS_ACTIVE) . " LIMIT 1;");
        if (count($results) === 1) {
            return new reservation_ticket($results[0]['reservation_ticket_id']);
        } else {
            return null;
        }
    }

    public function ticket_image_data()
    {
        global $site_physical_path;

        $instance = new purchasable_seat_instance($this->purchasable_seat_instance_id);

        $jpg_image = imagecreatefromjpeg($site_physical_path . '_resources/images/ticket_template.jpg');
        $imagesize = getimagesize($site_physical_path . '_resources/images/ticket_template.jpg');
        //$white = imagecolorallocate($jpg_image, 200, 200, 200);
        $black = imagecolorallocate($jpg_image, 30, 30, 30);
        $font = $site_physical_path . '_resources/external/CENTAUR.TTF';

        $y = 200;
        $x = 20;
        $size = 35;
        $lines = reservation_ticket::ticket_image_text_lines($instance->show_instance->title, $size, $font, $imagesize[0] - ($x * 2));
        foreach ($lines as $line) {
            $box = imagettftext($jpg_image, $size, 0, $x, $y, $black, $font, $line);
            $y += ($box[1] - $box[7]) + 10;
        }

        $y += 20;
        $datestr = date("M jS, g:ia", strtotime($instance->show_instance->datetime));
        $box = imagettfbbox(30, 0, $font, $datestr);
        imagettftext($jpg_image, 30, 0, ($imagesize[0] / 2) - ($box[2] / 2), $y, $black, $font, date("M jS, g:ia", strtotime($instance->show_instance->datetime)));
        $y += ($box[1] - $box[7]) + 20;

        $stage = new stage($instance->show_instance->stage_id);
        $cart_item = cart_item::get_cart_item_from_class_id("purchasable_seat_instance", $instance->purchasable_seat_instance_id);
        $transaction = transaction::transaction_for_cart($cart_item->cart_id);
        $user = new user($transaction->user_id);
        $text_block = "Stage: " . $stage->stage_name . " \n"
            . "Seat:" . $instance->get_readible_name(false) . "\n"
            . "Purchased: " . date("Y-m-d g:ia", strtotime($transaction->datetime)) . "\n"
            . "Purcahsed by: " . $user->user_name_last . ", " . $user->user_name_first;
        imagettftext($jpg_image, 20, 0, $x, $y, $black, $font, $text_block);

        $drawing = reservation_ticket::generate_barcode_image($this->reservation_ticket_robot_barcode);
        $barcode = $drawing->get_im();
        imagecopy($jpg_image, $barcode,
            $imagesize[0] / 2 - imagesx($barcode) / 2,    //x
            $imagesize[1] - 100,            //y
            0, 0,                    //src x,y
            imagesx($barcode),                //src w
            imagesy($barcode)                //src h
        );

        header('Content-type: image/jpeg');
        imagejpeg($jpg_image);
        imagedestroy($jpg_image);
        die();
    }

    static private function generate_barcode_image($text)
    {
        global $site_physical_path;
        require_once($site_physical_path . 'shared_functions/barcodes/class/BCGFontFile.php');
        require_once($site_physical_path . 'shared_functions/barcodes/class/BCGColor.php');
        require_once($site_physical_path . 'shared_functions/barcodes/class/BCGDrawing.php');
        require_once($site_physical_path . 'shared_functions/barcodes/class/JoinDraw.php');
        require_once($site_physical_path . 'shared_functions/barcodes/class/BCGcode128.barcode.php');

        $font = new BCGFontFile($site_physical_path . '_resources/external/cour.ttf', 12);
        $color_black = new BCGColor(0, 0, 0);
        $color_white = new BCGColor(255, 255, 255);

        $drawException = null;
        try {
            $code = new BCGcode128();
            $code->setScale(2); // Resolution
            $code->setThickness(30); // Thickness
            $code->setForegroundColor($color_black); // Color of bars
            $code->setBackgroundColor($color_white); // Color of spaces
            $code->setFont($font); // Font (or 0)
            $code->parse($text); // Text
        } catch (Exception $exception) {
            $drawException = $exception;
        }

        $drawing = new BCGDrawing('', $color_white); //empty parameter 1 means print to screen ...?;

        if ($drawException) {
            $drawing->drawException($drawException);
        } else {
            $drawing->setBarcode($code);
            $drawing->draw();
        }

        return $drawing;
    }

    static private function ticket_image_text_lines($string, $size, $font, $max_width)
    {
        $lines = array();
        $test_words = "";
        $words = explode(" ", $string);
        $line_num = 0;
        for ($i = 0; $i < count($words); $i++) {
            if (strlen(($test_words)) > 0) {
                $test_words .= " " . $words[$i];
            } else {
                $test_words = $words[$i];
            }

            $box = imagettfbbox($size, 0, $font, $test_words);
            if ($box[2] > $max_width) {
                $lines[$line_num] = substr($test_words, 0, strrpos($test_words, " "));
                $line_num++;
                $test_words = $words[$i];
            }
        }
        $lines[$line_num] = $test_words;
        return $lines;
    }

}
