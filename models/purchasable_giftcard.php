<?php

/**
 * Description of purchasable_giftcard
 *
 * @author lepercon
 */
class purchasable_giftcard extends purchasable
{


    public function __construct($id = null)
    {
        isset($id);
        parent::__construct();
    }

    public function get_price($user)
    {
        $user->user_id; //keeps NB from whinning
        die("Cannot call get_price on purchasable_giftcard. Price for giftcard does not exist.");
    }

    public function get_readible_name()
    {
        return "Giftcard";
    }

    public function react_with_items($items, $is_test = false)
    {
        isset($items); //NB whine prevention
        isset($is_test); //NB whine prevention
        return 0;
    }

    static public function generate_id()
    {
        return rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999);
    }

    static public function printable_id($id_string)
    {
        return substr($id_string, 0, 4) . " " . substr($id_string, 4, 4) . " " . substr($id_string, 8);
    }

    static public function generate_key()
    {
        /* Not using this, but keeping it for later
        $adjs = explode(" ", "aesthetic artisan ethical freegan glutenfree helvetica high next organic raw retro tattooed tall vegan viral denim bright dim evil final fine foxy groovy hip hot icky left main mellow ready ruff sad salty sharp solid square trilly brave affable bright calm kink neat nice quiet shy witty tidy tough romantic funny festive fiery fit fresh glad happy hearty jazzy jolly keen lavish magical natural neutral new novel noble optimal opulent paramount patient peak peppy pithy pliable plum poetic poised polite posh pro pretty prudent pure rad rapt rare real regal regnant right rich robust sacred safe sassy saucy select sleek smart skilled smooth snazzy snappy special spicy spiffy spotless sportive spry stainless stalwart striking sudious strurdy stylish suave sublime subtle succint suited supreme sunny super swell swift topical top viable bibrant vocal vogue vital vast volant versed vestal warm well wired wise worty youthful zany zesty zealous zestful");
        $nouns = explode(" ", "banksy austin bicycle brunch butcher cardigan craft cred diy hoodie iphone jean keytar legging photo portland quinoa seitan tofu vhs vice vinyl wolf apparel art bag beard beer bird blog booth cliche coffee irony life moon shorts squid sweater truck apple beat beef mustache food cat dog chick duke gate gravy grease gut ball home jam jelly lamp latch lily line queen man mess mezz mitt moo mouse nickel dime penny pad pigeon ride riff rug sky stache threads timber truck twister vine wren");
        $verbs = explode(" " , "listen watch place store master cleanse fund heard pack park level party sold dig drape fall focus frame fry hide jive kick knock lock mash murder nix pop rank rock send set signify slide stand take tick to to to to to adopt apply arrange control create derive design discard expect expose filter include measure modify obtain retain score show avoid affect exceed expand extend limit link model note seem shrink remove repeat restrict vary treat test track argue attain compel confirm deduce derive judge mirror prove refute remove solve verify yeild");
         */

        $characters = explode(" ", "A B C D E F G H J K L M N P R T U V W X Y Z");
        $str = "";
        for ($i = 0; $i < 4; $i++) {
            $str .= $characters[array_rand($characters)];
        }
        return $str;
    }


    static public function get_unprocess_usages($cart_id)
    {
        global $global_conn;
        $arr = array();
        foreach (db_query($global_conn, "SELECT * FROM giftcard_usage WHERE cart_id = " . db_escape($cart_id) . " AND transaction_id = 0") as $row) {
            $arr[] = $row;
        }
        return $arr;
    }

    static public function remove_unprocessed_usages($cart_id)
    {
        global $global_conn;
        db_exec($global_conn, "DELETE FROM giftcard_usage WHERE cart_id = " . db_escape($cart_id));
    }

    static public function create_unprocessed_usage($card_id, $amount, $cart_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM giftcard_usage WHERE 'purchasable_giftcard_instance_id' = " . db_escape($card_id) . " AND transaction_id = 0 AND cart_id = " . db_escape($cart_id));
        if (count($results) === 0) {
            db_exec($global_conn, build_insert_query($global_conn, 'giftcard_usage', array(
                'purchasable_giftcard_instance_id' => $card_id,
                'transaction_id' => '0',
                'giftcard_usage_amount' => $amount,
                'cart_id' => $cart_id
            )));
            return $global_conn->lastInsertId();
        } else {
            return $results[0]['giftcard_usage_id'];
        }
    }

    static public function actualize_unprocessed_usage($usage_id, $transaction_id)
    {
        global $global_conn;
        $results = db_query($global_conn, "SELECT * FROM giftcard_usage WHERE giftcard_usage_id = " . db_escape($usage_id));
        if (count($results) === 0 OR $results[0]['transaction_id'] !== '0') {
            new boffice_error("Could not deduct the giftcard amount from your giftcard.", true);
            prepend_log("giftcard usage did not actualize correctly - usage id = $usage_id. Transaction id should be $transaction_id");
            return false;
        } else {
            $exec_results = db_exec($global_conn, build_update_query($global_conn, 'giftcard_usage', array('transaction_id' => $transaction_id), " giftcard_usage_id = " . db_escape($usage_id)));
            if (intval($exec_results['rows_changed']) === 1) {
                return true;
            } else {
                return false;
            }
        }

    }
}
