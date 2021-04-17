<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

class Bank_Installer {
    static $default_account_chart = array(
        /* Balance Accounts - Assets */
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_HOT_VAULT,
            Account_Chart_Db_Table::NUMBER => '101',
            Account_Chart_Db_Table::LABEL => 'Cash in hot vault',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_CASH_HOT_VAULT,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_COLD_VAULT,
            Account_Chart_Db_Table::NUMBER => '102',
            Account_Chart_Db_Table::LABEL => 'Cash in cold vault',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_CASH_COLD_VAULT,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_CLIENT_CREDIT,
            Account_Chart_Db_Table::NUMBER => '130',
            Account_Chart_Db_Table::LABEL => 'Credit given to clients',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_CLIENT_CREDIT,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_INVESTMENT,
            Account_Chart_Db_Table::NUMBER => '150',
            Account_Chart_Db_Table::LABEL => 'Equipment, investments and other assets',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_INVESTMENT,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        /* Balance Accounts - Liabilities */
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => 'client_accounts',
            Account_Chart_Db_Table::NUMBER => '210',
            Account_Chart_Db_Table::LABEL => 'Client Accounts',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_LIABILITIES_CLIENT_SAVINGS,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => 'client_cheque_reserved',
            Account_Chart_Db_Table::NUMBER => '210',
            Account_Chart_Db_Table::LABEL => 'Cheque Reserved',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_LIABILITIES_CHEQUE_RESERVED,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        /* Balance Accounts - Equity */
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EQUITY_PAID_IN_CAPITAL,
            Account_Chart_Db_Table::NUMBER => '310',
            Account_Chart_Db_Table::LABEL => 'Paid in equity',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_EQUITY,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_EQUITY_PAID_IN_CAPITAL,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EQUITY_RETAINED_EARNINGS,
            Account_Chart_Db_Table::NUMBER => '320',
            Account_Chart_Db_Table::LABEL => 'Retained Equity',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_BALANCE_EQUITY,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_BALANCE_EQUITY_RETAINED_EARNINGS,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        /* Income Statement - Revenues */
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_RECEIVED_CHEQUE_FEE,
            Account_Chart_Db_Table::NUMBER => '410',
            Account_Chart_Db_Table::LABEL => 'Cheque Fee',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_INCOME_REVENUES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_INCOME_REVENUES,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => 'transaction_fee',
            Account_Chart_Db_Table::NUMBER => '411',
            Account_Chart_Db_Table::LABEL => 'Transaction Fees',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_INCOME_REVENUES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_INCOME_REVENUES,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        /* Income Statement - Expenses */
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EXPENSE_PERSONAL_COST,
            Account_Chart_Db_Table::NUMBER => '401',
            Account_Chart_Db_Table::LABEL => 'Personal costs',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EXPENSE_GENERAL_COST,
            Account_Chart_Db_Table::NUMBER => '411',
            Account_Chart_Db_Table::LABEL => 'General and administrative costs',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EXPENSE_PAID_CHEQUE_FEE,
            Account_Chart_Db_Table::NUMBER => '412',
            Account_Chart_Db_Table::LABEL => 'Cheque Fees',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
        array(
            Account_Chart_Db_Table::SYSTEM_NAME => 'transaction_fee',
            Account_Chart_Db_Table::NUMBER => '413',
            Account_Chart_Db_Table::LABEL => 'Transaction Fees',
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE => Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE => Account_Chart_Db_Table::SUB_TYPE_INCOME_EXPENSES,
            Account_Chart_Db_Table::GRAND_TOTALS => 0
        ),
    );

    static $default_account_defaults = array(
        array(
            Account_Defaults_Db_Table::SYSTEM_NAME => Account_Defaults_Db_Table::DEFAULT_NEW_CLIENT_SAVING_ACCOUNT,
            Account_Defaults_Db_Table::LABEL => 'Client saving account.',
            Account_Defaults_Db_Table::DESCRIPTION => 'Default saving accounts for new clients.',
            'account_chart' => 'client_accounts'
        ),
        array(
            Account_Defaults_Db_Table::SYSTEM_NAME => Account_Defaults_Db_Table::DEFAULT_NEW_CLIENT_CREDIT_ACCOUNT,
            Account_Defaults_Db_Table::LABEL => 'Client credit account.',
            Account_Defaults_Db_Table::DESCRIPTION => 'Default credit accounts for new clients.',
            'account_chart' => Accounting::SYSTEM_NAME_ASSET_CLIENT_CREDIT
        ),
    );

