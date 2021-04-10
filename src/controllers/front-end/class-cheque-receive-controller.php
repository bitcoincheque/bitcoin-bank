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
class Cheque_Receive_Controller extends Cheque_Controller {

    public function __construct( $cheque_id = null, $access_code = null ) {
        $view_class = 'BCQ_BitcoinBank\Cheque_Receive_View';
        parent::__construct( $cheque_id, $access_code, $view_class );
    }

    protected function load_model_valuesxxx( $values = array() ) {
        $values['cheque_valid'] = false;

        if($this->cheque_id)
        {
            if ($this->model->load_data_id($this->cheque_id))
            {
                $this->cheque_exist = true;
                $this->cheque_is_loaded = true;

                $columns = array(
                    Cheque_Db_Table::PRIMARY_KEY,
                    Cheque_Db_Table::ISSUE_TIMESTAMP,
                    Cheque_Db_Table::STATE,
                    Cheque_Db_Table::RECEIVER_ADDRESS,
                    Cheque_Db_Table::AMOUNT
                );

                $data_objects = $this->model->get_all_data_objects($columns);
                $values['data_objects'] = $data_objects;
                $values['meta_data'] = $this->model->get_meta_data_list($columns);
                $values['cheque_id'] = $this->cheque_id;
                $values['access_code'] = $this->access_code;
                $values['cheque_state'] = $this->model->get_data(Cheque_Db_Table::STATE);
                $values['cheque_valid'] = true;
            }
        }
        return $values;
    }

    protected function draw_view($parameters = null)
    {
        if(!$parameters)
        {
            $parameters = array();
        }

        if( $this->cheque_valid())
        {
            $columns = array(
                Cheque_Db_Table::PRIMARY_KEY,
                Cheque_Db_Table::ISSUE_TIMESTAMP,
                Cheque_Db_Table::STATE,
                Cheque_Db_Table::RECEIVER_ADDRESS,
                Cheque_Db_Table::AMOUNT
            );

            $data_objects = $this->model->get_all_data_objects($columns);
            $parameters['data_objects'] = $data_objects;
            $parameters['meta_data'] = $this->model->get_meta_data_list($columns);
            $parameters['cheque_id'] = $this->cheque_id;
            $parameters['access_code'] = $this->access_code;
            $parameters['cheque_state'] = $this->model->get_data(Cheque_Db_Table::STATE);
            $parameters['cheque_valid'] = true;

            $this->view->add_hidden_fields('cheque_id', $parameters['cheque_id']);
            $this->view->add_hidden_fields('access_code', $parameters['access_code']);

            if(isset($parameters['cheque_has_been_accepted']))
            {
                $parameters['show_cheque_data'] = true;

                $message = 'Cheque accepted.';
                $status_color = Status_Bar::STATUS_SUCCESS;
            }
            elseif(isset($parameters['cheque_has_been_rejected']))
            {
                $parameters['show_cheque_data'] = true;

                $message = 'Cheque rejected.';
                $status_color = Status_Bar::STATUS_SUCCESS;
            }
            else
            {
                switch ($parameters['cheque_state'])
                {
                    case Cheque_Db_Table::STATE_REGISTRATION_INIT:
                        $message = 'This cheque has an error. Contact site admin.';
                        $status_color = Status_Bar::STATUS_ERROR;
                        $parameters['show_cheque_data'] = true;
                        break;
                    case Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED:
                        $message = 'You must accept the cheque to receive it face value.';
                        $status_color = Status_Bar::STATUS_SUCCESS;
                        $parameters['show_cheque_data'] = true;
                        break;
                    case Cheque_Db_Table::STATE_REGISTRATION_CLAIMED:
                        $message = 'This cheque has already been accepted and received.';
                        $status_color = Status_Bar::STATUS_INFO;
                        $parameters['show_cheque_data'] = false;
                        break;
                    case Cheque_Db_Table::STATE_REGISTRATION_EXPIRED:
                        $message = 'This cheque has expired.';
                        $status_color = Status_Bar::STATUS_ERROR;
                        $parameters['show_cheque_data'] = false;
                        break;
                    case Cheque_Db_Table::STATE_REGISTRATION_REJECTED:
                        $message = 'This cheque has been rejected.';
                        $status_color = Status_Bar::STATUS_INFO;
                        $parameters['show_cheque_data'] = false;
                        break;
                    default:
                        $message = 'This cheque has an undefined error. Contact site admin.';
                        $status_color = Status_Bar::STATUS_ERROR;
                        $parameters['show_cheque_data'] = true;
                        break;
                }
            }

            $this->view->status_bar->set_status_text($message, $status_color);
        } else {
            $parameters['cheque_valid'] = true;
            $this->view->status_bar->set_status_text('This cheque is invalid.', Status_Bar::STATUS_ERROR);
        }

        return parent::draw_view($parameters); // TODO: Change the autogenerated stub
    }

