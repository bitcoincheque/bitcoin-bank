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

defined('ABSPATH') || exit;

use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Account_List_Controller extends Front_Page_Std_Controller {

    protected $client_id = false;

    public function __construct( $client_id = null ) {
        $model_class = 'BCQ_BitcoinBank\Accounts_Db_Table';
        $view_class = 'WP_PluginFramework\Views\List_View';

        parent::__construct( $model_class, $view_class );

        if( ! $client_id ) {
            $client_id = Accounting::get_client_id();
        }
        $this->client_id = $client_id;
    }

    protected function load_model_values( $values = array() ) {
        if ( isset( $this->model ) ) {
            $result = $this->model->load_data(Accounts_Db_Table::CLIENT_ID, $this->client_id);
            if($result !== false)
            {
                $columns = array(
                    Accounts_Db_Table::PRIMARY_KEY,
                    Accounts_Db_Table::LABEL,
                    Accounts_Db_Table::BALANCE
                );

                $values['data_objects'] = $this->model->get_all_data_objects($columns);

                $link_options = new Settings_Linking_Options();
                $transaction_page = $link_options->get_complete_link_url(Settings_Linking_Options::TRANSACTION_PAGE);
                $total_balance = 0;
                foreach ($values['data_objects'] as $idx => $data_obj)
                {
                    $balance = $data_obj[Accounts_Db_Table::BALANCE];
                    $fiat = clone $balance;
                    $fiat->set_property('alternative_currency', true);
                    $values['data_objects'][$idx]['fiat'] = $fiat;

                    $account_id = $data_obj[Accounts_Db_Table::PRIMARY_KEY]->get_value();
                    $url = $transaction_page . '?account_id=' . $account_id;
                    $a = new A('Transactions', $url);
                    $values['data_objects'][$idx]['links'] = $a;

                    $total_balance += $balance->get_value();
                }

                $total_balance_obj = new Crypto_Currency_Type(null, null, $total_balance);
                $fiat_balance_obj = clone $total_balance_obj;
                $fiat_balance_obj->set_property('alternative_currency', true);

                $total_balance_line = array(
                    'id' => '',
                    'label' => 'Total balance:',
                    'balance' => $total_balance_obj,
                    'fiat' => $fiat_balance_obj,
                    'links' => ''
                );
                array_push($values['data_objects'], $total_balance_line);

                $values['meta_data'] = $this->model->get_meta_data_list($columns);
                $values['meta_data']['fiat'] =  array('label' => 'Fiat value');
                $values['meta_data']['links'] =  array('label' => 'Links');

                $values['client_id'] = $this->client_id;
                $values['status'] = true;

            } else {
                $values['status'] = false;
                $values['message'] = 'No account';
            }
        }
        return $values;
    }
}
