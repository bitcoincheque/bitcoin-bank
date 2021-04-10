<?php

namespace BCQ_BitcoinBank;

use WP_PluginFramework\Utils\Query_Parameters;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Public_Keys extends Rest_Money_Account_Api
{
    /* List of table field names: */
    const FIELD_PUBLIC_KEY_ID = 'id';
    const FIELD_CREATE_TIME = 'create_time';
    const FIELD_EXPIRE_TIME = 'expire_time';
    const FIELD_STATUS = 'status';
    const FIELD_ALGORITHM = 'key_type';
    const FIELD_BIT_LENGTH = 'key_length';
    const FIELD_PUBLIC_KEY = 'public_key';

    public function __construct() {
        $model_class_name = 'BCQ_BitcoinBank\Certificates';
        parent::__construct($model_class_name);
    }

    public function register_routes($route = null, $methods=array()) {
        $route = '/public-keys';
        register_rest_route($this->name_space, $route, array(
            array(
                'methods' => \WP_REST_Server::READABLE,
                'callback' => array($this, 'endpoint_get')
            ),
            'schema' => array($this, 'get_schema')
        ));

        register_rest_route( $this->name_space, $route . '/(?P<' . self::ENDPOINT_ID_KEY. '>\d+)', array(
            array(
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => array($this, 'endpoint_get_single'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => array($this, 'validate_id_arguments'),
                        'sanitize_callback' => array($this, 'sanitize_id_arguments')
                    ),
                ),
            ),
            'schema' => array($this, 'get_schema_id'),
        ));
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::FIELD_PUBLIC_KEY_ID;
    }

    public function endpoint_get_read_database ($request, $query_parameters) {
        $fields = array(
            Certificates::PRIMARY_KEY,
            Certificates::CREATED_TIME,
            Certificates::EXPIRE_TIME,
            Certificates::STATE,
            Certificates::ALGORITHM,
            Certificates::BIT_LENGTH
        );
        $query_parameters->filter_fields($fields);
        return parent::endpoint_get_read_database($request, $query_parameters);
    }

    public function endpoint_get_single_read_database ($request, $query_parameters) {
        $fields = array(
            Certificates::PRIMARY_KEY,
            Certificates::CREATED_TIME,
            Certificates::EXPIRE_TIME,
            Certificates::STATE,
            Certificates::ALGORITHM,
            Certificates::BIT_LENGTH,
            Certificates::PUBLIC_KEY
        );
        $query_parameters->filter_fields($fields);
        return parent::endpoint_get_single_read_database($request, $query_parameters);
    }

    public function get_schema() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'account',
            'type' => 'object',
            'properties' => array(
                self::FIELD_PUBLIC_KEY_ID => array(
                    'description' => esc_html__('Unique key id number.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                ),
                self::FIELD_CREATE_TIME => array(
                    'description' => esc_html__('Time when this public key was created.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_EXPIRE_TIME => array(
                    'description' => esc_html__('Expire time for public key..', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_STATUS => array(
                    'description' => esc_html__('Status of this public key.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_ALGORITHM => array(
                    'description' => esc_html__('Algorithm used for signing.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_BIT_LENGTH => array(
                    'description' => esc_html__('Certificate length in number of bits.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                )
            )
        );
        return $schema;
    }

    public function get_schema_for_id_endpoint() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'account',
            'type' => 'object',
            'properties' => array(
                self::FIELD_PUBLIC_KEY_ID => array(
                    'description' => esc_html__('Unique key id number.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                ),
                self::FIELD_CREATE_TIME => array(
                    'description' => esc_html__('Time when this public key was created.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_EXPIRE_TIME => array(
                    'description' => esc_html__('Expire time for public key..', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_STATUS => array(
                    'description' => esc_html__('Status of this public key.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_ALGORITHM => array(
                    'description' => esc_html__('Algorithm used for signing.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::FIELD_BIT_LENGTH => array(
                    'description' => esc_html__('Certificate length in number of bits.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                ),
                self::FIELD_PUBLIC_KEY => array(
                    'description' => esc_html__('Public key base64 encoded.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                )
            )
        );
        return $schema;
    }

    public function get_rest_database_mapping() {
        $mapping_table = array(
            self::FIELD_PUBLIC_KEY_ID => Certificates::PRIMARY_KEY,
            self::FIELD_CREATE_TIME => Certificates::CREATED_TIME,
            self::FIELD_EXPIRE_TIME => Certificates::EXPIRE_TIME,
            self::FIELD_STATUS => Certificates::STATE,
            self::FIELD_ALGORITHM => Certificates::ALGORITHM,
            self::FIELD_BIT_LENGTH => Certificates::BIT_LENGTH,
            self::FIELD_PUBLIC_KEY => Certificates::PUBLIC_KEY,
        );
        return $mapping_table;
    }

}
