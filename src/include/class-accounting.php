<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

class Accounting
{
    /* Balance Accounts - Assets */
    const SYSTEM_NAME_ASSET_COLD_VAULT = 'cold_vault';
    const SYSTEM_NAME_ASSET_HOT_VAULT = 'hot_vault';
    const SYSTEM_NAME_ASSET_INVESTMENT = 'investment';

    /* Balance Accounts - Liabilities */

    /* Balance Statement - Equity */
    const SYSTEM_NAME_EQUITY_PAID_IN_CAPITAL = 'paid_in_equity';
    const SYSTEM_NAME_EQUITY_RETAINED_EARNINGS = 'retained_earnings';

    /* Income Statement - Revenues */
    const SYSTEM_NAME_RECEIVED_CHEQUE_FEE = 'client_cheque_fee';

    /* Income Statement - Expenses */
    const SYSTEM_NAME_EXPENSE_PERSONAL_COST = 'system_name_expense_personal_cost';
    const SYSTEM_NAME_EXPENSE_GENERAL_COST = 'system_name_expense_general_cost';
    const SYSTEM_NAME_EXPENSE_PAID_CHEQUE_FEE = 'system_name_expense_paid_cheque_fee';


    public static function check_user_has_bank_account( $user_login, $user )
    {
        $wp_user_id = $user->ID;
        $client_id = self::get_client_id($wp_user_id);
        self::get_client_default_account($client_id, $user_login);
    }

    static public function get_client_id($wp_user_id = null)
    {
        $client_id = false;

        if (is_null($wp_user_id))
        {
            $wp_user_id = get_current_user_id();
        }

        $client_data = new Clients_Db_Table();
        $result = $client_data->load_data(Clients_Db_Table::WP_USER_ID, $wp_user_id);
        if ($result === 1)
        {
            $client_id = $client_data->get_data(Clients_Db_Table::PRIMARY_KEY);
        }
        else if ( $result === false )
        {
            $user = get_user_by( 'id', $wp_user_id ); // 54 is a user ID
            $wp_user_login = $user->user_login;
            $client_id = self::create_client( $wp_user_id, $wp_user_login );
        }
        else
        {
            Debug_Logger::write_debug_error( 'Database table ' . $client_data::TABLE_NAME . ' has multiple wp_users ' . $wp_user_id . '.');
        }

        return $client_id;
    }

    static public function get_client_id_by_address($address) {
        $client_id = false;
        $wp_user = get_user_by('email', $address);
        if($wp_user != false)
        {
            $client_id = self::get_client_id($wp_user->ID);
        }
        return $client_id;
    }

    static public function get_client_default_account( $client_id, $username=null )
    {
        $account_id = false;
        if( $client_id )
        {
            $account_data = new Accounts_Db_Table();
            if ($account_data->load_data(Accounts_Db_Table::CLIENT_ID, $client_id))
            {
                $account_id = $account_data->get_data(Accounts_Db_Table::PRIMARY_KEY);
            }

            if( ! $account_id ) {
                if(!$username) {
                    $wp_user = wp_get_current_user();
                    $username = $wp_user->user_login;
                }
                $label = 'Saving account';
                self::create_client_account($client_id, $label);
            }
        }
        return $account_id;
    }

    static public function create_account( $client_id, $account_chart_id, $label )
    {
        $account_data = new Accounts_Db_Table();
        return $account_data->create_account( $client_id, $account_chart_id, $label);
    }

    static public function get_account_record( $account_id )
    {
        $account_data = new Accounts_Db_Table();
        return $account_data->get_account_record( $account_id );
    }

    static public function create_client( $wp_user_id, $username )
    {
        $money_api_url = Settings_Bank_Identity_Options::get_options(Settings_Bank_Identity_Options::MONEY_ACCOUNT_API_URL);
        $money_address = $username . '*' . $money_api_url;

        $client_data = new Clients_Db_Table();
        return $client_data->create_client( time(), $wp_user_id, $money_address );
    }

