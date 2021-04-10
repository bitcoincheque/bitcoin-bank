<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

class Rest_Teller_Client_API_Transactions extends Rest_Teller_Client_API
{
    public function __construct() {
        $model_class_name = 'BCQ_BitcoinBank\Transactions_Db_Table';
        parent::__construct($model_class_name);
    }

    public function register_routes($route = null, $methods=array())
    {
        parent::register_routes('/transactions', array(
            self::METHOD_GET => array(),
            self::METHOD_GET_ID => array()
        ));
    }

    public function update_query_parameter($request, $query_parameters) {

        if(isset($request['id'])) {
            $transaction_id = $request['id'];
            $query_parameters->add_filter(Transactions_Db_Table::PRIMARY_KEY, $transaction_id);
        }

        return $query_parameters;
    }

    public function get_schema() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'account',
            'type' => 'object',
            'properties' => array(
                'id' => array(
                    'description' => esc_html__('Unique transaction id number set by the bank.', 'bitcoin-bank'),
                    'type' => 'integer',
                    'readonly' => true,
                ),
                'time_stamp' => array(
                    'description' => esc_html__('Time and date for transaction.', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                'debit_account_id' => array(
                    'description' => esc_html__('Cash in the account.', 'bitcoin-bank'),
                    'type' => 'integer',
                ),
                'credit_account_id' => array(
                    'amount' => esc_html__('credit_account_id', 'bitcoin-bank'),
                    'type' => 'integer',
                ),
                'amount' => array(
                    'description' => esc_html__('amount', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                'transaction_type' => array(
                    'description' => esc_html__('transaction_type', 'bitcoin-bank'),
                    'type' => 'string',
                ),
                'reference_id' => array(
                    'description' => esc_html__('reference_id', 'bitcoin-bank'),
                    'type' => 'string',
                ),
            )
        );
        return $schema;
    }

    public function get_rest_database_mapping() {
        $mapping_table = array(
            'id' => Transactions_Db_Table::PRIMARY_KEY,
            'time_stamp' => Transactions_Db_Table::TIME_STAMP,
            'debit_account_id' => Transactions_Db_Table::DEBIT_ACCOUNT_ID,
            'credit_account_id' => Transactions_Db_Table::CREDIT_ACCOUNT_ID,
            'amount' => Transactions_Db_Table::AMOUNT,
            'transaction_type' => Transactions_Db_Table::TRANSACTION_TYPE,
            'reference_id' => Transactions_Db_Table::REFERENCE_ID,
        );
        return $mapping_table;
    }

}
