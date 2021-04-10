<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Teller_Client_API_Cheques extends Rest_Teller_Client_API
{
    const CHEQUE_ID = 'cheque_id';
    const ISSUE_TIMESTAMP = 'issue_time';
    const EXPIRE_TIME = 'expire_time';
    const STATE = 'state';
    const ISSUER_IDENTITY = 'issuer_identity';
    const SENDER_ADDRESS = 'sender_address';
    const RECEIVER_ADDRESS = 'receiver_address';
    const AMOUNT = 'amount';
    const MEMO = 'memo';
    const ACCESS_CODE = 'access_code';
    const HASH = 'hash';

    public function __construct() {
        $model_class_name = 'BCQ_BitcoinBank\Cheque_Db_Table';
        parent::__construct($model_class_name);
    }

    public function register_routes($route = null, $methods=array())
    {
        parent::register_routes('/cheques', array(
            self::METHOD_GET => array(),
            self::METHOD_GET_ID => array()
        ));
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::CHEQUE_ID;
    }

    public function update_query_parameter($request, $query_parameters) {
        $client_id = $this->get_authorised_client_id();
        $query_parameters->add_filter(Cheque_Db_Table::SENDER_CLIENT_ID, $client_id);
        return $query_parameters;
    }

    public function endpoint_get_cheque($request) {
        $cheque_id = intval($request['id']);
        $cheque_data = new Cheque_Db_Table();
        $cheque_data->load_data_id($cheque_id);
        $record = $cheque_data->get_data_record();
        $cheque_file = new Cheque_File();
        $cheque_file->set_data_record($record);
        $response = $cheque_file->get_cheque_data();

        if ($response !== false) {
            return rest_ensure_response($response);
        }
        else {
            return new \WP_Error('invalid_cheque', 'The cheque does not exist.', array('status' => 404));
        }
    }

    public function endpoint_post_cheque($request) {
        $result = self::authenticate_get_client_id($request);

        if ($result['ok'] === true) {
            $json = $request->get_json_params();
            if (is_array($json)) {
                $receiver_address = $json['send_to'];
                $amount = intval($json['amount']);
                $memo = $json['memo'];
                $expire = $json['expire'];

                if ($receiver_address and $amount and $expire) {
                    $debit_account_id = Accounting::get_client_default_account($result['client_id']);
                    $sender_address = Accounting::get_client_money_address();
                    $expire_time = time() + $expire;
                    $pay_fee = true;

                    $cheque_id = Cheque_Handler::make_cheque_transaction($debit_account_id, $amount, $sender_address, $receiver_address, $expire_time, $memo, $pay_fee);

                    $response = array(
                        'success' => 'ok',
                        'cheque_id' => $cheque_id
                    );

                    return rest_ensure_response($response);
                }
                else {
                    $result['error_message'] = 'Missing json data';
                }
            }
            else {
                $result['error_message'] = 'Json data missing in body.';
            }
        }

        $result['error_code'] = 'error';

        return new \WP_Error($result['error_code'], $result['error_message'], array('status' => $result['http_status']));
    }

    public function get_schema() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'cheque',
            'description' => esc_html__('This schema holds the standardized cheque file.', 'bitcoin-bank'),
            'type' => 'object',
            'properties' => array(
                self::CHEQUE_ID => array(
                    'description' => esc_html__('Unique number set by the issuer identifying this cheque.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                ),
                self::ISSUE_TIMESTAMP => array(
                    'description' => esc_html__('UTC time when this cheque was created.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::EXPIRE_TIME => array(
                    'description' => esc_html__('UTC time whent his cheque expires.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::AMOUNT => array(
                    'amount' => esc_html__('Face value as a string, denoted in currency.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::SENDER_ADDRESS => array(
                    'description' => esc_html__('Senders address.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::RECEIVER_ADDRESS => array(
                    'description' => esc_html__('Receivers address.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::MEMO => array(
                    'description' => esc_html__('Optional comment.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::ACCESS_CODE => array(
                    'description' => esc_html__('Access code to access the cheque.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
            ),
        );
        return $schema;
    }

    public function get_rest_database_mapping() {
        $mapping_table = array(
            self::CHEQUE_ID => Cheque_Db_Table::PRIMARY_KEY,
            self::ISSUE_TIMESTAMP => Cheque_Db_Table::ISSUE_TIMESTAMP,
            self::EXPIRE_TIME => Cheque_Db_Table::EXPIRE_TIME,
            self::ISSUER_IDENTITY => Cheque_Db_Table::ISSUER_IDENTITY,
            self::SENDER_ADDRESS => Cheque_Db_Table::SENDER_ADDRESS,
            self::RECEIVER_ADDRESS => Cheque_Db_Table::RECEIVER_ADDRESS,
            self::AMOUNT => Cheque_Db_Table::AMOUNT,
            self::MEMO => Cheque_Db_Table::MEMO,
            self::ACCESS_CODE => Cheque_Db_Table::ACCESS_CODE,
            self::HASH => Cheque_Db_Table::HASH,
        );
        return $mapping_table;
    }
}
