<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api_Cheques extends Rest_Money_Account_Api
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

    public function get_cheque_schema() {
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

