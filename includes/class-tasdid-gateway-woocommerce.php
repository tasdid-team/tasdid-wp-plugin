<?php
/**
 * add tasdid gateway to woocommerce
 *
 *
 * @link       https://tasdid.net
 * @since      1.2.0
 *
 * @package    Tasdid_Gateway
 * @subpackage Tasdid_Gateway/includes
 */

defined('ABSPATH') || exit;

class Tasdid_Gateway_WC extends WC_Payment_Gateway
{
    /**
     * Tasdid username
     *
     * @since 1.0.0
     * @var string
     */
    private $username;
    /**
     * Tasdid password
     *
     * @since 1.0.0
     * @var string
     */
    private $password;
    /**
     * Tasdid JWT
     *
     * @since 1.0.0
     * @var string
     */
    private $token;
    /**
     * is store using dollar currency
     *
     * @since 1.4.0
     * @var boolean
     */
    private $isDollar;
    /**
     * The exchange rate of the dollar against the dinar
     * @since 1.4.0
     * @var string
     */
    private $currency_price;
    /**
     * Tasdid service id
     *
     * @since 1.0.0
     * @var string
     */
    private $service_id;

    /**
     * Define the core functionality of the plugin and add tasdid to woocommerce gateways.
     *.
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->id = 'tasdid-gateway';
        $this->method_title = __('Tasdid Gateway', 'tasdid-gateway');
        $this->method_description = __('Have your customers pay with Qi-Card or MasterQi with Tasdid Platform.', 'tasdid-gateway');
        $this->supports = array(
            'products',
        );

        // Load form fields
        $this->init_form_fields();
        // Load the settings.
        $this->init_settings();
        // Define user set variables
        $this->title = $this->get_option('title') ? $this->get_option('title') : 'Tasdid Service';
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->username = $this->get_option('username');
        $this->password = $this->get_option('password');
        $this->token = $this->get_option('token');
        $this->isDollar = $this->get_option('isDollar');
        $this->currency_price = $this->get_option('currency_price');
        $this->service_id = $this->get_option('serviceId');

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'order_received_text'), 10, 2);


        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'isValidToUse'));
        }


        if (!empty($this->token)) {
            if (!Tasdid_Gateway_Helper::isValidToken($this->token)) {
                $token = Tasdid_Gateway_Helper::login($this->username, $this->password);
                if (empty($token)) return;
                $this->update_option("token", $token);
                $this->token = $token;
            }
        }

    }

    /**
     * initial settings fields
     *
     * @since 1.0.0
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable', 'tasdid-gateway'),
                'label' => __('Enable Tasdid Gateway', 'tasdid-gateway'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'tasdid-gateway'),
                'label' => __('Payment Option Title', 'tasdid-gateway'),
                'type' => 'text',
                'default' => 'Tasdid Service',
            ),
            'description' => array(
                'title' => __('Description', 'tasdid-gateway'),
                'type' => 'textarea',
                'description' => __('This description is what user see when choose tasdid gateway', 'tasdid-gateway'),
                'default' => __('Pay via Tasdid with your QiCard or MasterQi'),
            ),
            'username' => array(
                'title' => __('Username', 'tasdid-gateway'),
                'label' => __('Tasdid username', 'tasdid-gateway'),
                'type' => 'text',
            ),
            'password' => array(
                'title' => __('Password', 'tasdid-gateway'),
                'label' => __('Tasdid password', 'tasdid-gateway'),
                'type' => 'password',
            ),
            'isDollar' => array(
                'title' => __('store currency is the dollar?', 'tasdid-gateway'),
                'label' => __('check this only if your store currency is the dollar', 'tasdid-gateway'),
                'type' => 'checkbox',
            ),
            'currency_price' => array(
                'title' => __('The exchange rate of the dollar against the dinar', 'tasdid-gateway'),
                'label' => __('Tasdid ServiceId', 'tasdid-gateway'),
                'type' => 'number',
            ),
            'serviceId' => array(
                'title' => __('ServiceID', 'tasdid-gateway'),
                'label' => __('Tasdid ServiceId', 'tasdid-gateway'),
                'type' => 'text',
            ),
            'webhook_url' => array(
                'title' => __('Webhook Url V2', 'tasdid-gateway'),
                'type' => 'title',
                'description' => '<b><a href="' . get_site_url() . '/wp-json/tasdid/v1/orders" target="_blank">' . get_site_url() . '/wp-json/tasdid/v1/orders' . '</a></b>',
            ),
            'token' => array(
                'type' => 'hidden',
            ),
        );
    }

    /**
     * Customize order received text for woocommerce on use tasdid gateway.
     *
     * @since    1.0.0
     */

    public function order_received_text($text, $order)
    {
        if ($order->get_payment_method() === 'tasdid-gateway') {
            echo __('Dear Mr/Mrs.', 'tasdid-gateway') . PHP_EOL . $order->get_billing_first_name() . '<br />';
            echo __('An invoice has been created for your order, please pay the bill through tasdid platform', 'tasdid-gateway') . '<br />';
            echo '<a href="https://pay.tasdid.net/?id=' . $order->get_meta('_ts_order') . '" class="button">' . __("Pay Bill", "tasdid") . '</a>';
        } else {
            echo $text;
        }

    }

