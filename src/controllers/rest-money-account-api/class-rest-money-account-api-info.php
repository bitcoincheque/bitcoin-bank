<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Info extends Rest_Money_Account_Api
{

    public function register_routes($route = null, $methods=array())
    {
        parent::register_routes('/info', array(
            self::METHOD_GET_ONE => array()
        ));
    }

    public function endpoint_get_has_permission($request) {
        return true;
    }

    public function endpoint_get_one_read_data ($request, $query_parameters) {
        $bank_name = Settings_Bank_Identity_Options::get_options(Settings_Bank_Identity_Options::BANK_NAME);

        $response = array(
            'name' => $bank_name,
            'address' => '',
            'web' => site_url(),
            'supported_money_account_api_versions' => array('v1')
        );

        return $response;
    }

    public function get_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'info',
            'type'                 => 'object',
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

