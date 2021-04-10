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
use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Cheque_Sent_List_Controller extends Front_Page_List_Controller
{
    public function __construct( $model_class = null, $view_class = null, $id = null ) {
        if( ! isset($model_class)){
            $model_class = 'BCQ_BitcoinBank\Cheque_Db_Table';
        }
        parent::__construct( $model_class, $view_class, $id );
    }

    protected function load_model_values( $values = array() )
    {
        if ( isset( $this->model ) )
        {
            $client_id = Accounting::get_client_id();
            $account_id = Accounting::get_client_default_account($client_id);
            $this->model->load_data(Cheque_Db_Table::DEBIT_ACCOUNT_ID, $account_id);

            $columns = array(
                Cheque_Db_Table::PRIMARY_KEY,
                Cheque_Db_Table::ISSUE_TIMESTAMP,
                Cheque_Db_Table::STATE,
                Cheque_Db_Table::RECEIVER_ADDRESS,
                Cheque_Db_Table::AMOUNT
            );

            $values['data_objects'] = $this->model->get_all_data_objects($columns);
            krsort($values['data_objects'], SORT_NUMERIC);

            $values['meta_data'] = $this->model->get_meta_data_list($columns);
        }
        return $values;
    }

}