    static public function create_client_account($client_id, $label)
    {
        $account_defaults_data = new Account_Defaults_Db_Table();
        $account_defaults_data->load_data(Account_Defaults_Db_Table::SYSTEM_NAME, 'new_client_account');
        $account_chart_id = $account_defaults_data->get_data(Account_Defaults_Db_Table::ACCOUNT_CHART_ID);

        self::create_account( $client_id, $account_chart_id, $label );
        return $client_id;
    }

    static public function get_bank_owner_client_id() {
        return 0;
    }

    static public function get_account_owner( $account_id )
    {
        $client_id = false;

        if( $account_id)
        {
            $account_data = new Accounts_Db_Table();
            $client_id = $account_data->get_account_owner($account_id);
        }

        return $client_id;
    }

    static public function get_account_owner_wp_user( $account_id )
    {
        $wp_user_id = false;

        if( $account_id)
        {
            $client_id = self::get_account_owner($account_id);
            if($client_id)
            {
                $client_data = new Clients_Db_Table();
                $wp_user_id = $client_data->get_wp_user_id($client_id);
            }
        }

        return $wp_user_id;
    }

    static public function get_client_money_address( $client_id=null )
    {
        if(!$client_id) {
            $client_id = self::get_client_id();
        }

        $money_address = false;

        if( $client_id)
        {
            $account_data = new Clients_Db_Table();
            $money_address = $account_data->get_account_field($client_id, Clients_Db_Table::MONEY_ADDRESS);
        }

        return $money_address;
    }

