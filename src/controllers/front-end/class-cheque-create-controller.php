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

use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Cheque_Create_Controller extends Cheque_Controller
{
    /** @var Cheque_Send_View */
    public $view;

    public function __construct()
    {
        parent::__construct(null, null, 'BCQ_BitcoinBank\Cheque_Create_View');
    }

    protected function draw_view($parameters = null)
    {
        if(!$parameters)
        {
            $parameters = array();
        }

        if(isset($parameters['cheque_has_been_created']))
        {
            if($parameters['send_email_status'])
            {
                $message = "Cheque has been sent.";
                $status_color = Status_Bar::STATUS_SUCCESS;
            }
            else
            {
                $message = "E-mail error. Cheque created, but not sent.";
                $status_color = Status_Bar::STATUS_ERROR;
            }
            $this->view->status_bar->set_status_text($message, $status_color);
        }
        else
        {

            $client_id = Accounting::get_client_id();
            $account_id = Accounting::get_client_default_account($client_id);
            if ($account_id)
            {
                $accounting_data = new Accounts_Db_Table();
                $result = $accounting_data->load_data_id($account_id);
                $parameters['values'] = $accounting_data->get_data_object_record();
                $parameters['status'] = true;
            }
            else
            {
                $parameters['status'] = false;
            }
        }

        return parent::draw_view($parameters);
    }

    public function button_create_cheque_click()
    {
        $this->hide_input_error_indications();

        if (is_user_logged_in())
        {
            $input_data_ok = true;
            $message = '';

            $receiver_email = $this->view->email->get_text();
            $receiver_email = trim($receiver_email);
            if ( ! $receiver_email )
            {
                $this->response_set_input_error( 'email' );
                $message .= "No e-mail. ";
                $input_data_ok = false;
            }

            $receiver_email = filter_var( $receiver_email, FILTER_VALIDATE_EMAIL );

            $amount_txt = $this->view->amount->get_text();
            if( ! $amount_txt )
            {
                $this->response_set_input_error( 'amount' );
                $message .= "No amount value. ";
                $input_data_ok = false;
            }

            $amount = Crypto_currency_type::convert_str_to_value($amount_txt);

            if ( $input_data_ok )
            {
                if ($amount === false)
                {
                    $this->response_set_input_error('amount');
                    $message .= 'Invalid characters in amount.';
                    $input_data_ok = false;
                }
            }

            if ( $input_data_ok )
            {
                if ($amount < 0)
                {
                    $this->response_set_input_error('amount');
                    $message .= 'Can not send negative amount.';
                    $input_data_ok = false;
                }
            }

            if ( $input_data_ok )
            {
                if ($amount == 0)
                {
                    $this->response_set_input_error('amount');
                    $message .= 'Enter amount value to send.';
                    $input_data_ok = false;
                }
            }

            if ( $input_data_ok )
            {
                $wp_user_id = get_current_user_id();
                $client_id = Accounting::get_client_id($wp_user_id);
                $account_id = Accounting::get_client_default_account($client_id);
                $balance = Accounting::get_account_balance($account_id);
                $cheque_fee = Cheque_Handler::get_cheque_fee();
                $total_pay = $amount + $cheque_fee;

                if( $total_pay > $balance ) {
                    $this->response_set_input_error('amount');
                    $message .= 'Not enough funds.';
                    $input_data_ok = false;
                }
            }

            if ( $input_data_ok )
            {
                $memo = $this->view->memo->get_text();
                if ( strlen($memo) > 50 ) {
                    $message .= 'Memo can be maximum 50 characters.';
                    $input_data_ok = false;
                }
            }
            if ( $input_data_ok )
            {
                $expire = $this->view->expire->get_selected();
                switch( $expire ) {
                    case 'five_minutes':
                        $expire_seconds = 5*60;
                        break;
                    case 'thirty_minutes':
                        $expire_seconds = 30*60;
                        break;
                    case 'one_hour':
                        $expire_seconds = 3600;
                        break;
                    case 'two_hour':
                        $expire_seconds = 2*3600;
                        break;
                    case 'tree_hour':
                        $expire_seconds = 3*3600;
                        break;
                    case 'six_hour':
                        $expire_seconds = 6*3600;
                        break;
                    case 'half_day':
                        $expire_seconds = 12*3600;
                        break;
                    case 'one_day':
                        $expire_seconds = 24*3600;
                        break;
                    case 'two_days':
                        $expire_seconds = 2*24*3600;
                        break;
                    case 'tree_days':
                        $expire_seconds = 3*24*3600;
                        break;
                    case 'five_days':
                        $expire_seconds = 5*24*3600;
                        break;
                    case 'one_week':
                        $expire_seconds = 7*24*3600;
                        break;
                    case 'two_weeks':
                        $expire_seconds = 14*24*3600;
                        break;
                    default:
                        $message .= 'Error. Expire option not availeble.';
                        $input_data_ok = false;
                        break;
                }
            }

            if ( $input_data_ok ) {
                $sender_address = Accounting::get_client_money_address($client_id);
                $expire_time = time() + $expire_seconds;


                $cheque = new Cheque_File();
                $cheque->set_data(Cheque_File::AMOUNT, $amount);
                $cheque->set_data(Cheque_File::CURRENCY_UNIT, 'BTC');
                $cheque->set_data(Cheque_File::EXPIRE_TIME, $expire_time);
                $cheque->set_data(Cheque_File::SENDER_ADDRESS, $sender_address);
                $cheque->set_data(Cheque_File::RECEIVER_ADDRESS, $receiver_email);
                $cheque->set_data(Cheque_File::MEMO, $memo);

                if ( $cheque->validate_cheque_data() === false ) {
                    $message .= 'Error creating cheque';
                    $input_data_ok = false;
                }
            }

            if ( $input_data_ok ) {
                $debit_account_id = $account_id;
                $time_stamp = time();
                $sender_client_id = $client_id;
                $fee_obj = Prices::calculate_cheque_fee($client_id, $account_id, $cheque);
                $receiver_client_id = null;

                $cheque = Cheque_Handler::create_cheque(
                    $cheque,
                    $time_stamp,
                    $debit_account_id,
                    $fee_obj,
                    $sender_client_id,
                    $receiver_client_id,
                    $state=Cheque_Db_Table::STATE_REGISTRATION_INIT
                );

                if( $cheque === false ) {
                    $message .= 'Error. Could not write cheque.';
                    $input_data_ok = false;
                }
            }

            if( $input_data_ok) {
                $cheque_id = $cheque->get_data(Cheque_File::SERIAL_NUMBER);
                $time_stamp = $cheque->get_data(Cheque_File::ISSUE_TIME);
                $fee = $fee_obj->get_value();
                $result = Accounting::make_cheque_transaction(
                    $debit_account_id,
                    $amount,
                    $cheque_id,
                    $fee,
                    $time_stamp
                );
                if($result !== true) {
                    $message .= 'Server error: Can not make account transaction.';
                    $input_data_ok = false;
                }
            }

            if( $input_data_ok ) {
                $result = Cheque_Handler::change_state_to_issued($cheque_id);
                if($result !== true) {
                    $message .= 'Server error: Can not issue cheque. Transaction has been made, but will be refunded when cheque expires.';
                    $input_data_ok = false;
                }
            }

            if ( $input_data_ok )
            {
                $cheque_data = new Cheque_Db_Table();
                $cheque_data->load_data_id($cheque_id);
                $access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);

                $send_result = Cheque_Handler::send_email_cheque($receiver_email, $cheque_id, $access_code, "Test", $memo);

                $columns = array(
                    Cheque_Db_Table::PRIMARY_KEY,
                    Cheque_Db_Table::ISSUE_TIMESTAMP,
                    Cheque_Db_Table::STATE,
                    Cheque_Db_Table::RECEIVER_ADDRESS,
                    Cheque_Db_Table::AMOUNT
                );

                $data_obj = $cheque_data->get_all_data_objects($columns);
                $meta_data = $cheque_data->get_meta_data_list($columns);

                $parameter = array(
                    'cheque_valid' => true,
                    'cheque_id' => $cheque_id,
                    'access_code' => $access_code,
                    'data_objects' => $data_obj,
                    'meta_data' => $meta_data,
                    'show_cheque_data' => true,
                    'cheque_has_been_created' => true
                );

                if ($send_result)
                {
                    $parameter['send_email_status'] = true;
                }
                else
                {
                    $parameter['send_email_status'] = false;
                }

                $this->reload_view('BCQ_BitcoinBank\Cheque_Sent_Receipt_View', null, null, $parameter);
            }
            else
            {
                if(!$message)
                {
                    $message = "Undefined error.";
                }
                $this->view->status_bar_footer->set_status_text($message, Status_Bar::STATUS_ERROR);
            }
        }
        else
        {
            $message = "Not logged in.";
            $this->view->status_bar_footer->set_status_text($message, Status_Bar::STATUS_ERROR);

        }

        $this->show_onput_error_indications();
    }
}