    public function button_claim_cheque_click()
    {
        $cheque_id = Security_Filter::safe_read_post_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
        $access_code = Security_Filter::safe_read_post_request('access_code', Security_Filter::STRING_KEY_NAME);

        $this->reload_cheque_data($cheque_id, $access_code);

        if ($this->cheque_valid())
        {
            if(Cheque_Handler::claim_cheque($cheque_id, $access_code))
            {
                $message = "Cheque has been received.";

                $columns = array(
                    Cheque_Db_Table::PRIMARY_KEY,
                    Cheque_Db_Table::ISSUE_TIMESTAMP,
                    Cheque_Db_Table::STATE,
                    Cheque_Db_Table::RECEIVER_ADDRESS,
                    Cheque_Db_Table::AMOUNT
                );

                $data_obj = $this->model->get_all_data_objects($columns);
                $meta_data = $this->model->get_meta_data_list($columns);

                $values = array(
                    'cheque_id' => $cheque_id,
                    'access_code' => $access_code,
                    'message' => $message,
                    'data_objects' => $data_obj,
                    'meta_data' => $meta_data
                );

                $parameter = array('cheque_has_been_accepted' => true);

                $this->reload_view('BCQ_BitcoinBank\Cheque_Sent_Receipt_View', null, $values, $parameter);
            } else {
                $receiver_address = $this->model->get_data(Cheque_Db_Table::RECEIVER_ADDRESS);
                $wp_user = wp_get_current_user();
                if($receiver_address != $wp_user->user_email) {
                    $this->view->status_bar_footer->set_status_text('Error. This cheque can only be received by ' . $receiver_address, Status_Bar::STATUS_ERROR);
                } else {
                    $this->view->status_bar_footer->set_status_text('Error claiming cheque.', Status_Bar::STATUS_ERROR);
                }
            }
        } else
        {
            $this->view->status_bar_footer->set_status_text('Error accepting cheque.', Status_Bar::STATUS_ERROR);
        }
    }

    public function button_reject_cheque_click()
    {
        $cheque_id = Security_Filter::safe_read_post_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
        $access_code = Security_Filter::safe_read_post_request('access_code', Security_Filter::STRING_KEY_NAME);

        $this->reload_cheque_data($cheque_id, $access_code);

        $state = $this->model->get_data(Cheque_Db_Table::STATE);

        if ($this->cheque_valid())
        {
            if($state == Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED)
            {
                if (Cheque_Handler::reject_cheque($cheque_id, $access_code))
                {
                    $message = "Cheque has been rejected. Fund has been returned.";

                    $columns = array(
                        Cheque_Db_Table::PRIMARY_KEY,
                        Cheque_Db_Table::ISSUE_TIMESTAMP,
                        Cheque_Db_Table::STATE,
                        Cheque_Db_Table::RECEIVER_ADDRESS,
                        Cheque_Db_Table::AMOUNT
                    );

                    $data_obj = $this->model->get_all_data_objects($columns);
                    $meta_data = $this->model->get_meta_data_list($columns);

                    $values = array(
                        'cheque_id' => $cheque_id,
                        'access_code' => $access_code,
                        'message' => $message,
                        'data_objects' => $data_obj,
                        'meta_data' => $meta_data
                    );

                    $parameter = array('cheque_has_been_rejected' => true);

                    $this->reload_view('BCQ_BitcoinBank\Cheque_Sent_Receipt_View', null, $values, $parameter);
                }
                else
                {
                    $receiver_address = $this->model->get_data(Cheque_Db_Table::RECEIVER_ADDRESS);
                    $wp_user = wp_get_current_user();
                    if ($receiver_address != $wp_user->user_email)
                    {
                        $this->view->status_bar_footer->set_status_text('Error. This cheque can only be rejected by ' . $receiver_address, Status_Bar::STATUS_ERROR);
                    }
                    else
                    {
                        $this->view->status_bar_footer->set_status_text('Error rejecting cheque.', Status_Bar::STATUS_ERROR);
                    }
                }
            }
            else
            {
                $this->view->status_bar_footer->set_status_text('Can not reject. Check has been used.', Status_Bar::STATUS_ERROR);
            }
        }
        else
        {
            $this->view->status_bar_footer->set_status_text('Error. Cheque not valid.', Status_Bar::STATUS_ERROR);
        }
    }

}
