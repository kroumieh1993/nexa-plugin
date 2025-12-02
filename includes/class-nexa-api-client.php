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

    public function list_properties( array $filters = [] ) {
        $path = '/properties';
        
        // Build query string from filters, only including non-empty values
        // Note: Filters should be pre-validated before calling this method
        $query_params = [];
        
        if ( ! empty( $filters['city'] ) ) {
            $query_params['city'] = $filters['city'];
        }
        if ( ! empty( $filters['category'] ) ) {
            $query_params['category'] = $filters['category'];
        }
        if ( ! empty( $filters['type'] ) ) {
            $query_params['type'] = $filters['type'];
        }
        if ( isset( $filters['min_price'] ) && $filters['min_price'] !== '' ) {
            $query_params['min_price'] = (int) $filters['min_price'];
        }
        if ( isset( $filters['max_price'] ) && $filters['max_price'] !== '' ) {
            $query_params['max_price'] = (int) $filters['max_price'];
        }
        if ( isset( $filters['bedrooms'] ) && $filters['bedrooms'] !== '' ) {
            $query_params['bedrooms'] = (int) $filters['bedrooms'];
        }
        if ( isset( $filters['bathrooms'] ) && $filters['bathrooms'] !== '' ) {
            $query_params['bathrooms'] = (int) $filters['bathrooms'];
        }
        
        if ( ! empty( $query_params ) ) {
            $path .= '?' . http_build_query( $query_params );
        }
        
        return $this->request( 'GET', $path );
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

    /* -------- Agency Parameters endpoints -------- */

    /**
     * List all agency parameters (cities and property types).
     *
     * @param string|null $type Optional filter by type ('city' or 'property_type').
     * @return array API response.
     */
    public function list_agency_parameters( $type = null ) {
        $path = '/agency-parameters';
        if ( $type ) {
            $path .= '?type=' . rawurlencode( $type );
        }
        return $this->request( 'GET', $path );
    }

    /**
     * Create a new agency parameter.
     *
     * @param array $payload { parameter_type, value, sort_order }.
     * @return array API response.
     */
    public function create_agency_parameter( array $payload ) {
        return $this->request( 'POST', '/agency-parameters', $payload );
    }

    /**
     * Update an existing agency parameter.
     *
     * @param int   $id      Parameter ID.
     * @param array $payload { value, sort_order }.
     * @return array API response.
     */
    public function update_agency_parameter( $id, array $payload ) {
        return $this->request( 'PUT', '/agency-parameters/' . (int) $id, $payload );
    }

    /**
     * Delete an agency parameter.
     *
     * @param int $id Parameter ID.
     * @return array API response.
     */
    public function delete_agency_parameter( $id ) {
        return $this->request( 'DELETE', '/agency-parameters/' . (int) $id );
    }

    /**
     * Bulk replace all values for a parameter type.
     *
     * @param string $type   Parameter type ('city' or 'property_type').
     * @param array  $values Array of string values.
     * @return array API response.
     */
    public function bulk_update_agency_parameters( $type, array $values ) {
        return $this->request( 'PUT', '/agency-parameters/bulk/' . rawurlencode( $type ), [ 'values' => $values ] );
    }
}
