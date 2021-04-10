<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

class Cheque_File
{
    /* Error codes */
    const NO_ERROR = 'no_error';
    const ERROR_MANDATORY_DATA_MISSING = 'error_mandatory_data_missing';
    const ERROR_UNDEFINED = 'error_undefined';

    const SERIAL_NUMBER = 'id';
    const ISSUER_IDENTITY = 'issuer_identity';
    const CURRENCY_UNIT = 'currency_unit';
    const AMOUNT = 'amount';
    const ISSUE_TIME = 'issue_time';
    const EXPIRE_TIME = 'expire_time';
    const SENDER_ADDRESS = 'sender_address';
    const RECEIVER_ADDRESS = 'receiver_address';
    const MEMO = 'memo';
    const ACCESS_CODE = 'access_code';
    const HASH = 'hash';

    protected $cheque_data = array(
        self::ISSUER_IDENTITY => '',
        self::SERIAL_NUMBER => null,
        self::CURRENCY_UNIT => '',
        self::AMOUNT => 0,
        self::ISSUE_TIME => '',
        self::EXPIRE_TIME => '',
        self::SENDER_ADDRESS => null,
        self::RECEIVER_ADDRESS => null,
        self::MEMO => '',
        self::ACCESS_CODE => null,
        self::HASH => null,
    );

    protected $mandotary_data_given = array(
        self::AMOUNT => false,
        self::CURRENCY_UNIT => false,
        self::EXPIRE_TIME => false,
        self::SENDER_ADDRESS => false,
        self::RECEIVER_ADDRESS => false
    );

    protected $last_error_code = null;
    protected $last_error_message = null;

    public function __construct() {
    }

    public function set_data_record($data_record) {
        $result=true;
        if( $this->set_number_field('/', self::SERIAL_NUMBER, $data_record, 'id') !== true) {
            $result=false;
        }
        if( $this->set_currency_field('/', self::AMOUNT, $data_record) !== true) {
            $result=false;
        }
        if( $this->set_string_field('/', self::CURRENCY_UNIT, $data_record) !== true) {
            $result=false;
        }
        if( $this->set_time_field('/', self::ISSUE_TIME, $data_record, 'issue_time') !== true) {
            $result=false;
        }
        if( $this->set_time_field('/', self::EXPIRE_TIME, $data_record) !== true) {
            $result=false;
        }
        if( $this->set_time_field('/', self::SENDER_ADDRESS, $data_record) !== true) {
            $result=false;
        }
        if( $this->set_address_field('/', self::RECEIVER_ADDRESS, $data_record) !== true) {
            $result=false;
        }
        if( $this->set_string_field('/', self::MEMO, $data_record) !== true) {
            $result=false;
        }
        if( $this->set_string_field('/', self::ACCESS_CODE, $data_record) !== true) {
            $result=false;
        }
        return $result;
    }

    public function set_sender_address($sender_address) {
        if(is_string($sender_address)) {
            $this->cheque_data[self::SENDER_ADDRESS] = $sender_address;
            $this->mandotary_data_given[self::SENDER_ADDRESS] = true;
            return true;
        } else {
            return false;
        }
    }

    public function get_data($key) {
        return $this->cheque_data[$key];
    }

    public function set_data($key, $value) {
        $record = array($key => $value);
        return $this->set_data_record($record);
    }

    public function get_cheque_data($fields = null) {
        if ($this->has_all_mandatory_data()) {
            if ($fields) {
                foreach ($fields as $field) {
                    $record[$field] = $this->cheque_data[$field];
                }
            }
            else {
                $record = $this->cheque_data;
            }
            return $record;
        }
        else {
            return false;
        }
    }

    public function get_currency() {
        $currency = new Crypto_currency_type(null, null);
        $currency->set_value($this->cheque_data[self::AMOUNT]);
        $currency->set_unit($this->cheque_data[self::CURRENCY_UNIT]);
        return $currency;
    }

    public function get_text() {
        if ($this->has_all_mandatory_data()) {
            $json_text = json_encode($this->cheque_data);
            return $json_text;
        }
        else {
            return false;
        }
    }

