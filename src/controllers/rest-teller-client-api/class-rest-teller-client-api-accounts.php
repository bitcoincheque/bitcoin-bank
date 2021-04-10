<?php

namespace BCQ_BitcoinBank;

use WP_PluginFramework\Utils\Query_Parameters;

defined( 'ABSPATH' ) || exit;

class Rest_Teller_Client_Api_Accounts extends Rest_Teller_Client_API
{
    /* List of table field names: */
    const FIELD_ACCOUNT_ID = 'account_id';
    const FIELD_CLIENT_ID = 'client_id';
    const FIELD_BALANCE = 'balance';
    const FIELD_CURRENCY = 'currency';
    const FIELD_LABEL = 'label';

    public function __construct() {
        $model_class_name = 'BCQ_BitcoinBank\Accounts_Db_Table';
        parent::__construct($model_class_name);
    }

    public function register_routes($route = null, $methods=array())
    {
        parent::register_routes('/accounts', array(
            self::METHOD_GET => array(),
            self::METHOD_GET_ID => array()
        ));
    }

    public function update_query_parameter($request, $query_parameters) {
        $client_id = $this->get_authorised_client_id();
        $query_parameters->add_filter(Accounts_Db_Table::CLIENT_ID, $client_id);
        return $query_parameters;
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::FIELD_ACCOUNT_ID;
    }

    public function get_schema() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'account',
            'type' => 'object',
            'properties' => array(
                self::FIELD_ACCOUNT_ID => array(
                    'description' => esc_html__('Unique account number set by the bank.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                ),
                self::FIELD_CLIENT_ID => array(
                    'description' => esc_html__('Account owner.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::FIELD_BALANCE => array(
                    'description' => esc_html__('Cash in the account.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::FIELD_LABEL => array(
                    'description' => esc_html__('Name of the account.', 'bitcoin-bank'),
                    'type' => 'string',
                )
            )
        );
        return $schema;
    }

    public function get_rest_database_mapping() {
        $mapping_table = array(
            self::FIELD_ACCOUNT_ID => Accounts_Db_Table::PRIMARY_KEY,
            self::FIELD_CLIENT_ID => Accounts_Db_Table::CLIENT_ID,
            self::FIELD_BALANCE => Accounts_Db_Table::BALANCE,
            self::FIELD_LABEL => Accounts_Db_Table::LABEL,
        );
        return $mapping_table;
    }

}
