<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tasdid.net
 * @since             1.0.0
 * @package           Tasdid_Gateway
 *
 * @wordpress-plugin
 * Plugin Name: Tasdid Gateway
 * Plugin URI: http://www.tasdid.net/plugins/wordpress
 * Description: Make your clients pay their orders with Qi-Card or MasterQi in your store
 * Author: Ebdaa
 * Author URI: http://ebdaa.tech
 * Version: 1.5.2
 * Text Domain: tasdid-gateway
 * Domain Path: /languages
 *
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('TASDID_BASE_URL', 'https://api.tasdid.net/v1/api');
define('PLUGIN_UPDATE_URI', 'https://ebdaa.tech/wp/tasdid.json');
define('TASDID_PLUGIN_SLUG', 'tasdid-gateway');

/**
 * The logger plugin for create log file for the plugin 
 */
require plugin_dir_path(__FILE__) . 'includes/libraries/php-logger/simple-php-logger.php';


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-tasdid-gateway.php';


/**
 * Update checker for plugin
 *
 * @since 1.0.0
 */
if (is_admin()) {
    if (!class_exists('Puc_v4_Factory')) {
        require_once plugin_dir_path(__FILE__) . 'includes/libraries/plugin-update-checker-4.9/plugin-update-checker.php';
    }
    $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
        'http://ebdaa.tech/wp/tasdid.json',
        __FILE__, //Full path to the main plugin file or functions.php.
        TASDID_PLUGIN_SLUG
    );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_tasdid_gateway()
{

    $plugin = new Tasdid_Gateway();
    $plugin->run();

}

add_filter('plugins_loaded', 'run_tasdid_gateway');