    /**
     * on choose pay using tasdid gateway crate bill for user order and change the status
     * of the order to pending and store tasdid bill number in the post
     *
     * @param int $order_id
     * @return array|null;
     * @since 1.0.0
     */
    public function process_payment($order_id)
    {
        global $woocommerce;
        $order = wc_get_order($order_id);

        if (!$this->validate_phone_number($order->get_billing_phone())) {
            return wc_add_notice(__('Error: Invalid phone number', 'tasdid-gateway'), 'error');
        }

        // create tasdid bill
        $request = $this->create_tasdid_bill($order);
        // decode request response
        $response = json_decode(wp_remote_retrieve_body($request), true);
        $statusCode = wp_remote_retrieve_response_code($request);

        switch ($statusCode) {
            case 200:
                $order->update_status('pending', __('Awaiting bill payment', 'tasdid-gateway'));
                add_post_meta($order->get_id(), "_ts_order", $response['data']['payId']);
                $woocommerce->cart->empty_cart();
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            case 400 && $response['message'] === 'Duplicate PayId':
                wc_add_notice(__('Sorry, this order already has a bill from tasdid.', 'tasdid-gateway'), 'error');
                Logger::error("order_id: " . $order_id . " Error Message: " . $response['message'], $request['response']);
                break;
            case 400:
                wc_add_notice(__('Something Wrong, please check your information', 'tasdid-gateway'), 'error');
                Logger::error("order_id: " . $order_id . " Error Message: " . $response['message'], $request['response']);
                break;
            default:
                wc_add_notice(__('Something Wrong, Please try again later.', 'tasdid-gateway') . $statusCode, 'error');
                Logger::error("order_id: " . $order_id . " Response Code:" . $statusCode, $request['response']);
                break;
        }

        return null;
    }

    /**
     * validate mobile number with specific rules
     * rules: mobile should contain 07[x] and length should be 11
     *
     * @param string $mobile
     * @return bool
     * @since    1.2.2
     */
    private function validate_phone_number($mobile)
    {
        if (!preg_match_all('/07[3-9][0-9]/', $mobile)) {
            return false;
        }

        if (strlen($mobile) !== 11) {
            return false;
        }

        return true;
    }

    /**
     * Create Tasdid bill for the order
     * @param WC_Order $order
     * @return WP_Async_Request
     */
    private function create_tasdid_bill($order)
    {

        // create due date for tasdid bills
        $dueDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 5, date('Y')));

        /**
         * if product has a custom service_id, create bill using product service_id
         * @note if order has a difference items the global serviceId will be used
         * @since 1.2.0
         */
        $item_service_id = reset($order->get_items())->get_product()->get_meta('tasdid_serviceId');
        $service_id = $this->get_order_items_count($order) === 1 && !empty($item_service_id) ? $item_service_id : $this->service_id;

        $amount = $this->get_correct_order_amount($order->get_total());
        // request body for creating new bill for tasdid
        $args = array(
            'payId' => '',
            'customerName' => $order->get_formatted_billing_full_name(),
            'dueDate' => $dueDate,
            'payDate' => null,
            'amount' => $amount,
            'phoneNumber' => $order->get_billing_phone(),
            'serviceId' => $service_id,
        );
        // add Authorization to request header
        $headers = array(
            'Authorization' => 'Bearer ' . $this->token,
        );
        return Tasdid_Gateway_Helper::Request(TASDID_BASE_URL . '/Provider/AddBill', 'PUT', $args, $headers);
    }

    /**
     * get order items count without repeat the same item
     *
     * @since    1.0.0
     * @access   private
     */
    private function get_order_items_count($order)
    {
        $ordersIds = array();
        foreach ($order->get_items() as $item) {
            if (!in_array($item->get_id(), $ordersIds)) {
                array_push($ordersIds, $item->get_id());
            }

        }
        return count($ordersIds);
    }

    /**
     * convert order price to the dollar if it store use dollar currency
     * or return the price without decimal
     *
     * @param int $amount
     * @return int
     * @since 1.4.0
     */
    private function get_correct_order_amount($amount)
    {
        if ($this->isDollar !== 'no') {
            return number_format($amount * $this->currency_price);
        }
        return number_format($amount);
    }

    /**
     * Validate if the gateway is valid to use or not
     *
     * @since    1.0.0
     * @access   private
     */
    public function isValidToUse()
    {
        // validate if plugin is valid to use
        if (empty($this->get_option('username')) || empty($this->get_option('password'))) {
            $this->enabled = "no";
            $this->update_option('enabled', 'no');
            return;
        } else if (empty($this->get_option('serviceId'))) {
            Tasdid_Gateway_Helper::showError(__('Tasdid Gateway was disabled, You must set global ServiceID from Tasdid Gateway settings, this serviceID will be used when customer buy difference items', 'tasdid-gateway'));
            $this->enabled = "no";
            $this->update_option('enabled', 'no');
            return;
        }
        $token = Tasdid_Gateway_Helper::login($this->get_option('username'), $this->get_option('password'));
        if (empty($token)) {
            $this->enabled = "no";
            $this->update_option('enabled', 'no');
            $this->update_option('token', null);
            return;
        }
        $this->update_option("token", $token);
        $this->token = $token;
    }

}
