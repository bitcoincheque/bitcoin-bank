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

use WP_PluginFramework\Controllers\Std_Controller;
use WP_PluginFramework\DataTypes\Currency_Type;
use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Transaction_List_Controller extends Std_Controller {

    protected $account_id = null;

    public function __construct(  ) {
        $model_class = 'BCQ_BitcoinBank\Transactions_Db_Table';
        $view_class = 'WP_PluginFramework\Views\List_View';
        parent::__construct( $model_class, $view_class );
		$this->set_permission( true );
    }

    protected function load_model_values( $values = array() ) {
        $result = $this->model->load_data();
        $transactions = $this->model->get_all_data_objects();

        foreach ($transactions as $transaction) {
            $transaction_type = $transaction[Transactions_Db_Table::TRANSACTION_TYPE]->get_value();
            switch ( $transaction_type ) {
                case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_DRAW:
                case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_RECEIVE:
                case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_EXPIRE:
                case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_FEE:
                case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REVERSED2:
                case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REJECT:
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
        $values['meta_data'] = $this->model->get_meta_data_list();

        return $values;
    }

}
