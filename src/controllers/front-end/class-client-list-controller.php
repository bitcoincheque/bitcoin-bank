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
class Client_List_Controller extends List_Controller
{
    public function __construct( $model_class = null, $view_class = null, $id = null ) {
        if( ! isset($model_class)){
            $model_class = 'BCQ_BitcoinBank\Clients_Db_Table';
        }
        parent::__construct( $model_class, $view_class, $id );
    }
}