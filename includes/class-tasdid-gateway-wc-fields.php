<?php
/**
 * add custom settings for tasdid gateway in product page
 *
 *
 * @link       https://tasdid.net
 * @since      1.2.0
 *
 * @package    Tasdid_Gateway
 * @subpackage Tasdid_Gateway/includes
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

class Tasdid_Gateway_WC_Fields
{
    /**
     * add tab to woocommerce section in product page
     *
     * @param array $tabs collection of woocommerce tabs
     * @return array
     * @since 1.2.0
     */
    public function add_tasdid_tab($tabs)
    {
        $tabs['tasdid-gateway'] = array(
            'label' => __('Tasdid Gateway', 'tasdid-gateway'), // The name of your panel
            'target' => 'tasdid_panel', // Will be used to create an anchor link so needs to be unique
            'class' => array('tasdid_tab', 'show_if_simple', 'show_if_variable'), // Class for your panel tab - helps hide/show depending on product type
            'priority' => 10, // Where your panel will appear. By default, 70 is last item
        );
        return $tabs;
    }

    /**
     * add custom fields to tasdid tab in product page
     *
     * @since 1.2.0
     */
    public function add_tasdid_tab_fields()
    { ?>
        <div id='tasdid_panel' class='panel woocommerce_options_panel'>
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id' => 'tasdid_serviceId',
                        'label' => __('Custom ServiceID', 'tasdid-gateway'),
                        'type' => 'text',
                    )
                );
                ?>
            </div>
        </div>
    <?php }

    /**
     * store tab fields data to post metadata
     *
     * @param string $post_id
     * @since 1.2.0
     */
    public function save_tasdid_fields_data($post_id)
    {
        $product = wc_get_product($post_id);
        $tasdid_serviceID = isset($_POST['tasdid_serviceId']) ? $_POST['tasdid_serviceId'] : '';
        $product->update_meta_data('tasdid_serviceId', sanitize_text_field($tasdid_serviceID));
        $product->save();
    }

}
