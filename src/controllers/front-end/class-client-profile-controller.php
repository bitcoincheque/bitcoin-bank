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
class Client_Profile_Controller extends Std_Controller {

    protected $clients_id = false;

    public function __construct( $clients_id = null ) {
        $model_class = 'BCQ_BitcoinBank\Clients_Db_Table';
        $view_class = 'BCQ_BitcoinBank\Client_Details_View';
        parent::__construct( $model_class, $view_class );
        if( ! $clients_id ) {
            $this->clients_id = Accounting::get_client_id();
        }
    }

    protected function load_model_values( $values = array() ) {
        if ( isset( $this->model ) ) {
            $result = $this->model->load_data_id($this->clients_id);
            if($result === 1)
            {
                $data_in_array = $this->model->get_copy_all_data();
                $values['data_objects'] = $data_in_array[0];

                $values['meta_data'] = $this->model->get_meta_data_list();

                $wp_current_user =wp_get_current_user();
                $values['data_objects']['wp_user_name'] = $wp_current_user->user_login;
                $values['meta_data']['wp_user_name'] = array('label' => 'Login username');

                $values['client_id'] = $this->clients_id;
                $values['status'] = true;

            } else {
                $values['status'] = false;
            }
        }
        return $values;
    }
}
