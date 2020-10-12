<?php


defined( 'ABSPATH' ) || exit;


class Tasdid_Gateway_Helper
{
    static function decode_jwt($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))), true);
    }

    static function isValidToken($token)
    {
        $tokenExpire = Tasdid_Gateway_Helper::decode_jwt($token);
        $date = new \DateTime();
        return $date->getTimestamp() < $tokenExpire['exp'];
    }

    static function Request($url, $method, $body, $headers = [])
    {
        $headers = array_merge(array(
            'Content-Type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
        ), $headers);

        $args = array(
            'method' => $method,
            'headers' => $headers,
            'data_format' => 'body',
            'body' => json_encode($body)
        );
        // login
        return wp_remote_request($url, $args);
    }

    static function showError($text)
    {
        add_action('admin_notices', function () use ($text) {
            ?>
            <div class="error notice">
                <p><?php echo $text ?></p>
            </div>
            <?php
        });

    }

    static function login($username, $password)
    {
        $url = TASDID_BASE_URL.'/auth/token';
        $body = array(
            'username' => $username,
            'password' => $password
        );
        // login
        $request = Tasdid_Gateway_Helper::Request($url, "POST", $body);
        // get response body
        $request_body = json_decode(wp_remote_retrieve_body($request), true);
        // get ststus code
        $statusCode = wp_remote_retrieve_response_code($request);
        if ($statusCode === 401) return Tasdid_Gateway_Helper::showError("Tasdid Service: invalid username or password");

        return $request_body["token"];
    }
}