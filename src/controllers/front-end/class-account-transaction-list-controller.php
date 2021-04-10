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

use WP_PluginFramework\Controllers\Std_Controller;
use WP_PluginFramework\DataTypes\Currency_Type;
use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Account_Transaction_List_Controller extends Std_Controller {

    protected $account_id = null;

    public function __construct( $account_id = null  ) {
        $model_class = 'BCQ_BitcoinBank\Transactions_Db_Table';
        $view_class = 'WP_PluginFramework\Views\List_View';
        parent::__construct( $model_class, $view_class );

        if( ! $account_id ) {
            $client_id = Accounting::get_client_id();
            $account_id = Accounting::get_client_default_account($client_id);
        }

        if( $account_id )
        {
            $this->account_id = $account_id;
        }

		$this->set_permission( true );
    }

    protected function load_model_values( $values = array() ) {
        if( $this->account_id ) {
            $result = $this->model->load_data(Transactions_Db_Table::CREDIT_ACCOUNT_ID, $this->account_id);

            $values['meta_data'] = $this->model->get_meta_data_list();
            $meta_data = $values['meta_data'][Transactions_Db_Table::AMOUNT];

            $credit_transactions = $this->model->get_all_data_objects();

            for ($i=0; $i<count($credit_transactions); $i++) {
                $credit_transactions[$i]['debit'] = '';
                $credit_transactions[$i]['credit'] = $credit_transactions[$i][Transactions_Db_Table::AMOUNT];
                unset($credit_transactions[$i][Transactions_Db_Table::AMOUNT]);
                unset($credit_transactions[$i][Transactions_Db_Table::CREDIT_ACCOUNT_ID]);
                unset($credit_transactions[$i][Transactions_Db_Table::DEBIT_ACCOUNT_ID]);
            }

            $result = $this->model->load_data(Transactions_Db_Table::DEBIT_ACCOUNT_ID, $this->account_id);
            $debit_transactions = $this->model->get_all_data_objects();

            for ($i=0; $i<count($debit_transactions); $i++) {
                $debit_transactions[$i]['debit'] = $debit_transactions[$i][Transactions_Db_Table::AMOUNT];
                $debit_transactions[$i]['credit'] = '';
                unset($debit_transactions[$i][Transactions_Db_Table::AMOUNT]);
                unset($debit_transactions[$i][Transactions_Db_Table::CREDIT_ACCOUNT_ID]);
                unset($debit_transactions[$i][Transactions_Db_Table::DEBIT_ACCOUNT_ID]);
            }

            $links_options = new Settings_Linking_Options();
            $cheque_details_url = $links_options->get_complete_link_url( Settings_Linking_Options::CHEQUE_DETAILS_PAGE );

            $transactions = array_merge($debit_transactions, $credit_transactions);
            $values['data_objects'] = array();
            foreach ($transactions as $transaction) {
                $transaction_type = $transaction[Transactions_Db_Table::TRANSACTION_TYPE]->get_value();
                switch ( $transaction_type ) {
                    case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_DRAW:
                    case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_RECEIVE:
                    case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_EXPIRE:
                    case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_FEE:
                        $transaction_ref = $transaction[Transactions_Db_Table::REFERENCE_ID]->get_value();
                        if( $transaction_ref )
                        {
                            $transaction[Transactions_Db_Table::REFERENCE_ID] = new Cheque_Id_Type(null, null, $transaction_ref);
                        }
                        break;
                }

                $id = $transaction[Transactions_Db_Table::PRIMARY_KEY]->get_value();
                $values['data_objects'][$id] = $transaction;
            }
            krsort($values['data_objects'], SORT_NUMERIC);

            $amount_meta = $values['meta_data'][Transactions_Db_Table::AMOUNT];
            $amount_meta['label'] = 'Cash out';
            $values['meta_data']['debit'] = $amount_meta;
            $amount_meta['label'] = 'Cash in';
            $values['meta_data']['credit'] = $amount_meta;
            unset($values['meta_data'][Transactions_Db_Table::AMOUNT]);
            unset($values['meta_data'][Transactions_Db_Table::CREDIT_ACCOUNT_ID]);
            unset($values['meta_data'][Transactions_Db_Table::DEBIT_ACCOUNT_ID]);

            if(count($transactions))
            {
                $values['status'] = true;
            } else {
                $values['status'] = false;
                $values['message'] = 'No transactions found.';
            }
        } else {
            $values['status'] = false;
            $values['message'] = 'No account found.';
        }

        return $values;
    }

}
