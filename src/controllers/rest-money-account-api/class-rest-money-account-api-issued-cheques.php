<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Issued_Cheques extends Rest_Money_Account_Api_Cheques
{
    public function __construct() {
        $model_class_name = 'BCQ_BitcoinBank\Cheque_Db_Table';
        parent::__construct($model_class_name);
    }

    public function register_routes($route = null, $methods=array())
    {
        parent::register_routes('/issued-cheques', array(
            self::METHOD_GET => array(),
            self::METHOD_GET_ID => array(),
            self::METHOD_POST => array()
        ));
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::CHEQUE_ID;
    }

    public function endpoint_get_read_database($request, $query_parameters) {
        $client_id = $this->get_authorised_client_id();
        $query_parameters->add_filter(Cheque_Db_Table::SENDER_CLIENT_ID, $client_id);
        return parent::endpoint_get_read_database($request, $query_parameters);
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

    public function get_schema() {
        $schema = $this->get_cheque_schema();
        return $schema;
    }

    public function get_schema_for_id_endpoint() {
        $schema = $this->get_cheque_schema();
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

