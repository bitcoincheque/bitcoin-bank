<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Cheque_Receiver extends Rest_Money_Account_Api_Cheques
{
    const CHEQUE_ID = 'cheque_id';
    const ISSUE_TIMESTAMP = 'issue_time';
    const EXPIRE_TIME = 'expire_time';
    const STATE = 'state';
    const ISSUER_IDENTITY = 'issuer_identity';
    const SENDER_ADDRESS = 'sender_address';
    const RECEIVER_ADDRESS = 'receiver_address';
    const AMOUNT = 'amount';
    const CURRENCY_UNIT = 'currency_unit';
    const MEMO = 'memo';
    const ACCESS_CODE = 'access_code';
    const HASH = 'hash';

    public function __construct() {
        $model_class_name = 'BCQ_BitcoinBank\Cheque_Db_Table';
        parent::__construct($model_class_name);
    }

    public function register_routes($route = null, $methods=array())
    {
        parent::register_routes('/cheque-receiver', array(
            self::METHOD_GET_MANY => array(),
            self::METHOD_GET_ID => array(),
            self::METHOD_POST => array()
        ));
        /*
        register_rest_route( self::NAME_SPACE, '/cheques', array(
            array(
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => array($this, 'endpoint_get'),
                'permission_callback' => array($this, 'authenticate_get_client_id')
            ),
            'schema' => array($this, 'get_schema')
        ));

        register_rest_route( self::NAME_SPACE, '/cheques/(?P<' . self::ENDPOINT_ID_KEY. '>\d+)', array(
            array(
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => array($this, 'endpoint_get_single'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => array($this, 'validate_id_arguments'),
                        'sanitize_callback' => array($this, 'sanitize_id_arguments')
                    ),
                ),
                'permission_callback' => array($this, 'authenticate_get_client_id')
            ),
            'schema' => array($this, 'get_schema'),
        ));

        register_rest_route( self::NAME_SPACE, '/cheques', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array('BCQ_BitcoinBank\Rest_Money_Account_Api_Cheques', 'endpoint_post'),
            'schema' => array('BCQ_BitcoinBank\Rest_Money_Account_Api_Cheques', 'get_schema'),
        ));
        */
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::CHEQUE_ID;
    }

    public function endpoint_get_many_read_data($request, $query_parameters) {
        $client_id = $this->get_authorised_client_id();
        $query_parameters->add_filter(Cheque_Db_Table::SENDER_CLIENT_ID, $client_id);
        return parent::endpoint_get_many_read_data($request, $query_parameters);
    }

    public function endpoint_get_single_read_database($request, $query_parameters) {
        $client_id = $this->get_authorised_client_id();
        $query_parameters->add_filter(Cheque_Db_Table::SENDER_CLIENT_ID, $client_id);
        return parent::endpoint_get_single_read_database($request, $query_parameters);
    }

    public function endpoint_post_write_database ($record, $request, $query_parameters) {
        $error = false;

        $client_id = $this->get_authorised_client_id();
        if($client_id === false) {
            $error = new \WP_Error('client_error', 'Unauthorised user.', array('status' => 401));
        }

        if(!$error) {
            $account_id = Accounting::get_client_default_account($client_id);
            if ($account_id === false) {
                $error = new \WP_Error('client_error', 'No account exist for client.', array('status' => 401));
            }
        }

        if(!$error) {
            $cheque = new Cheque_File();
            $result = $cheque->set_data_record($record);
            if ($result !== true) {
                $error_message = 'Error in input data for coding cheque.';
                $error = new \WP_Error('client_error', $error_message, array('status' => 400));
            }
        }

        if(!$error) {
            $sender_address = Accounting::get_client_money_address();
            if($sender_address === false) {
                $error = new \WP_Error('client_error', 'No Money Address exist for this account.', array('status' => 500));
            }
        }

        if(!$error) {
            if ($cheque->set_sender_address($sender_address) === false) {
                $error = new \WP_Error('client_error', 'Error in Money Address for this account.', array('status' => 500));
            }
        }

        if(!$error) {
            if ($cheque->has_all_mandatory_data() === false) {
                $missing_fields = $cheque->get_mandatory_fields_missing();
                $rest_fields = $this->convert_db_to_rest_record($missing_fields);
                $error_message = $cheque->get_error_message() . ': ' . implode(', ', array_keys($rest_fields)) . '.';
                $error = new \WP_Error('client_error', $error_message, array('status' => 400));
            }
        }

        if(!$error) {
            $fee_obj = Prices::calculate_cheque_fee($client_id, $account_id, $cheque);
            $amount_obj = $cheque->get_currency();
            $total_price = $amount_obj->get_value() + $fee_obj->get_value();
            $has_fund = Accounting::check_enough_balance($account_id, $total_price);
            if($has_fund !== true) {
                /* Insufficient fund will return HTTP status 200 and a error message in the response body. */
                if(Accounting::check_enough_balance($account_id, $amount_obj->get_value())) {
                    $message = 'Account does not have enough fund for the cheque fee of ' . $fee_obj->formatted_print() . '.' ;
                    $error = new \WP_Error('payment_required', $message, array('status' => 402));
                }else {
                    $message = 'Not enough funds.';
                    $error = new \WP_Error('payment_required', $message, array('status' => 402));
                }
            }
        }

        if(!$error) {
            $debit_account_id = $account_id;
            $time_stamp = time();
            $sender_client_id = $client_id;
            $receiver_client_id = null;
            $cheque = Cheque_Handler::create_cheque(
                $cheque,
                $time_stamp,
                $debit_account_id,
                $fee_obj,
                $sender_client_id,
                $receiver_client_id
            );
            if($cheque === false) {
                $error = new \WP_Error('server_error', 'Server error: Can not create cheque.', array('status' => 500));
            }
        }

        if(!$error) {
            if($cheque->validate_cheque_data() !== true) {
                $error_type = $cheque->get_error_type();
                switch ($error_type) {
                    case Cheque_File::ERROR_MANDATORY_DATA_MISSING:
                        $missing_fields = $cheque->get_mandatory_fields_missing();
                        $rest_fields = $this->convert_db_to_rest_record($missing_fields);
                        $error_message = $cheque->get_error_message() . ': ' . implode(', ', array_keys($rest_fields)) . '.';
                        $error = new \WP_Error('client_error', $error_message, array('status' => 400));
                        break;

                    default:
                        $error_message = 'Error creating cheque: ' . $cheque->get_error_message();
                        $error = new \WP_Error('client_error', $error_message, array('status' => 400));
                        break;
                }
            }
        }

        if(!$error) {
            $amount = $amount_obj->get_value();
            $cheque_id = $cheque->get_data(Cheque_File::SERIAL_NUMBER);
            $fee = $fee_obj->get_value();
            $result = Accounting::make_cheque_transaction(
                $debit_account_id,
                $amount,
                $cheque_id,
                $fee,
                $time_stamp = null
            );
            if($result !== true) {
                $error = new \WP_Error('server_error', 'Server error: Can not make account transaction.', array('status' => 500));
            }
        }

        if(!$error) {
            $result = Cheque_Handler::change_state_to_issued($cheque_id);
            if($result !== true) {
                $error = new \WP_Error('server_error', 'Server error: Can not issue cheque. Transaction has been made, but will be refunded when cheque expires.', array('status' => 500));
            }
        }

        if(!$error) {
            $result = $cheque->get_cheque_data();
        } else {
            $result = $error;
        }

        return $result;
    }

    public function endpoint_postxxx($request) {
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
                    'readonly' => true,
                ),
                self::EXPIRE_TIME => array(
                    'description' => esc_html__('UTC time whent his cheque expires.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
                ),
                self::AMOUNT => array(
                    'amount' => esc_html__('Face value as a string, denoted in currency.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::CURRENCY_UNIT => array(
                    'amount' => esc_html__('Currency unit.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                self::SENDER_ADDRESS => array(
                    'description' => esc_html__('Senders address.', 'bitcoin-bank'),
                    'type' => 'string',
                    'readonly' => true,
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
                    'readonly' => true,
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
            self::CURRENCY_UNIT => Cheque_Db_Table::CURRENCY_UNIT,
            self::MEMO => Cheque_Db_Table::MEMO,
            self::ACCESS_CODE => Cheque_Db_Table::ACCESS_CODE,
            self::HASH => Cheque_Db_Table::HASH,
        );
        return $mapping_table;
    }
}

