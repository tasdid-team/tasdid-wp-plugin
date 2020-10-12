<?php

class Tasdid_Gateway_Actions
{
    /**
     * add the value of tasdid bill column
     * @param $column
     */
    public function show_tasdid_bill_number_in_orders_table($column)
    {

        global $post;

        if ('tasdid_order' === $column) {

            echo get_post_meta($post->ID, "_ts_order", true);

        }
    }
}
