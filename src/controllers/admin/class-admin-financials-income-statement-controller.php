<?php
/** Bitcoin Bank plugin for WordPress.
 *
 *  Copyright (C) 2018 Arild Hegvik
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
class Admin_Financials_Income_Statement_Controller extends Admin_Controller {

	/**
	 * Construction.
	 */
	public function __construct() {
		parent::__construct( 'BCQ_BitcoinBank\Account_Chart_Db_Table', 'BCQ_BitcoinBank\Admin_Income_Statement_View' );

		$this->tab_name = 'income';
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

        $parameters['headers'] = array('Code', 'Accounts', 'Value');

        $total_revenue=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_INCOME_REVENUES);
        $sub_account_type = Account_Chart_Db_Table::MAIN_TYPE_INCOME_REVENUES;
        $href = $account_page_url . '&main_account=' . strval($sub_account_type);
        $a = new A('Revenues', $href);
        $parameters['income_headers'] = array(array('', $a, ''));
        $parameters['income_accounts'] = $this->model->get_copy_all_data($colums);
        foreach($parameters['income_accounts'] as $idx => $account_data) {
            $total_revenue += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
            $line_label = $parameters['income_accounts'][$idx][Account_Chart_Db_Table::LABEL];
            $sub_account_type = $account_data[Account_Chart_Db_Table::SUB_ACCOUNT_TYPE];
            $href = $account_page_url . '&sub_account=' . strval($sub_account_type);
            $a = new A($line_label, $href);
            $parameters['income_accounts'][$idx][Account_Chart_Db_Table::LABEL] = $a;
            $parameters['income_accounts'][$idx][Account_Chart_Db_Table::GRAND_TOTALS] = new Crypto_currency_type($credit_type_account_metadata, null, $account_data[Account_Chart_Db_Table::GRAND_TOTALS]);
            unset($parameters['income_accounts'][$idx][Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE]);
            unset($parameters['income_accounts'][$idx][Account_Chart_Db_Table::SUB_ACCOUNT_TYPE]);
        }
        $total_revenue_obj = new Crypto_currency_type($credit_type_account_metadata, null, $total_revenue);
        $parameters['income_sum'] = array(array('', 'Sum Revenues', $total_revenue_obj));

        $total_expenses=0;
        $this->model->load_data(Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE, Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES);
        $sub_account_type = Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES;
        $href = $account_page_url . '&main_account=' . strval($sub_account_type);
        $a = new A('Expenses', $href);
        $parameters['expense_header'] = array(array('', $a, ''));
        $parameters['expense_accounts'] = $this->model->get_copy_all_data($colums);
        foreach($parameters['expense_accounts'] as $idx => $account_data) {
            $total_expenses += $account_data[Account_Chart_Db_Table::GRAND_TOTALS];
            $line_label = $parameters['expense_accounts'][$idx][Account_Chart_Db_Table::LABEL];
            $sub_account_type = $account_data[Account_Chart_Db_Table::SUB_ACCOUNT_TYPE];
            $href = $account_page_url . '&sub_account=' . strval($sub_account_type);
            $a = new A($line_label, $href);
            $parameters['expense_accounts'][$idx][Account_Chart_Db_Table::LABEL] = $a;
            $parameters['expense_accounts'][$idx][Account_Chart_Db_Table::GRAND_TOTALS] = new Crypto_currency_type($credit_type_account_metadata, null, $account_data[Account_Chart_Db_Table::GRAND_TOTALS]);
            unset($parameters['expense_accounts'][$idx][Account_Chart_Db_Table::MAIN_ACCOUNT_TYPE]);
            unset($parameters['expense_accounts'][$idx][Account_Chart_Db_Table::SUB_ACCOUNT_TYPE]);
        }
        $total_expenses_obj = new Crypto_currency_type($credit_type_account_metadata, null, $total_expenses);
        $parameters['expense_sum'] = array(array('', 'Sum Expenses', $total_expenses_obj));

        $protfit = $total_revenue + $total_expenses;
        $protfit_obj = new Crypto_currency_type($credit_type_account_metadata, null, $protfit);
        $parameters['profit'] = array(array('', 'Profit/Loss', $protfit_obj));


        return parent::draw_view( $parameters );
    }

}