    public function get_mandatory_fields_missing() {
        $missing_field = array();
        foreach ($this->mandotary_data_given as $key => $data_field) {
            if ($data_field == false) {
                $missing_field[$key] = $this->cheque_data[$key];
            }
        }
        return $missing_field;
    }

    public function has_all_mandatory_data() {
        foreach ($this->mandotary_data_given as $data_field) {
            if ($data_field == false) {
                return false;
            }
        }
        return true;
    }

    protected function set_number_field($path, $key, $record_array, $record_key = null) {
        if (!isset($record_key)) {
            $record_key = $key;
        }
        if (array_key_exists($record_key, $record_array)) {
            $data = $record_array[$record_key];
            switch (gettype($data)) {
                case 'integer':
                    $this->cheque_data[$key] = $record_array[$record_key];
                    if (array_key_exists($key, $this->mandotary_data_given)) {
                        $this->mandotary_data_given[$key] = true;
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }

        return true;
    }

    protected function set_currency_field($path, $key, $record_array, $record_key = null) {
        if (!isset($record_key)) {
            $record_key = $key;
        }
        if (array_key_exists($record_key, $record_array)) {
            $data = $record_array[$record_key];
            switch (gettype($data)) {
                case 'integer':
                    $this->cheque_data[$key] = $record_array[$record_key];
                    if (array_key_exists($key, $this->mandotary_data_given)) {
                        $this->mandotary_data_given[$key] = true;
                    }
                    break;
                case 'string':
                    $amount = Crypto_currency_type::convert_str_to_value($data);
                    if (is_int($amount)) {
                        $this->cheque_data[$key] = $amount;
                        if (array_key_exists($key, $this->mandotary_data_given)) {
                            $this->mandotary_data_given[$key] = true;
                        }
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }

        return true;
    }

    protected function set_string_field($path, $key, $record_array, $record_key = null) {
        if (!isset($record_key)) {
            $record_key = $key;
        }
        if (array_key_exists($record_key, $record_array)) {
            if (is_string($record_array[$record_key])) {
                $this->cheque_data[$key] = $record_array[$record_key];

                if (array_key_exists($key, $this->mandotary_data_given)) {
                    $this->mandotary_data_given[$key] = true;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    protected function set_time_field($path, $key, $record_array, $record_key = null) {
        if (!isset($record_key)) {
            $record_key = $key;
        }
        if (array_key_exists($record_key, $record_array)) {
            if (is_string($record_array[$record_key])) {
                $this->cheque_data[$key] = $record_array[$record_key];

                if (array_key_exists($key, $this->mandotary_data_given)) {
                    $this->mandotary_data_given[$key] = true;
                }
            }
            else if (is_integer($record_array[$record_key])) {
                $this->cheque_data[$key] = date( 'Y-m-d H:i:s', $record_array[$record_key]);
                if (array_key_exists($key, $this->mandotary_data_given)) {
                    $this->mandotary_data_given[$key] = true;
                }
            }
            else {
                return false;
            }

            return true;
        }
    }

    protected function set_address_field($path, $key, $record_array, $record_key = null) {
        if (!isset($record_key)) {
            $record_key = $key;
        }
        if (array_key_exists($record_key, $record_array)) {
            if (is_string($record_array[$record_key])) {
                $this->cheque_data[$key] = $record_array[$record_key];

                if (array_key_exists($key, $this->mandotary_data_given)) {
                    $this->mandotary_data_given[$key] = true;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    protected function calculate_hash() {
    }

    public function validate_cheque_data() {
        if($this->has_all_mandatory_data()){
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    public function get_error_type() {
        if(!$this->has_all_mandatory_data()) {
            return self::ERROR_MANDATORY_DATA_MISSING;
        } else {
            return self::NO_ERROR;
        }
    }


    public function get_error_message() {
        if(!$this->has_all_mandatory_data()) {
            $message = 'Mandatory fields missing';
        } else {
            $message = 'Undefined error';
        }
        return $message;
    }
}
