<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Info extends Rest_Money_Account_Api
{

    public function register_routes($route = null, $methods=array())
    {
        register_rest_route( self::NAME_SPACE, '/info', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array('BCQ_BitcoinBank\Rest_Maapi_Info', 'endpoint_info_get'),
            'schema' => 'endpoint_info_schema',
        ));
    }

    static public function endpoint_info_get( $request ) {
        $bank_identity = new Settings_Bank_Identity_Options();
        $bank_identity->load_data();
        $bank_name = $bank_identity->get_data(Settings_Bank_Identity_Options::BANK_NAME);

        $response = array(
            'name' => $bank_name,
            'address' => '',
            'web' => site_url(),
            'supported_money_account_api_versions' => array('v1')
        );

        return rest_ensure_response( $response );
    }

    static function endpoint_info_schema() {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'info',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'name' => array(
                    'description'  => esc_html__( 'Name of the site.', 'my-textdomain' ),
                    'type'         => 'string',
                    'readonly'     => true,
                ),
                'address' => array(
                    'description'  => esc_html__( 'Physical address.', 'my-textdomain' ),
                    'type'         => 'integer',
                ),
                'web' => array(
                    'description'  => esc_html__( 'Web address.', 'my-textdomain' ),
                    'type'         => 'string',
                ),
                'supported_money_account_api_versions' => array(
                    'description'  => esc_html__( 'List of supported api versions.', 'my-textdomain' ),
                    'type'         => 'string',
                ),
            ),
        );

        return $schema;
    }

}
