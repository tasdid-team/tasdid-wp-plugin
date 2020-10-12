<?php
/**
 * add custom rest-api to update order status using tasdid webhook
 *
 *
 * @link       https://tasdid.net
 * @since      1.2.0
 *
 * @package    Tasdid_Gateway
 * @subpackage Tasdid_Gateway/includes
 */

defined('ABSPATH') || exit;

class Tasdid_Gateway_Routes extends WP_REST_Controller
{

    /**
     * Register the routes for the objects of the controller.
     *
     * @since 1.0.0
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'tasdid/v' . $version;
        $base = 'orders';
        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_order_status'),
                'args' => array(),
            ),
        ));
    }

    /**
     * update order status from pending to processing by tasdid bill number
     *
     * @param WP_REST_REQUEST $request
     * @return WP_REST_Response
     * @since 1.0.0
     */
    public function update_order_status($request)
    {

        $body_params = $request->get_json_params();
        if (!$this->is_valid_request($body_params)) {
            Logger::warning('invalid request with invalid body', $body_params);
            return $this->http_response(array('status' => 'failed', 'message' => 'request body is not valid!'), 400);
        }

        $orders = wc_get_orders(array('_ts_order' => $body_params['PayId']));

        if (count($orders) === 0) {
            Logger::error('order_id: ' . $body_params['PayId'] . ' is not exist!');
            return $this->http_response(array('status' => 'failed', 'message' => 'order not exist!'), 400);

        }

        $payment_gateways = WC_Payment_Gateways::instance();
        $tasdid_gateway = $payment_gateways->payment_gateways()['tasdid-gateway'];
        $privateKey = strtoupper(md5($tasdid_gateway->settings['username'] . '|' . $body_params['PayId'] . '|' . $body_params['Status']));

        // validate private key
        if ($privateKey === $body_params["Key"]) {
            $orders[0]->update_status("processing");
            Logger::notice('order_id: ' . $body_params['PayId'] . ' has been paid');
            return $this->http_response(array('status' => 'success', 'message' => 'order status has been set as paid'), 200);
        } else {
            Logger::error('order_id: ' . $body_params['PayId']  . ' got request with invalid key', $request->get_params());
            return $this->http_response(array('status' => 'failed', 'message' => 'secret key is not valid!'), 400);
        }

    }

    /**
     * validate request body and check if it containts the required properties
     *
     * @param array $body
     * @return boolean
     * @since 1.4.0
     */
    private function is_valid_request($body)
    {
        return isset($body["PayId"]) && isset($body["Status"]) && isset($body["Key"]);
    }

    /**
     * create wordpress response for WP_REST
     *
     * @param array $body
     * @param int $status
     * @return WP_REST_Response
     * @since 1.4.0
     */
    private function http_response($body, $status)
    {
        $response = new WP_REST_Response($body);
        $response->set_status($status);
        return $response;

    }
}
