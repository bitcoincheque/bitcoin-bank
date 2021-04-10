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
use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Front_Page_List_Controller extends List_Controller
{
    protected function enqueue_script()
    {
        $unique_prefix = Plugin_Container::get_prefixed_plugin_slug();

        $style_handler2 = $unique_prefix . '_style_handler2';
        $style_url      = plugins_url() . '/bitcoin-bank/asset/css/bitcoin-bank-details-style.css';
        $style_version  = Plugin_Container::get_plugin_version();
        wp_enqueue_style( $style_handler2, $style_url, array(), $style_version );

        parent::enqueue_script();
    }
}