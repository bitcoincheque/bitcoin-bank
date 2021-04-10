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

use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Cheque_Lookup_Controller extends Cheque_Controller {

    protected $cheque_id = false;
    protected $access_code = false;

    public function __construct( $cheque_id = null, $access_code = null ) {
        $view_class = 'BCQ_BitcoinBank\Cheque_Lookup_View';
        parent::__construct( $cheque_id, $access_code, $view_class );

        $this->cheque_id = $cheque_id;
        $this->access_code = $access_code;
    }

    protected function load_model_values( $values = array() ) {
        if($this->cheque_id and $this->access_code)
        {
            if (isset($this->model))
            {
                $result = $this->model->load_data_id($this->cheque_id);
                if ($result === 1)
                {
                    $data_in_array = $this->model->get_copy_all_data();
                    $values['data_objects'] = $data_in_array[0];
                    $values['meta_data'] = $this->model->get_meta_data_list();
                    $values['cheque_id'] = $this->cheque_id;
                    $values['access_code'] = '0000';
                    $values['status'] = true;
                }
                else
                {
                    $values['status'] = false;
                }
            }
        }
        return $values;
    }

    public function button_lookup_cheque_click()
    {
        $input_data_ok = true;
        $message = '';
        $this->hide_input_error_indications();

        $cheque_id = $this->view->cheque_id->get_text();
        $access_code = $this->view->access_code->get_text();

        $cheque_id = filter_var( $cheque_id, FILTER_SANITIZE_NUMBER_INT );
        $cheque_id = intval($cheque_id);
        $access_code = filter_var( $access_code, FILTER_SANITIZE_STRING );

        if ( ! $this->cheque_exist( $cheque_id ) ) {
            $this->response_set_input_error('cheque_id');
            $message .= 'Error. This cheque s/n does not exist.';
            $input_data_ok = false;
        }

        if ( $input_data_ok ) {
            if ( ! $this->cheque_access_code_exist( $cheque_id, $access_code ) ) {
                $this->response_set_input_error('access_code');
                $message .= 'Error. Wrong Access Code.';
                $input_data_ok = false;
            }
        }

        if ( $input_data_ok ) {
            $args = array($cheque_id, $access_code);
            $this->reload_controller('BCQ_BitcoinBank\Cheque_Receive_Controller', $args );

        } else {
            if ( ! $message ) {
                $message = "Undefined error.";
            }
            $this->view->status_bar_footer->set_status_text($message, Status_Bar::STATUS_ERROR);
        }

        $this->show_onput_error_indications();
    }
}
