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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Controllers\Admin_Controller;
use WP_PluginFramework\HtmlElements\A;
use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Financials_Balance_Sheet_Controller extends Admin_Controller {

	/**
	 * Construction.
	 */
	public function __construct() {
		parent::__construct( 'BCQ_BitcoinBank\Account_Chart_Db_Table', 'BCQ_BitcoinBank\Admin_Balance_Sheet_View' );

		$this->tab_name = 'balance';
	}

    public function draw_view( $parameters = null )
    {
        $colums = array(
            Account_Chart_Db_Table::NUMBER,
            Account_Chart_Db_Table::LABEL,
            Account_Chart_Db_Table::GRAND_TOTALS,
            Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE,
            Account_Chart_Db_Table::SUB_ACCOUNT_TYPE
        );

        $credit_type_account_metadata = array(
            'debit_credit_type' => Account_Chart_Db_Table::CREDIT_ACCOUNT
        );

        $site_url = get_site_url();
        $account_page_url = $site_url . '/wp-admin/admin.php?page=bcq-admin-accounts&tab=accounts';
        $income_page_url = $site_url . '/wp-admin/admin.php?page=bcq-admin-financials&tab=income';

        $parameters['headers'] = array('Code', 'Accounts', 'Value');

        $totals_asset=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET);
        $sub_account_type = Account_Chart_Db_Table::MAIN_TYPE_BALANCE_ASSET;
        $href = $account_page_url . '&main_account=' . strval($sub_account_type);
        $a = new A('Asset', $href);
        $parameters['asset_headers'] = array(array('', $a, ''));
        $parameters['asset_accounts'] = $this->model->get_copy_all_data($colums);
        foreach($parameters['asset_accounts'] as $idx => $account_data) {
            $line_label = $parameters['asset_accounts'][$idx][Account_Chart_Db_Table::LABEL];
            $sub_account_type = $account_data[Account_Chart_Db_Table::SUB_ACCOUNT_TYPE];
            $href = $account_page_url . '&sub_account=' . strval($sub_account_type);
            $a = new A($line_label, $href);
            $parameters['asset_accounts'][$idx][Account_Chart_Db_Table::LABEL] = $a;
            $totals_asset += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
            $parameters['asset_accounts'][$idx][Account_Chart_Db_Table::GRAND_TOTALS] = new Crypto_currency_type(null, null, $account_data[Account_Chart_Db_Table::GRAND_TOTALS]);
            unset($parameters['asset_accounts'][$idx][Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE]);
            unset($parameters['asset_accounts'][$idx][Account_Chart_Db_Table::SUB_ACCOUNT_TYPE]);
        }
        $asset_obj = new Crypto_currency_type(null, null, $totals_asset);
        $parameters['asset_totals'] = array(array('', 'Total Assets', $asset_obj));


        $total_liabilities=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES);
        $sub_account_type = Account_Chart_Db_Table::MAIN_TYPE_BALANCE_LIABILITIES;
        $href = $account_page_url . '&main_account=' . strval($sub_account_type);
        $a = new A('Liabilities', $href);
        $parameters['liabilities_headers'] = array(array('', $a, ''));
        $parameters['liabilities_accounts'] = $this->model->get_copy_all_data($colums);
        foreach($parameters['liabilities_accounts'] as $idx => $account_data) {
            $line_label = $parameters['liabilities_accounts'][$idx][Account_Chart_Db_Table::LABEL];
            $sub_account_type = $account_data[Account_Chart_Db_Table::SUB_ACCOUNT_TYPE];
            $href = $account_page_url . '&sub_account=' . strval($sub_account_type);
            $a = new A($line_label, $href);
            $parameters['liabilities_accounts'][$idx][Account_Chart_Db_Table::LABEL] = $a;
            $total_liabilities += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
            $parameters['liabilities_accounts'][$idx][Account_Chart_Db_Table::GRAND_TOTALS] = new Crypto_currency_type($credit_type_account_metadata, null, $account_data[Account_Chart_Db_Table::GRAND_TOTALS]);
            unset($parameters['liabilities_accounts'][$idx][Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE]);
            unset($parameters['liabilities_accounts'][$idx][Account_Chart_Db_Table::SUB_ACCOUNT_TYPE]);
        }

        $total_liabilities_obj = new Crypto_currency_type($credit_type_account_metadata, null, $total_liabilities);
        $parameters['liabilities_totals'] = array(array('', 'Total Liabilities', $total_liabilities_obj));


        $total_revenue=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_INCOME_REVENUES);
        $parameters['income_headers'] = array(array('', 'Revenue', ''));
        $parameters['income_accounts'] = $this->model->get_copy_all_data($colums);
        foreach($parameters['income_accounts'] as $account_data) {
            $total_revenue += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
        }

        $total_expenses=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES);
        $parameters['expense_header'] = array(array('Expenses', ''));
        $parameters['expense_accounts'] = $this->model->get_copy_all_data($colums);
        foreach($parameters['expense_accounts'] as $account_data) {
            $total_expenses += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
        }
        $parameters['expense_sum'] = array(array('', 'Sum Expenses', $total_expenses));

        $profit = $total_revenue + $total_expenses;

        $profit_line = array(
            '0' => '',
            'label' => 'Accumulated Profit/Loss',
            'grand_totals' => $profit
        );

        $total_equity=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_BALANCE_EQUITY);
        $sub_account_type = Account_Chart_Db_Table::MAIN_TYPE_BALANCE_EQUITY;
        $href = $account_page_url . '&main_account=' . strval($sub_account_type);
        $a = new A('Equity', $href);
        $parameters['equity_headers'] = array(array('', $a, ''));
        $parameters['equity_accounts'] = $this->model->get_copy_all_data($colums);
        array_push($parameters['equity_accounts'], $profit_line);
        foreach($parameters['equity_accounts'] as $idx => $account_data) {
            $line_label = $parameters['equity_accounts'][$idx][Account_Chart_Db_Table::LABEL];
            $a = new A($line_label, $income_page_url);
            $parameters['equity_accounts'][$idx][Account_Chart_Db_Table::LABEL] = $a;
            $total_equity += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
            $parameters['equity_accounts'][$idx][Account_Chart_Db_Table::GRAND_TOTALS] = new Crypto_currency_type($credit_type_account_metadata, null, $account_data[Account_Chart_Db_Table::GRAND_TOTALS]);
            unset($parameters['equity_accounts'][$idx][Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE]);
            unset($parameters['equity_accounts'][$idx][Account_Chart_Db_Table::SUB_ACCOUNT_TYPE]);
        }
        $total_equity_obj = new Crypto_currency_type($credit_type_account_metadata, null, $total_equity);
        $parameters['equity_totals'] = array(array('', 'Total Equity', $total_equity_obj));

        $total_obj = new Crypto_currency_type($credit_type_account_metadata, null, $total_liabilities + $total_equity);
        $parameters['total_balance'] = array(array('', 'Total Liabilities and Equity', $total_obj));


        return parent::draw_view( $parameters );
    }
}
