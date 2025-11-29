<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nexa_RE_Api_Client {

    protected $base_url;
    protected $token;

    public function __construct() {
        $this->base_url = rtrim( Nexa_RE_Settings::API_BASE_URL, '/' );
        $this->token    = trim( get_option( Nexa_RE_Settings::OPTION_API_TOKEN, '' ) );
    }

    protected function request( $method, $path, $body = null, $extra_args = [] ) {
        $url = $this->base_url . $path;

        $args = array_merge(
            [
                'method'  => $method,
                'headers' => [
                    'X-AGENCY-TOKEN' => $this->token,
                    'Accept'         => 'application/json',
                ],
                'timeout' => 15,
            ],
            $extra_args
        );

        if ( $body !== null ) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return [
                'ok'    => false,
                'error' => $response->get_error_message(),
                'code'  => 0,
                'data'  => null,
            ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        return [
            'ok'    => $code >= 200 && $code < 300,
            'error' => $code >= 400 ? ( is_array( $data ) ? ( $data['message'] ?? '' ) : '' ) : '',
            'code'  => $code,
            'data'  => $data,
        ];
    }

    /* -------- Properties endpoints -------- */

    public function list_properties() {
        return $this->request( 'GET', '/properties' );
    }

    public function get_property( $id ) {
        return $this->request( 'GET', '/properties/' . (int) $id );
    }

    public function delete_property( $id ) {
        return $this->request( 'DELETE', '/properties/' . (int) $id );
    }

    public function create_property( array $payload ) {
        return $this->request( 'POST', '/properties', $payload );
    }

    public function update_property( $id, array $payload ) {
        return $this->request( 'PUT', '/properties/' . (int) $id, $payload );
    }

    // Later we can add:
    // public function list_appointments() { ... }
    // public function create_task() { ... }
}
