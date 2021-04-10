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
use WP_PluginFramework\HtmlElements\A;
use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Cheque_List_Controller extends Front_Page_List_Controller
{
    public function __construct( $client_id = null, $account_id = null ) {
        $model_class = 'BCQ_BitcoinBank\Cheque_Db_Table';
        parent::__construct( $model_class );
    }

    protected function init_view( $values ) {
        /*
        foreach ( $values['data_objects'] as $idx => $row ) {
            //$href = site_url() . '/wp-admin/admin.php?page=bcq-admin-cheques&cheque_id=' . $row['id'];
            $href = site_url() . '/cheque-details?cheque_id=' . $row['id'] . '&access_code=' . $row['access_code'];;
            $a = new A('Details', $href);
            $values['data_objects'][$idx]['cheque_detail]'] = $a;

            $currency_value = $values['data_objects'][$idx][Cheque_Db_Table::AMOUNT];
            $currency_obj = new Currency_Type($values['meta_data'][Cheque_Db_Table::AMOUNT], Cheque_Db_Table::AMOUNT, $currency_value);
            $formatted_currency = $currency_obj->get_formatted_text();
            $values['data_objects'][$idx][Cheque_Db_Table::AMOUNT] = $formatted_currency;
        }

        $values['meta_data']['links'] =  array('label' => 'Links');
*/
        parent::init_view( $values );
    }
}
