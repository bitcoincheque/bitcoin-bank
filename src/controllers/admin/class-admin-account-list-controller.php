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

use WP_PluginFramework\Controllers\List_Controller;
use WP_PluginFramework\DataTypes\Currency_Type;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Account_List_Controller extends List_Controller
{
    /** @var Operation_Account_Transfer_View */
    public $view;

    public function __construct()
    {
        $model_class = 'BCQ_BitcoinBank\Accounts_Db_Table';
        parent::__construct($model_class);
    }

    protected function load_model_values( $values = array() ) {
        $account_chart_list = false;

        $account_chart_data = new Account_Chart_Db_Table();

        if(isset($_GET['main_account'])) {
            $list_value = $_GET['main_account'];
            $list_value = filter_var( $list_value, FILTER_SANITIZE_NUMBER_INT );
            $list_value = intval($list_value);

            $account_chart_list = $account_chart_data->get_main_account_chart_list($list_value);
        }
        else if(isset($_GET['sub_account']))
        {
            $list_value = $_GET['sub_account'];
            $list_value = filter_var( $list_value, FILTER_SANITIZE_NUMBER_INT );
            $list_value = intval($list_value);

            $account_chart_list = $account_chart_data->get_sub_account_chart_list($list_value);
        }

        if ( isset( $this->model ) ) {
            if($account_chart_list === false)
            {
                $this->model->load_data();
                $values['data_objects'] = $this->model->get_all_data_objects();
                $values['meta_data'] = $this->model->get_meta_data_list();
            } else {
                $values['data_objects'] = array();
                foreach($account_chart_list as $account_chart_line)
                {
                    $condition_value = $account_chart_line[Account_Chart_Db_Table::PRIMARY_KEY];
                    $this->model->load_data(Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID, $condition_value);
                    $account_line = $this->model->get_all_data_objects();
                    $values['data_objects'] = array_merge( $values['data_objects'], $account_line );
                }
                $values['meta_data'] = $this->model->get_meta_data_list();

                $sum_balance = 0;
                foreach( $values['data_objects'] as $account_line ) {
                    $balance = $account_line[Accounts_Db_Table::BALANCE]->get_value();
                    $sum_balance += $balance;
                }
                $sum_obj = new Crypto_currency_type(null, null, $sum_balance);
                $sum_line = array(
                    Accounts_Db_Table::PRIMARY_KEY => 'Sum',
                    Accounts_Db_Table::CLIENT_ID => '',
                    Accounts_Db_Table::BALANCE => $sum_obj,
                    Accounts_Db_Table::CHART_ACCOUNT_TYPE_ID => '',
                    Accounts_Db_Table::LABEL => ''
                );
                array_push( $values['data_objects'], $sum_line);
            }
        }

        $site_url = get_site_url();
        $transaction_page_url = $site_url . '/wp-admin/admin.php?page=bcq-admin-transactions';

        /*
        foreach( $values['data_objects'] as $idx => $account_line )
        {
            $account_id = $account_line[Accounts_Db_Table::PRIMARY_KEY];
            $href = $transaction_page_url . '&account_id=' . strval($account_id->get_value());
            $a = new A('Latest transactions', $href);
            $values['data_objects'][$idx]['links'] = $a;
        }
        $values['meta_data']['links'] = array('label' => 'Links');
        */

        return $values;
    }
}
