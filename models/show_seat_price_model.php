<?php

/**
 * Description of show_seat_price_model
 *
 * @author lepercon
 */
interface show_seat_price_model
{
    /**
     * Calculate and return the sticker price of the model (excluding cart_item collisions) based on the pricing model
     * @param user $user
     * @param show_instance $show_instance
     * @param purchasable $purchasable
     * @param cart_item $cart_item
     */
    static public function get_price($user, $purchasable, $cart_item = null);

    static public function price_varies_by_show_instance();
}
