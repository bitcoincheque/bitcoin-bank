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
class Operation_Withdraw_Cash_Controller extends Admin_Controller
{
    /** @var Operation_Account_Transfer_View */
    public $view;

    public function __construct()
    {
        $model_class = null;
        $view_class = 'BCQ_BitcoinBank\Operation_Withdraw_Cash_View';
        parent::__construct($model_class, $view_class);
    }

    public function button_transfer_type_reload_cheque_click()
    {
        $errors = false;
        $message = '';

        $transfer_type = $this->view->transfer_type->get_selected();

        switch($transfer_type) {
            case 'client_withdrawal':
                $from_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_LIABILITIES_CLIENT_SAVINGS);
                $to_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_CASH_HOT_VAULT);
                break;

            case 'equity_withdrawal':
                $from_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_EQUITY_PAID_IN_CAPITAL);
                $to_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_CASH_HOT_VAULT);
                break;

            case 'dividend_withdrawal':
                $from_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_EQUITY_RETAINED_EARNINGS);
                $to_account = Accounting::get_sub_account_chart_list(Account_Chart_Db_Table::SUB_TYPE_BALANCE_ASSET_CASH_HOT_VAULT);
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

        $to_item_list = array();
        foreach ($to_account as $account) {
            $account_id = $account[Accounts_Db_Table::PRIMARY_KEY];
            $label = $account[Accounts_Db_Table::LABEL];
            $item = $account_id . ': ' . $label;
            $to_item_list[$account_id] = $item;
        }
        $this->view->to_account->set_items($to_item_list);


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

            $to_account = $this->view->to_account->get_selected();
            if( ! $to_account )
            {
                $this->response_set_input_error( 'to_account' );
                $message .= "Enter To Account No. to send money to.";
                $errors = true;
            }
            $to_account = filter_var( $to_account, FILTER_SANITIZE_NUMBER_INT );
            $to_account = intval($to_account);

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
                $available_sum = Accounting::get_account_balance($from_account);
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
                $transaction_id = Accounting::make_account_transfer($from_account, $to_account, $amount_satoshi, null);
                if($transaction_id === false) {
                    $message .= 'Error. Could not make transaction.';
                    $errors = true;
                } else {
                    $this->view->status_bar_footer->set_status_text('Transfer ok.', Status_Bar::STATUS_SUCCESS);
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