    static $default_system_accounts = array(
        /* Balance Accounts - Assets */
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_COLD_VAULT,
            System_Accounts_Db_Table::LABEL => 'Cold storage vault.',
            System_Accounts_Db_Table::DESCRIPTION => 'Default accounts for cash in cold vault.',
            'account_chart' => Accounting::SYSTEM_NAME_ASSET_COLD_VAULT
        ),
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_HOT_VAULT,
            System_Accounts_Db_Table::LABEL => 'Hot storage vault.',
            System_Accounts_Db_Table::DESCRIPTION => 'Default accounts for cash stored in hot vault.',
            'account_chart' => Accounting::SYSTEM_NAME_ASSET_HOT_VAULT
        ),
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_ASSET_INVESTMENT,
            System_Accounts_Db_Table::LABEL => 'General investments',
            System_Accounts_Db_Table::DESCRIPTION => 'Equipment, investments and other assets.',
            'account_chart' => Accounting::SYSTEM_NAME_ASSET_INVESTMENT
        ),
        /* Balance Accounts - Liabilities */
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => 'client_cheque_reserved',
            System_Accounts_Db_Table::LABEL => 'Cheque Reserved.',
            System_Accounts_Db_Table::DESCRIPTION => 'Default Balance account for holding cash reserved for sent cheques.',
            'account_chart' => 'client_cheque_reserved'
        ),
        /* Balance Accounts - Equity */
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EQUITY_PAID_IN_CAPITAL,
            System_Accounts_Db_Table::LABEL => 'Paid in equity',
            System_Accounts_Db_Table::DESCRIPTION => 'Default Balance account for paid in capital.',
            'account_chart' => Accounting::SYSTEM_NAME_EQUITY_PAID_IN_CAPITAL
        ),
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EQUITY_RETAINED_EARNINGS,
            System_Accounts_Db_Table::LABEL => 'Retained earnings',
            System_Accounts_Db_Table::DESCRIPTION => 'Default Balance account for retained earrings.',
            'account_chart' => Accounting::SYSTEM_NAME_EQUITY_RETAINED_EARNINGS
        ),

        /* Income Statement - Revenues */
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_RECEIVED_CHEQUE_FEE,
            System_Accounts_Db_Table::LABEL => 'Client Cheque Fee',
            System_Accounts_Db_Table::DESCRIPTION => 'Default income accounts for received cheque fees.',
            'account_chart' => Accounting::SYSTEM_NAME_RECEIVED_CHEQUE_FEE
        ),
        /* Income Statement - Expenses */
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EXPENSE_PERSONAL_COST,
            System_Accounts_Db_Table::LABEL => 'Personal costs',
            System_Accounts_Db_Table::DESCRIPTION => 'Default expense account for paid personal costs.',
            'account_chart' => Accounting::SYSTEM_NAME_EXPENSE_PERSONAL_COST
        ),
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EXPENSE_GENERAL_COST,
            System_Accounts_Db_Table::LABEL => 'General and administrative costs',
            System_Accounts_Db_Table::DESCRIPTION => 'Default expense account for general and administrative costs.',
            'account_chart' => Accounting::SYSTEM_NAME_EXPENSE_GENERAL_COST
        ),
        array(
            System_Accounts_Db_Table::SYSTEM_NAME => Accounting::SYSTEM_NAME_EXPENSE_PAID_CHEQUE_FEE,
            System_Accounts_Db_Table::LABEL => 'Paid Cheque Fee',
            System_Accounts_Db_Table::DESCRIPTION => 'Default expense account for paid cheque fees.',
            'account_chart' => Accounting::SYSTEM_NAME_EXPENSE_PAID_CHEQUE_FEE
        ),
    );

    static $default_other_accounts = array(
        /* Balance Accounts - Assets */
        array(
            System_Accounts_Db_Table::LABEL => 'Equipment, investments and other assets.',
            'account_chart' => Accounting::SYSTEM_NAME_ASSET_INVESTMENT
        ),
    );

    static public function install_account_chart (){
        $account_chart_data = new Account_Chart_Db_Table();
        $account_chart_data->load_all_data();
        $account_chart_data_list = $account_chart_data->get_copy_all_data();
        $resulting_account_chart = array();

        foreach( self::$default_account_chart as $default_chart_line ) {
            $system_name = $default_chart_line[Account_Chart_Db_Table::SYSTEM_NAME];
            $found = false;
            foreach($account_chart_data_list as $account_chart_data_line) {
                if ( $account_chart_data_line[Account_Chart_Db_Table::SYSTEM_NAME] === $system_name ) {
                    $resulting_account_chart[$system_name] = $account_chart_data_line[Account_Chart_Db_Table::PRIMARY_KEY];
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $account_chart_data->clear_all_data();
                $account_chart_data->set_data_record( $default_chart_line );
                $account_chart_data->save_data();
                $resulting_account_chart[$system_name] = $account_chart_data->get_data(Account_Chart_Db_Table::PRIMARY_KEY);
            }
        }

        return $resulting_account_chart;
    }

    static public function install_acount_defaults ( $account_chart ) {
        $account_defaults_data = new Account_Defaults_Db_Table();
        $account_defaults_data->load_all_data();
        $account_defaults_data_list = $account_defaults_data->get_copy_all_data();

        foreach( self::$default_account_defaults as $default_account_line ) {
            $system_name = $default_account_line[Account_Defaults_Db_Table::SYSTEM_NAME];
            $found = false;
            foreach($account_defaults_data_list as $account_defaults_data_line) {
                if ( $account_defaults_data_line[Account_Defaults_Db_Table::SYSTEM_NAME] === $system_name ) {
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $account_chart_system_name = $default_account_line['account_chart'];
                unset($default_account_line['account_chart']); /* Must remove, otherwise it would  confuse database. */
                $account_chart_id = $account_chart[$account_chart_system_name];
                $default_account_line[Account_Defaults_Db_Table::ACCOUNT_CHART_ID] = $account_chart_id;

                $account_defaults_data->clear_all_data();
                $account_defaults_data->set_data_record( $default_account_line );
                $account_defaults_data->save_data();
            }
        }
    }

    static public function install_system_accounts ( $account_chart ) {
        $system_account_data = new System_Accounts_Db_Table();
        $system_account_data->load_all_data();
        $system_account_data_list = $system_account_data->get_copy_all_data();

        foreach( self::$default_system_accounts as $default_system_accounts_line ) {
            $system_name = $default_system_accounts_line[System_Accounts_Db_Table::SYSTEM_NAME];
            $found = false;
            foreach($system_account_data_list as $system_account_data_line) {
                if ( $system_account_data_line[System_Accounts_Db_Table::SYSTEM_NAME] === $system_name ) {
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $account_chart_system_name = $default_system_accounts_line['account_chart'];
                unset($default_system_accounts_line['account_chart']); /* Must remove, otherwise it would  confuse database. */
                $account_chart_id = $account_chart[$account_chart_system_name];

                $bank_owner_client_id = Accounting::get_bank_owner_client_id();
                $label = $default_system_accounts_line[System_Accounts_Db_Table::LABEL];
                $account_id = Accounting::create_account($bank_owner_client_id, $account_chart_id, $label);

                $default_system_accounts_line[System_Accounts_Db_Table::ACCOUNT_ID] = $account_id;

                $system_account_data->clear_all_data();
                $system_account_data->set_data_record( $default_system_accounts_line );
                $system_account_data->save_data();
            } else {

                $account_id = $system_account_data_line[System_Accounts_Db_Table::ACCOUNT_ID];
                $account_record = Accounting::get_account_record($account_id);
                if (!$account_record)
                {
                    $bank_owner_client_id = Accounting::get_bank_owner_client_id();

                    $account_chart_system_name = $default_system_accounts_line['account_chart'];
                    $account_chart_id = $account_chart[$account_chart_system_name];

                    $label = $default_system_accounts_line[System_Accounts_Db_Table::LABEL];
                    $account_id = Accounting::create_account($bank_owner_client_id, $account_chart_id, $label);

                    $system_account_id = $system_account_data_line[System_Accounts_Db_Table::PRIMARY_KEY];
                    $system_account_data->load_data_id($system_account_id);
                    $system_account_data->set_data( System_Accounts_Db_Table::ACCOUNT_ID, $account_id );
                    $system_account_data->save_data();
                }
            }

        }
    }

    static public function install_bank () {
        $account_chart = self::install_account_chart();
        self::install_acount_defaults($account_chart);
        self::install_system_accounts($account_chart);
    }
}
