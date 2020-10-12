<?php

/**
 * The core plugin class.
 *
 *
 * @link       https://tasdid.net
 * @since      1.4.0
 *
 * @package    Tasdid_Gateway
 * @subpackage Tasdid_Gateway/includes
 */

class Tasdid_Gateway
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.4.0
     * @access   protected
     * @var      Tasdid_Gateway_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin.
     *
     * Load the dependencies, define the locale, and set the hooks for website actions and filters
     * and register new routes for wordpress rest-apis and add new gateway for woocommerce
     * and add update checker for admin
     *
     * @since    1.4.0
     */

    public function __construct()
    {
        $this->load_dependencies();
        $this->set_locale();
        $this->set_filters();
        $this->set_actions();
        $this->register_routes();
        $this->set_admin_page();
        $this->add_woocommerce_gateway();
        $this->add_woocommerce_custom_fields();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Tasdid_Gateway_Loader. Orchestrates the hooks of the plugin.
     * - Tasdid_Gateway_i18n. Defines internationalization functionality.
     * - Tasdid_Gateway_Filters. Defines all hooks for the filters.
     * - Tasdid_Gateway_Actions. Defines all hooks for the actions.
     * - Tasdid_Gateway_WC. Defines woocommerce custom payment functionality.
     * - Tasdid_Gateway_Routes. Defines all hooks for the REST_APIS.
     * - Puc_v4_Factory. Defines plugin update functionality.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */

    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-loader.php';
        /**
         * The helper class that is used to do requests, validate token,
         * login.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-helper.php';

        /**
         * The class responsible adding for custom routes for wordpress REST APIS
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-routes.php';
        /**
         * The class responsible for adding custom filters to the website
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-filters.php';
        /**
         * The class responsible for adding custom actions to the website
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-actions.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-i18n.php';
        /**
         * The class responsible for defining the admin page functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tasdid-gateway-admin.php';
        /**
         * The class responsible for adding new payment gateway for woocommerce
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-woocommerce.php';
        /**
         * The class responsible for adding custom fields for woocommerce product
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tasdid-gateway-wc-fields.php';

        $this->loader = new Tasdid_Gateway_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the M_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Tasdid_Gateway_i18n();
        $plugin_i18n->load_plugin_textdomain();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }
    
    /**
     * Register All filters for the plugin
     * @since 1.0.0
     */
    private function set_filters()
    {
        $tasdid_filters = new Tasdid_Gateway_Filters();

        $this->loader->add_filter('woocommerce_order_number', $tasdid_filters, 'customize_woocommerce_order_number');
        $this->loader->add_filter('woocommerce_order_data_store_cpt_get_orders_query', $tasdid_filters, 'add_tasdid_meta_to_query', 10, 2);
        $this->loader->add_filter('manage_edit-shop_order_columns', $tasdid_filters, 'add_tasdid_bill_number_to_orders_table');

    }

    /**
     * Register All actions for the plugin
     * @since 1.0.0
     */
    private function set_actions()
    {
        $tasdid_actions = new Tasdid_Gateway_Actions();
        $this->loader->add_action('manage_shop_order_posts_custom_column', $tasdid_actions, 'show_tasdid_bill_number_in_orders_table');
    }

    /**
     * Register a new routes for wordpress rest-apis.
     *
     * @access   private
     * @since    1.0.0
     */
    private function register_routes()
    {
        $plugin_routes = new Tasdid_Gateway_Routes();

        $this->loader->add_action('rest_api_init', $plugin_routes, 'register_routes');
    }

    /**
     * Register a new payment gateway for woocommerce.
     * @access   private
     * @since    1.0.0
     */
    private function add_woocommerce_gateway()
    {
        add_filter('woocommerce_payment_gateways', function ($methods) {
            $methods[] = 'Tasdid_Gateway_WC';
            return $methods;
        });

    }

    /**
     * Register all custom fields for woocommerce product.
     * @access   private
     * @since    1.3.0
     */
    private function add_woocommerce_custom_fields()
    {
        $plugin_fields = new Tasdid_Gateway_WC_Fields();
        $this->loader->add_filter('woocommerce_product_data_tabs', $plugin_fields, 'add_tasdid_tab');
        $this->loader->add_action('woocommerce_product_data_panels', $plugin_fields, 'add_tasdid_tab_fields');
        $this->loader->add_action('woocommerce_process_product_meta', $plugin_fields, 'save_tasdid_fields_data');
    }

    /**
     * Register admin page for tasdid logs
     * @access private
     * @since 1.3.0
     */
    private function set_admin_page(){
        $plugin_admin = new Tasdid_Gateway_Admin();
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_menu_page_to_admin');
    }



    /**
     * Run the loader to execute all of the hooks with WordPress.
     * @since    1.4.0
     */
    public function run()
    {
        $this->loader->run();
    }
}
