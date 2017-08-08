<?php

/**
 * This is the price model for general seating that reflects seat prices based only on patron type
 *
 * @author lepercon
 */
class show_seat_price_model_patron_type implements show_seat_price_model
{
    /**
     * Calculate and return the sticker price of the model (excluding cart_item collisions) based on the pricing model
     * @param user $user
     * @param show_instance $show_instance
     * @param purchasable $purchasable_seat_instance
     * @param cart_item $cart_item
     */
    public static function get_price($user, $purchasable, $cart_item = null)
    {
        global $global_conn;

        $patron_type_id = 0;
        if ($user !== null) {
            $patron_type_id = $user->patron_type_id;
        }
        if ($cart_item !== null) {
            if ($cart_item->cart_item_priced_as_patron_type_id > 0) {
                $patron_type_id = $cart_item->cart_item_priced_as_patron_type_id;
            }
        }

        if ($purchasable instanceof purchasable_seat_instance) {
            $price = $purchasable->show_instance->show_base_price * $purchasable->purchasable_seat_abstract_price_multiplier;
            $seat_price_by_patron = db_query($global_conn, "
		SELECT * 
		FROM seat_price_by_patron_type 
		WHERE 
		    patron_type_id = " . db_escape($patron_type_id) . " 
		    AND purchasable_seat_abstract_id = " . db_escape($purchasable->purchasable_seat_abstract_id) . " 
	    ");
            if (count($seat_price_by_patron)) {
                $price *= $seat_price_by_patron[0]['price_multiplier'];
            }
        } else if ($purchasable instanceof purchasable_seating_general) {
            $price = $purchasable->show_instance->show_base_price;
            $seat_price_by_patron = db_query($global_conn, "
		SELECT price_multiplier 
		FROM seat_price_by_general_seating_by_patron_type 
		WHERE 
		    patron_type_id = " . db_escape($patron_type_id) . " 
		    AND purchasable_seating_general_id = " . db_escape($purchasable->purchasable_seating_general_id) . " 
	    ");
            if (count($seat_price_by_patron)) {
                $price *= $seat_price_by_patron[0]['price_multiplier'];
            }
        }
        return $price;
    }


    static public function price_varies_by_show_instance()
    {
        return false;
    }
}
