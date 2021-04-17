<?php

namespace BCQ_BitcoinBank;

use WP_PluginFramework\Utils\Query_Parameters;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Accounts extends Rest_Money_Account_Api
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
            self::METHOD_GET_MANY => array(),
            self::METHOD_GET_ID => array()
        ));
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::FIELD_ACCOUNT_ID;
    }

    public function update_query_parameter($request, $query_parameters) {
        $client_id = $this->get_authorised_client_id();
        $query_parameters->add_filter(Accounts_Db_Table::CLIENT_ID, $client_id);
        return $query_parameters;
    }

    static public function endpoint_post_cheques( $request )
    {
        $result = self::authenticate_get_client_id($request);

        if( $result['ok'] === true )
        {
            $json = $request->get_json_params();
            if(is_array($json))
            {
                $receiver_address = $json['send_to'];
                $amount = intval($json['amount']);
                $memo = $json['memo'];
                $expire = $json['expire'];

                if ($receiver_address and $amount and $expire)
                {
                    $debit_account_id = Accounting::get_client_default_account($result['client_id']);
                    $sender_address = Accounting::get_client_money_address();
                    $expire_time = time() + $expire;
                    $pay_fee = true;

                    $cheque_id = Cheque_Handler::make_cheque_transaction(
                        $debit_account_id,
                        $amount,
                        $sender_address,
                        $receiver_address,
                        $expire_time,
                        $memo,
                        $pay_fee
                    );

                    $response = array(
                        'success' => 'ok',
                        'cheque_id' => $cheque_id
                    );

                    return rest_ensure_response($response);
                } else {
                    $result['error_message'] = 'Missing json data';
                }
            } else {
                $result['error_message'] = 'Json data missing in body.';
            }
        }

        $result['error_code'] = 'error';

        return new \WP_Error(
            $result['error_code'],
            $result['error_message'],
            array( 'status' => $result['http_status'] )
        );
    }

    public function get_schema() {
        $schema = $this->get_account_schema();
        return $schema;
    }

    public function get_schema_for_id_endpoint() {
        $schema = $this->get_account_schema();
        return $schema;
    }

    public function get_account_schema() {
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
