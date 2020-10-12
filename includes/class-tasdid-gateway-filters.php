<?php

class Tasdid_Gateway_Filters
{
    /**
     * add sequences to the order number
     * @param $order_id
     * @return string
     */
    public function customize_woocommerce_order_number($order_id)
    {
        return sprintf("%'.05d", $order_id);
    }

    /**
     * Handle a custom 'tasdid_order_number' query var to get orders with the 'tasdid_order_number' meta.
     * @param array $query - Args for WP_Query.
     * @param array $query_vars - Query vars from WC_Order_Query.
     * @return array modified $query
     */
    public function add_tasdid_meta_to_query($query, $query_vars)
    {
        if (!empty($query_vars['_ts_order'])) {
            $query['meta_query'][] = array(
                'key' => '_ts_order',
                'value' => esc_attr($query_vars['_ts_order']),
            );
        }

        return $query;
    }

    /**
     * add tasdid bill column to orders table
     * @param $columns
     * @return array modified columns
     */
    public function add_tasdid_bill_number_to_orders_table($columns)
    {
        $columns['tasdid_order'] = 'Tasdid bill';
        return $columns;
    }

}