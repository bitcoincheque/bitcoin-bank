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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Controllers\Std_Controller;
use WP_PluginFramework\Plugin_Container;


abstract class Cheque_Controller extends Std_Controller {

    protected $cheque_id = null;
    protected $access_code = null;
    protected $access_code_valid = false;
    protected $cheque_is_loaded = false;

    public function __construct( $cheque_id = null, $access_code = null, $view_class = null ) {
        parent::__construct( 'BCQ_BitcoinBank\Cheque_Db_Table', $view_class );

        $this->cheque_id = $cheque_id;
        $this->access_code = $access_code;

        $this->set_permission( false );
    }

    protected function enqueue_script()
    {
        $unique_prefix = Plugin_Container::get_prefixed_plugin_slug();

        $style_handler = $unique_prefix . '_bank_style_handler';
        $style_url      = plugins_url() . '/bitcoin-bank/asset/css/bitcoin-bank-details-style.css';
        $style_version  = Plugin_Container::get_plugin_version();
        wp_enqueue_style( $style_handler, $style_url, array(), $style_version );

        parent::enqueue_script();
    }

    protected function load_model_values( $values = array() ) {
        if($this->cheque_id)
        {
            if ($this->model->load_data_id($this->cheque_id))
            {
                $this->cheque_exist = true;
                $this->cheque_is_loaded = true;

                $ac = $this->model->get_data(Cheque_Db_Table::ACCESS_CODE);
                if ($ac === $this->access_code)
                {
                    $this->access_code_valid = true;
                }
            }
        }
        return $values;
    }

    protected function cheque_exist( $cheque_no ) {
        $cheque_exist = $this->model->load_data(Cheque_Db_Table::PRIMARY_KEY, $cheque_no);
        if( $cheque_exist === 1 ) {
            return true;
        } else {
            return false;
        }
    }

    protected function cheque_access_code_exist( $cheque_no, $access_code ) {
        $cheque_exist = $this->model->load_data(Cheque_Db_Table::PRIMARY_KEY, $cheque_no);
        if( $cheque_exist === 1 ) {
            $ac = $this->model->get_data(Cheque_Db_Table::ACCESS_CODE);
            if ( $access_code === $ac) {
                return true;
            }
        }

        return false;
    }

    protected function reload_cheque_data( $cheque_id, $access_code ) {
        $this->cheque_id = $cheque_id;
        $this->access_code = $access_code;
        $this->load_model_values();
    }

    protected function can_user_access_cheque( $access_code=null ) {
        $approved = false;

        $ac = $this->model->get_data(Cheque_Db_Table::ACCESS_CODE);
        if ($ac === $access_code )
        {
            $this->access_code_valid = true;
        }

        $client_id = Accounting::get_client_id();

        $sender_client_id = $this->model->get_data(Cheque_Db_Table::SENDER_CLIENT_ID);
        if ($sender_client_id == $client_id)
        {
            $approved = true;
        }

        $receiver_client_id = $this->model->get_data(Cheque_Db_Table::RECEIVER_CLIENT_ID);
        if ($receiver_client_id == $client_id)
        {
            $approved = true;
        }

        $receiver_email = $this->model->get_data(Cheque_Db_Table::SENDER_ADDRESS);
        $wp_current_user = wp_get_current_user();
        $user_email = $wp_current_user->user_email;
        if ($receiver_email === $user_email)
        {
            $approved = true;
        }

        if(current_user_can('administrator'))
        {
            $approved = true;
        }

        return $approved;
    }

    protected function is_cheque_loaded() {
        return $this->cheque_is_loaded;
    }

    protected function is_access_code_valid() {
        return $this->access_code_valid;
    }

    protected function cheque_valid() {
        if ( $this->cheque_is_loaded and $this->access_code_valid ) {
            return true;
        } else {
            return false;
        }
    }


    protected function hide_input_error_indications() {
        $this->view->hide_input_error_indications();
    }

    protected function show_onput_error_indications() {
        $error_inputs = $this->get_server_context_data( 'error_input' );
        if ( $error_inputs ) {
            $this->view->show_input_error_indications( $error_inputs );
        }
    }

    protected function response_set_input_error( $input_name ) {
        $error_input                = $this->get_server_context_data( 'error_input', array() );
        $error_input[ $input_name ] = true;
        $this->set_server_context_data( 'error_input', $error_input );
    }
}
