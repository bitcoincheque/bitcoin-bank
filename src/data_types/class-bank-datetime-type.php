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

use WP_PluginFramework\DataTypes\Time_Stamp_Type;
use WP_PluginFramework\HtmlElements\A;

class Bank_Datetime_Type extends Time_Stamp_Type {

    public function get_formatted_text() {

        $value = $this->get_value_seconds();

        if ( is_admin() or current_user_can('administrator'))
        {
            $text = date( 'Y-m-d H:i:s', $value );
        }
        else
        {
            $text = date( 'Y-m-d H:i', $value );
        }

        return $text;
    }
}
