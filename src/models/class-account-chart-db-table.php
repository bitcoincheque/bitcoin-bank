<?php
/** Bitcoin Bank plugin for WordPress.
 *
 *  Copyright (C) 2021 Arild Hegvik
 *
 *  GNU GENERAL PUBLIC LICENSE (GNU GPLv3)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Bitcoin-Bank
 */

namespace BCQ_BitcoinBank;

defined('ABSPATH') || exit;

use WP_PluginFramework\Models\Active_Record;
use WP_PluginFramework\Utils\Debug_Logger;


/**
 * Summary.
 *
 * Description.
 */
class Account_Chart_Db_Table extends Active_Record
{

    /* Database table name: */
    const TABLE_NAME = 'bcq_bitcoin_bank_account_chart';

    /* List of table field names: */
    const SYSTEM_NAME = 'system_name';
    const NUMBER = 'number';
    const LABEL = 'label';
    const MAIN_ACCOUNT_TYPE = 'main_account_type';
    const SUB_ACCOUNT_TYPE = 'sub_account_type';
    const GRAND_TOTALS = 'grand_totals';

    /* General ledger main account types: */
    const MAIN_TYPE_NOT_SET = 0;

    const MAIN_TYPE_BALANCE_ASSET = 1;
    const SUB_TYPE_BALANCE_ASSET_CASH_HOT_VAULT = 101;
    const SUB_TYPE_BALANCE_ASSET_CASH_COLD_VAULT = 102;
    const SUB_TYPE_BALANCE_ASSET_CHEQUE_RECEIVABLE = 103;
    const SUB_TYPE_BALANCE_ASSET_RECEIVABLE = 104;
    const SUB_TYPE_BALANCE_ASSET_INVESTMENT = 105;
    const SUB_TYPE_BALANCE_ASSET_CLIENT_CREDIT = 106;

    const MAIN_TYPE_BALANCE_LIABILITIES = 2;
    const SUB_TYPE_BALANCE_LIABILITIES_CLIENT_SAVINGS = 201;
    const SUB_TYPE_BALANCE_LIABILITIES_CHEQUE_RESERVED = 202;
    const SUB_TYPE_BALANCE_LIABILITIES_CLIENT_PAYABLES = 203;

    const MAIN_TYPE_BALANCE_EQUITY = 3;
    const SUB_TYPE_BALANCE_EQUITY_PAID_IN_CAPITAL = 301;
    const SUB_TYPE_BALANCE_EQUITY_RETAINED_EARNINGS = 302;

    const MAIN_TYPE_INCOME_REVENUES = 4;
    const SUB_TYPE_INCOME_REVENUES = 401;

    const MAIN_TYPE_INCOME_EXPENSES = 5;
    const SUB_TYPE_INCOME_EXPENSES = 501;

    const MAIN_TYPE_INCOME_GAINES = 6;
    const SUB_TYPE_INCOME_GAINES = 601;

    const MAIN_TYPE_INCOME_LOSSES = 7;
    const SUB_TYPE_INCOME_LOSSES = 701;

    const DEBIT_ACCOUNT = 0;
    const CREDIT_ACCOUNT = 1;

