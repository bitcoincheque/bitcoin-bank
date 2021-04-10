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

class Account_Id_Type extends Id_Type {

    public function get_formatted_text() {
        $text = sprintf("%04d", $this->value);
        return $text;
    }

    public function create_content() {
        $text = $this->get_formatted_text();
        if ( is_admin() or current_user_can('administrator') )
        {
            $href = get_site_url() . '/wp-admin/admin.php?page=bcq-admin-transactions&account_id=' . strval($this->value);
            $a = new A($text, $href);
            $this->set_content($a);
        }
        else{
            $this->set_content($text);
        }
    }
}
