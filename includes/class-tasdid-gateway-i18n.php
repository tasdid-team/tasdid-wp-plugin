<?php

/**
 * Define the internationalization functionality.
 *
 *
 * @link       https://tasdid.net
 * @since      1.4.0
 *
 * @package    Tasdid_Gateway
 * @subpackage Tasdid_Gateway/includes
 */

class Tasdid_Gateway_i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain( 'tasdid-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
    }


}