    /* Metadata describing database fields and data properties: */
    static $meta_data = array(
        self::SYSTEM_NAME => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'System Name'
        ),
        self::NUMBER => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Code'
        ),
        self::LABEL => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Label'
        ),
        self::MAIN_ACCOUNT_TYPE => array(
            'data_type' => 'Unsigned_Integer_Type',
            'default_value' => self::MAIN_TYPE_NOT_SET,
            'label' => 'Account Type'
        ),
        self::SUB_ACCOUNT_TYPE => array(
            'data_type' => 'Unsigned_Integer_Type',
            'default_value' => null,
            'label' => 'Account Type'
        ),
        self::GRAND_TOTALS => array(
            'data_type' => 'Currency_Type',
            'default_value' => 0,
            'decimals' => 8,
            'unit' => 'BTC',
            'label' => 'Grand Totals'
        )
    );

    protected function create_data_object( $metadata, $key, $value = null, $record = null ) {
        $data_object = parent::create_data_object( $metadata, $key, $value );
        if($data_object instanceof Crypto_currency_type) {
            $account_chart_type = $record[self::SUB_ACCOUNT_TYPE];
            $debit_credit_type = Accounting::get_debit_credit_type($account_chart_type);
            $data_object->set_property('debit_credit_type', $debit_credit_type);
        }
        return $data_object;
    }

    public function get_main_account_type($account_chart_id) {
        $main_account_type = false;
        if ($this->load_data_id($account_chart_id))
        {
            $main_account_type = $this->get_data(self::MAIN_ACCOUNT_TYPE);
        }
        return $main_account_type;
    }

    public function get_sub_account_type($account_chart_id) {
        $main_account_type = false;
        if ($this->load_data_id($account_chart_id))
        {
            $main_account_type = $this->get_data(self::SUB_ACCOUNT_TYPE);
        }
        return $main_account_type;
    }

    public function get_main_account_chart_list($main_account_type) {
        $account_chart_lists = array();
        if ($this->load_data(self::MAIN_ACCOUNT_TYPE, $main_account_type))
        {
            $account_chart_lists = $this->get_copy_all_data();
        }
        return $account_chart_lists;
    }

    public function get_sub_account_chart_list($sub_account_type) {
        $account_chart_lists = array();
        if ($this->load_data(self::SUB_ACCOUNT_TYPE, $sub_account_type))
        {
            $account_chart_lists = $this->get_copy_all_data();
        }
        return $account_chart_lists;
    }

    static public function get_debit_credit_type( $account_chart_type ) {
        $account_type = false;
        switch($account_chart_type)
        {
            case self::MAIN_TYPE_BALANCE_ASSET:
            case self::SUB_TYPE_BALANCE_ASSET_CASH_HOT_VAULT:
            case self::SUB_TYPE_BALANCE_ASSET_CASH_COLD_VAULT:
            case self::SUB_TYPE_BALANCE_ASSET_CHEQUE_RECEIVABLE:
            case self::SUB_TYPE_BALANCE_ASSET_RECEIVABLE:
            case self::SUB_TYPE_BALANCE_ASSET_INVESTMENT:
            case self::SUB_TYPE_BALANCE_ASSET_CLIENT_CREDIT:
                $account_type = self::DEBIT_ACCOUNT;
                break;

            case self::MAIN_TYPE_BALANCE_LIABILITIES:
            case self::SUB_TYPE_BALANCE_LIABILITIES_CLIENT_SAVINGS:
            case self::SUB_TYPE_BALANCE_LIABILITIES_CHEQUE_RESERVED:
            case self::SUB_TYPE_BALANCE_LIABILITIES_CLIENT_PAYABLES:

            case self::MAIN_TYPE_BALANCE_EQUITY:
            case self::SUB_TYPE_BALANCE_EQUITY_PAID_IN_CAPITAL:
            case self::SUB_TYPE_BALANCE_EQUITY_RETAINED_EARNINGS:

            case self::MAIN_TYPE_INCOME_REVENUES:
            case self::SUB_TYPE_INCOME_REVENUES:

            case self::MAIN_TYPE_INCOME_EXPENSES:
            case self::SUB_TYPE_INCOME_EXPENSES:

            case self::MAIN_TYPE_INCOME_GAINES:
            case self::SUB_TYPE_INCOME_GAINES:

            case self::MAIN_TYPE_INCOME_LOSSES:
            case self::SUB_TYPE_INCOME_LOSSES:
                $account_type = self::CREDIT_ACCOUNT;
                break;

            default:
                Debug_Logger::write_debug_error('Missing account type definitions for ', $account_type );
                break;
        }

        return $account_type;
    }

    public function calculate_main_account_chart_sum($main_account_type) {
        $sum = 0;
        $account_chart_lists = $this->get_main_account_chart_list($main_account_type);
        foreach ($account_chart_lists as $account_chart_line) {
            $sum += $account_chart_line[self::GRAND_TOTALS];
        }
        return $sum;
    }

}