    static public function account_exist($account_id)
    {
        $account_data = new Accounts_Db_Table();
        if ($account_data->load_data_id($account_id))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static public function get_account_balance($account_id)
    {
        $account_data = new Accounts_Db_Table();
        if ($account_data->load_data_id($account_id))
        {
            $balance = $account_data->get_data(Accounts_Db_Table::BALANCE);
            $account_chart_id = $account_data->get_data(Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID);
            $account_chart_data = new Account_Chart_Db_Table();
            $account_chart_main_type = $account_chart_data->get_main_account_type($account_chart_id);

            switch ($account_chart_main_type)
            {
                case Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES:
                case Account_Chart_Db_Table::MAIN_TYPE_BALANCE_EQUITY:
                    $balance *= -1;
                    break;
            }
        }
        else
        {
            $balance = 0;
        }
        return $balance;
    }

    static public function get_account_balance_object($account_id)
    {
        $account_data = new Accounts_Db_Table();
        if ($account_data->load_data_id($account_id))
        {
            $balance = $account_data->get_data_object(Accounts_Db_Table::BALANCE);
        }
        else
        {
            $balance = $account_data->get_data_object(Accounts_Db_Table::BALANCE);
        }
        return $balance;
    }

    static public function check_enough_balance($account_id, $value) {
        $balance = self::get_account_balance($account_id);
        if($balance >= $value) {
            return true;
        } else {
            return false;
        }
    }


    static public function get_amount_in_units($accont_id, $amount)
    {
        $amount = $amount * pow(10, 8);
        return intval($amount);
    }

    static protected function account_debit($account_id, $amount, $overdraft_allowed=false) {
        if (( gettype($account_id) == 'integer' )
            and ( gettype($amount) == 'integer' )
            and ( $amount > 0 ))
        {
            $account_data = new Accounts_Db_Table();
            if ($account_data->load_data_id($account_id))
            {
                $balance = $account_data->get_data(Accounts_Db_Table::BALANCE);

                $account_chart_id = $account_data->get_data(Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID);
                $account_chart_data = new Account_Chart_Db_Table();
                $account_chart_main_type = $account_chart_data->get_main_account_type($account_chart_id);

                $debit_ok = false;

                switch ($account_chart_main_type) {
                    case Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES:
                        $credit_balance = $balance * (-1);
                        if (($credit_balance >= $amount) or ($overdraft_allowed))
                        {
                            $debit_ok = true;
                        }
                        break;

                    default:
                        $debit_ok = true;
                        break;
                }

                if ($debit_ok)
                {
                    $balance += $amount;
                    $account_data->set_data(Accounts_Db_Table::BALANCE, $balance);
                    $account_data->save_data();

                    $account_chart_id = $account_data->get_data(Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID);
                    if ( gettype($account_id) == 'integer' ) {
                        $account_chart_data = new Account_Chart_Db_Table();
                        if ($account_chart_data->load_data_id($account_chart_id)) {
                            $grand_totals = $account_chart_data->get_data(Account_Chart_Db_Table::GRAND_TOTALS);
                            $grand_totals += $amount;
                            $account_chart_data->set_data(Account_Chart_Db_Table::GRAND_TOTALS, $grand_totals);
                            $account_chart_data->save_data();
                        }
                    }

                    return $balance;
                }
            }
        }
        return false;
    }

    static protected function account_credit($account_id, $amount, $overdraft_allowed=false) {
        if (( gettype($account_id) == 'integer' )
        and ( gettype($amount) == 'integer' )
        and ( $amount > 0 )) {
            $account_data = new Accounts_Db_Table();
            if ($account_data->load_data_id($account_id))
            {
                $balance = $account_data->get_data(Accounts_Db_Table::BALANCE);

                $account_chart_id = $account_data->get_data(Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID);
                $account_chart_data = new Account_Chart_Db_Table();
                $account_chart_main_type = $account_chart_data->get_main_account_type($account_chart_id);

                $credit_ok = false;

                switch ($account_chart_main_type) {
                    case Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET:
                        if (($balance >= $amount) or ($overdraft_allowed))
                        {
                            $credit_ok = true;
                        }
                        break;

                    default:
                        $credit_ok = true;
                        break;
                }

                if ($credit_ok)
                {
                    $balance -= $amount;
                    $account_data->set_data(Accounts_Db_Table::BALANCE, $balance);
                    $account_data->save_data();

                    $account_chart_id = $account_data->get_data(Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID);
                    if (gettype($account_id) == 'integer')
                    {
                        $account_chart_data = new Account_Chart_Db_Table();
                        if ($account_chart_data->load_data_id($account_chart_id))
                        {
                            $grand_totals = $account_chart_data->get_data(Account_Chart_Db_Table::GRAND_TOTALS);
                            $grand_totals -= $amount;
                            $account_chart_data->set_data(Account_Chart_Db_Table::GRAND_TOTALS, $grand_totals);
                            $account_chart_data->save_data();
                        }
                    }

                    return $balance;
                }
            }
        }
        return false;
    }

    static public function make_transaction(
        $debit_account_id,
        $credit_account_id,
        $amount,
        $timestamp,
        $transaction_type,
        $ref=null,
        $overdraft_allowed=false)
    {
        if ((gettype($credit_account_id) == 'integer')
            and (gettype($debit_account_id) == 'integer')
            and (gettype($amount) == 'integer')
            and ($amount > 0))
        {

            if (self::account_debit($debit_account_id, $amount, $overdraft_allowed) !== false)
            {
                if (self::account_credit($credit_account_id, $amount) !== false)
                {

                    $transaction = new Transactions_Db_Table();
                    $transaction->set_data(Transactions_Db_Table::TIME_STAMP, $timestamp);
                    $transaction->set_data(Transactions_Db_Table::CREDIT_ACCOUNT_ID, $credit_account_id);
                    $transaction->set_data(Transactions_Db_Table::DEBIT_ACCOUNT_ID, $debit_account_id);
                    $transaction->set_data(Transactions_Db_Table::AMOUNT, $amount);
                    $transaction->set_data(Transactions_Db_Table::TRANSACTION_TYPE, $transaction_type);
                    $transaction->set_data(Transactions_Db_Table::REFERENCE_ID, $ref);
                    $transaction->save_data();
                    $transaction_id = $transaction->get_data(Transactions_Db_Table::PRIMARY_KEY);
                    return $transaction_id;
                }
                else
                {
                    /* Could not credit account, revers debit. */
                    if (self::account_credit($debit_account_id, $amount) !== false)
                    {
                        /* Could not reverse debit. */
                        Debug_Logger::write_debug_error('Error in transaction', $debit_account_id, $amount);
                    }
                }
            }
        }

        return false;
    }

    static public function transaction_set_ref($transaction_id, $ref_id){
        $transaction_data = new Transactions_Db_Table();
        $transaction_data->load_data_id($transaction_id);
        $transaction_data->set_data(Transactions_Db_Table::REFERENCE_ID, $ref_id);
        $transaction_data->save_data();
        return true;
    }

    static public function get_cheque_reserved_account_id() {
        $system_accounts_data = new System_Accounts_Db_Table();
        if($system_accounts_data->load_data(System_Accounts_Db_Table::SYSTEM_NAME, 'client_cheque_reserved')) {
            $account_id = $system_accounts_data->get_data(System_Accounts_Db_Table::ACCOUNT_ID);
            return $account_id;
         } else {
            return false;
        }
    }

    static public function get_cheque_fee_account_id() {
        $system_accounts_data = new System_Accounts_Db_Table();
        if($system_accounts_data->load_data(System_Accounts_Db_Table::SYSTEM_NAME, 'client_cheque_fee')) {
            $account_id = $system_accounts_data->get_data(System_Accounts_Db_Table::ACCOUNT_ID);
            return $account_id;
        } else {
            return false;
        }
    }

    static public function make_cheque_transaction(
        $debit_account_id,
        $amount,
        $cheque_id,
        $fee=0,
        $time_stamp = null
    ) {
        $result = false;

        if (!$time_stamp) {
            $time_stamp = time();
        }

        $credit_account_id = self::get_cheque_reserved_account_id();
        $fee_credit_account_id = self::get_cheque_fee_account_id();

        $total_pay = $amount + $fee;
        $balance = self::get_account_balance($debit_account_id);

        if ($total_pay <= $balance) {
            $fee_transaction_id = true;
            if ($fee > 0) {
                $fee_transaction_id = self::make_transaction($debit_account_id, $fee_credit_account_id, $fee, $time_stamp, Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_FEE, $cheque_id);
            }

            if ($fee_transaction_id !== false) {
                $transaction_id = self::make_transaction($debit_account_id, $credit_account_id, $amount, $time_stamp, Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_DRAW, $cheque_id);
            }

            if (($fee_transaction_id === false) or ($transaction_id === false)) {
                /* Something went wrong. Revers only those transaction that was successful. */
                if (is_int($fee_transaction_id)) {
                    $reversed_fee_transaction_id = self::make_transaction($fee_credit_account_id, $debit_account_id, $fee, $timestamp, Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REVERSED2, $cheque_id);
                    if ($reversed_fee_transaction_id !== false) {
                        /* Could not reverse debit. */
                        Debug_Logger::write_debug_error('Error reversing fee transaction', $debit_account_id, $fee_credit_account_id, $fee);
                    }
                }

                if (is_int($transaction_id)) {
                    $reversed_transaction_id = self::make_transaction($credit_account_id, $debit_account_id, $amount, $timestamp, Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REVERSED2, $cheque_id);
                    if ($reversed_transaction_id !== false) {
                        /* Could not reverse debit. */
                        Debug_Logger::write_debug_error('Error reversing transaction', $debit_account_id, $credit_account_id, $amount);
                    }
                }
            } else {
                $result = true;
            }
        }
        return $result;
    }

    static public function make_cheque_claim_transaction($account_id, $amount, $cheque_id) {
        $cheque_account_id = self::get_cheque_reserved_account_id();
        $timestamp = time();
        $transaction_id = self::make_transaction($cheque_account_id, $account_id, $amount,$timestamp, Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_RECEIVE, $cheque_id);
        if ( $transaction_id !== false ) {
                return true;
        } else {
            $transaction_id = self::make_transaction($cheque_account_id, $account_id, $amount, $timestamp,Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REVERSED2, $cheque_id);
            if ( $transaction_id !== false ) {
                /* Could not reverse debit. */
                Debug_Logger::write_debug_error('Error receive reversing transaction', $account_id, $cheque_account_id, $amount);
            }
        }
        return false;
    }

    static public function make_cheque_expire_transaction($from_account_id, $to_account_id, $amount, $cheque_id, $time_stamp=null, $overdraft_allowed=false) {
        if(! $time_stamp ) {
            $timestamp = time();
        }
        $transaction_type = Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_EXPIRE;
        $ref = $cheque_id;
        $transaction_id = self::make_transaction($from_account_id, $to_account_id, $amount, $timestamp, $transaction_type, $ref, $overdraft_allowed);
        return $transaction_id;
    }

    static public function make_cheque_reject_transaction($from_account_id, $to_account_id, $amount, $cheque_id, $time_stamp=null, $overdraft_allowed=false) {
        if(! $time_stamp ) {
            $timestamp = time();
        }
        $transaction_type = Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REJECT;
        $ref = $cheque_id;
        $transaction_id = self::make_transaction($from_account_id, $to_account_id, $amount, $timestamp, $transaction_type, $ref, $overdraft_allowed);
        return $transaction_id;
    }

    static public function make_account_transfer($from_account_id, $to_account_id, $amount, $time_stamp=null, $overdraft_allowed=false) {
        if(! $time_stamp ) {
            $timestamp = time();
        }
        $transaction_type = Transactions_Db_Table::TRANSACTION_TYPE_ACCOUNT_TRANSFER;
        $ref = null;
        $transaction_id = self::make_transaction($from_account_id, $to_account_id, $amount, $timestamp, $transaction_type, $ref, $overdraft_allowed);
        return $transaction_id;
    }

    static public function get_main_account_chart_list($account_main_type ){
        $account_list = array();

        $account_chart_data = new Account_Chart_Db_Table();
        $account_chart_list = $account_chart_data->get_main_account_chart_list( $account_main_type );

        $account_data = new Accounts_Db_Table();
        foreach($account_chart_list as $account_chart_type)
        {
            $account_type = $account_chart_type[Account_Chart_Db_Table::PRIMARY_KEY];
            $list = $account_data->get_accounts_of_chart_types( $account_type );
            $account_list = array_merge($account_list, $list);
        }

        return $account_list;
    }

    static public function get_sub_account_chart_list($account_main_type ){
        $account_list = array();

        $account_chart_data = new Account_Chart_Db_Table();
        $account_chart_list = $account_chart_data->get_sub_account_chart_list( $account_main_type );

        $account_data = new Accounts_Db_Table();
        foreach($account_chart_list as $account_chart_type)
        {
            $account_type = $account_chart_type[Account_Chart_Db_Table::PRIMARY_KEY];
            $list = $account_data->get_accounts_of_chart_types( $account_type );
            $account_list = array_merge($account_list, $list);
        }

        return $account_list;
    }

    static public function balance_calculate_equity() {
        $account_chart_data = new Account_Chart_Db_Table();
        $asset = $account_chart_data->calculate_main_account_chart_sum(Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET);
        $liabilities = $account_chart_data->calculate_main_account_chart_sum(Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES);
        $equity = $asset + $liabilities;
        return $equity;
    }

    static public function get_debit_credit_type( $account_chart_id ) {
        $account_chart_data = new Account_Chart_Db_Table();
        $account_chart_data->load_data_id($account_chart_id);
        $sub_account_type = $account_chart_data->get_data(Account_Chart_Db_Table::SUB_ACCOUNT_TYPE);
        $debit_credit_type = Account_Chart_Db_Table::get_debit_credit_type($sub_account_type);
        return $debit_credit_type;
    }
}
