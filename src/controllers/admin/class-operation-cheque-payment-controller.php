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

use WP_PluginFramework\Controllers\Admin_Controller;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Views\Admin_Std_View;

/**
 * Summary.
 *
 * Description.
 */
class Operation_Cheque_Payment_Controller extends Admin_Controller
{
    /** @var Operation_Account_Transfer_View */
    public $view;

    public function __construct()
    {
        $model_class = null;
        $view_class = 'BCQ_BitcoinBank\Operation_Cheque_Payment_View';
        parent::__construct($model_class, $view_class);
    }

    public function button_transfer_type_reload_cheque_click()
    {
        $errors = false;
        $message = '';

        $transfer_type = $this->view->transfer_type->get_selected();

        switch($transfer_type) {
            case 'expense':
                $from_account = Accounting::get_main_account_chart_list(Account_Chart_Db_Table::MAIN_TYPE_INCOME_EXPENSES);
                break;

            case 'investment':
                $from_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_INVESTMENT);
                break;

            default:
                $errors = true;
                $message = 'Invalid transfer type';
                break;
        }

        $from_item_list = array();
        foreach ($from_account as $account) {
            $account_id = $account[Accounts_Db_Table::PRIMARY_KEY];
            $label = $account[Accounts_Db_Table::LABEL];
            $item = $account_id . ': ' . $label;
            $from_item_list[$account_id] = $item;
        }
        $this->view->from_account->set_items($from_item_list);

        if ( $errors )
        {
            $this->view->status_bar_footer->set_status_text($message, Status_Bar::STATUS_ERROR);
        }
    }

    public function button_make_transfer_click()
    {
        $this->hide_input_error_indications();

        if (is_user_logged_in())
        {
            $errors = false;
            $message = '';

            $from_account = $this->view->from_account->get_selected();
            if ( ! $from_account )
            {
                $this->response_set_input_error( 'from_account' );
                $message .= "Enter From Account No. to take money from.";
                $errors = true;
            }
            $from_account = filter_var( $from_account, FILTER_SANITIZE_NUMBER_INT );
            $from_account = intval($from_account);

            $receiver_email = $this->view->email->get_text();
            if( ! $receiver_email )
            {
                $this->response_set_input_error( '$$receiver_email' );
                $message .= "Enter e-mail address to send cheque to.";
                $errors = true;
            }
            $receiver_email = filter_var( $receiver_email, FILTER_SANITIZE_EMAIL );

            $amount_txt = $this->view->amount->get_text();
            if( ! $amount_txt )
            {
                $this->response_set_input_error( 'amount' );
                $message .= "No amount value. ";
                $errors = true;
            }
            $amount_btc = doubleval($amount_txt);
            $amount_btc = filter_var( $amount_btc, FILTER_VALIDATE_FLOAT );

            if ( ! $errors )
            {
                if ($amount_btc < 0.0)
                {
                    $this->response_set_input_error('amount');
                    $message .= 'Can not send negative amount. ';
                    $errors = true;
                }
            }

            if ( ! $errors )
            {
                if ($amount_btc == 0)
                {
                    $this->response_set_input_error('amount');
                    $message .= 'Enter amount value to transfer. ';
                    $errors = true;
                }
            }

            if ( ! $errors )
            {
                $transfer_type = $this->view->transfer_type->get_selected();
                switch($transfer_type) {
                    case 'expense':
                        $available_sum = Accounting::balance_calculate_equity();
                        break;

                    case 'investment':
                        $available_sum = Accounting::balance_calculate_equity();
                        break;

                    default:
                        $errors = true;
                        $message = 'Undefined error.';
                        break;
                }
            }

            if ( ! $errors )
            {
                $amount_satoshi = Accounting::get_amount_in_units($from_account, $amount_btc);

                if ($amount_satoshi > $available_sum)
                {
                    $this->response_set_input_error('amount');
                    $message .= 'Not enough funds.';
                    $errors = true;
                }
            }

            if ( ! $errors )
            {
                $memo = $this->view->memo->get_text();
                $memo = filter_var($memo, FILTER_SANITIZE_STRING);
            }

            if ( ! $errors )
            {
                $sender_address = Accounting::get_client_money_address();
                $expire_time = time() + 3600;

                $cheque_id = Cheque_Handler::make_cheque_transaction(
                    $from_account,
                    $amount_satoshi,
                    $sender_address,
                    $receiver_email,
                    $expire_time,
                    $memo,
                    false
                );

                if($cheque_id === false) {
                    $message .= 'Error. Could not make cheque transaction.';
                    $errors = true;
                } else {
                    $cheque_data = new Cheque_Db_Table();
                    $cheque_data->load_data_id($cheque_id);
                    $access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);

                    $send_result = Cheque_Handler::send_email_cheque($receiver_email, $cheque_id, $access_code, "Test", $memo);

                    if($send_result) {
                        $this->view->status_bar_footer->set_status_text('Cheque sent successfully.', Status_Bar::STATUS_SUCCESS);
                    } else {
                        $message = "Warning. Cheque created, but not sent.";
                        $errors = true;
                    }
                }
            }
        }
        else
        {
            $message = "Not logged in.";
            $errors = true;
        }

        if ( $errors )
        {
            $this->view->status_bar_footer->set_status_text($message, Status_Bar::STATUS_ERROR);
        }

        $this->show_onput_error_indications();
    }
}
