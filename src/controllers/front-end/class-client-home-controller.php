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

use WP_PluginFramework\Controllers\Std_Controller;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Client_Home_Controller extends Std_Controller {

    protected $client_id = false;

    public function __construct( $client_id = null ) {
        $model_class = 'BCQ_BitcoinBank\Clients_Db_Table';
        $view_class = 'BCQ_BitcoinBank\Client_Details_View';
        parent::__construct( $model_class, $view_class );
        if( ! $client_id ) {
            $this->client_id = Accounting::get_client_id();
        }
    }

    protected function draw_view($parameters = null)
    {
        $parameters = array();

        if ( $this->client_id )
        {
            $result = $this->model->load_data_id($this->client_id);
            if ($result !== false)
            {
                $data_in_array = $this->model->get_all_data_objects();
                $parameters = $data_in_array[0];

                $wp_current_user = wp_get_current_user();
                $parameters['wp_user_name'] = $wp_current_user->user_login;
                $parameters['first_name'] = $wp_current_user->first_name;
                $parameters['last_name'] = $wp_current_user->last_name;
                $parameters['email'] = $wp_current_user->user_email;
                $parameters['status'] = true;

            }
            else
            {
                $parameters['status'] = false;
            }
        }

        return parent::draw_view($parameters); // TODO: Change the autogenerated stub
    }


}
