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

use WP_PluginFramework\Controllers\Form_Controller;
use WP_PluginFramework\Plugin_Container;

class Cheque_Details_Controller extends Cheque_Controller {

    public function __construct( $cheque_id, $view_class = null ) {
        if( ! isset($view_class)){
            $view_class = 'BCQ_BitcoinBank\Cheque_Details_View';
        }

        parent::__construct( $cheque_id, null, $view_class );
    }

    protected function draw_view($parameters = null)
    {
        $parameters = array();

        if( $this->is_cheque_loaded())
        {
            $parameters['cheque_id'] = $this->model->get_data(Cheque_Db_Table::PRIMARY_KEY);
            $parameters['cheque_exist'] = true;

            if ($this->can_user_access_cheque())
            {
                $parameters['cheque_id'] = $this->model->get_data(Cheque_Db_Table::PRIMARY_KEY);
                $parameters['access_code'] = $this->model->get_data(Cheque_Db_Table::ACCESS_CODE);
                $parameters['values'] = $this->model->get_data_object_record();
                $parameters['access_right'] = true;
            }
            else
            {
                $parameters['access_right'] = false;
            }
        } else {
            $parameters['cheque_exist'] = false;
        }

        return parent::draw_view($parameters); // TODO: Change the autogenerated stub
    }
}
