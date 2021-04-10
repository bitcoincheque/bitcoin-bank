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

use WP_PluginFramework\DataTypes\Id_Type;
use WP_PluginFramework\HtmlElements\A;

class Account_Chart_Id_Description_Type extends Id_Type {

    public function get_formatted_text() {
        static $account_charts = null;

        if (!isset($account_charts))
        {
            $account_chart_data = new Account_Chart_Db_Table();
            if ($account_chart_data->load_data())
            {
                $account_charts = $account_chart_data->get_all_data_objects(null, true);
            }
        }

        $account_chart_id = $this->get_value();
        if(isset($account_chart_id))
        {
            if(isset($account_charts[$account_chart_id])) {
                $account_chart = $account_charts[$account_chart_id];
                $text = $account_chart[Account_Chart_Db_Table::NUMBER]->get_string();
                $text .= ': ' . $account_chart[Account_Chart_Db_Table::LABEL]->get_string();
            } else {
                $text = 'Error. Undefined account chart ' . strval($account_chart_id);
            }
        } else {
            $text = 'Error. Account chart missing.';
        }
        return $text;
    }
}
