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

use WP_PluginFramework\HtmlElements\Input_Hidden;
use WP_PluginFramework\Pages\Admin_Page;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlElements\Button;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlComponents\Status_Bar;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Pages {

	static $admin_menu_page;
	static $admin_info_page;

	public static function admin_menu() {
		if ( current_user_can( 'manage_options' )) {
            $icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAABHklEQVQ4T2NkoDJgpLJ5DAQNPHvh8v89Bw6D7XVxsGUwNtDFqwen5OoNW/4/fPyUgZOTg4GdjQ1s4M9fvxi+f//BIC8rzRAa4INVL4rgqbMX/j959pwBZhALMzPWEPnz9y/cYBkpSQYzYwO4OWDGwSMnwN7i4eFmYGZmBmNiwN+/fxlA+MuXr+DgsLexYCQYhsQYjKwGxcDmron/QZK1ZflgcVL5ID0oBvZPmwM2sDArBSxOKn/UQAaGzgnTwGFYXpAFDkNS+RhhePzUWbCBlmbGYANJ5WMYSGqaw6YeI+sRm0tghv369QvuIxQXgsILlPVIBaCsJyYqwhAR5Ad2HJioben+LyQkSKpZcPUgVwoLCTKkxEXSOC+T7UQkjQBlZLkVV6hqtgAAAABJRU5ErkJggg==';

			$main_bubble_notification     = '';
			$sub_menu_bubble_notification = '';

			$pending_approval = Registration_Db_Table::get_pending_approval_counter();

			if ( $pending_approval > 0 ) {
				$page = Security_Filter::safe_read_get_request( 'page', Security_Filter::STRING_KEY_NAME );
				if ( ! ( isset( $page )
				&& ( ( 'bcq-admin-menu' === $page ) || ( 'bcq-admin-registration-status' === $page ) || ( 'bcq-admin-statistics' === $page ) || ( 'bcq-admin-help' === $page ) ) ) ) {
					$main_bubble_notification = ' <span class="awaiting-mod">' . $pending_approval . '</span>';
				}

				$sub_menu_bubble_notification = ' <span class="awaiting-mod">' . $pending_approval . '</span>';
			}

			if ( current_user_can( 'manage_options' ) ) {
				add_menu_page(
					'bcq-admin',
					'Bitcoin Bank' . $main_bubble_notification,
					'manage_options',
					'bcq-admin-menu',
					array(
						'BCQ_BitcoinBank\Admin_Pages',
						'admin_financials_page',
					),
					$icon
				);

				self::$admin_menu_page = add_submenu_page(
					'bcq-admin-menu',
					esc_html__( 'Financials', 'bitcoin-bank' ),
					esc_html__( 'Financials', 'bitcoin-bank' ),
					'manage_options',
					'bcq-admin-menu',
					array(
						'BCQ_BitcoinBank\Admin_Pages',
						'admin_financials_page',
					)
				);

                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Clients', 'bitcoin-bank' ),
                    'Clients',
                    'manage_options',
                    'bcq-admin-clients',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_clients_page',
                    )
                );

                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Accounts', 'bitcoin-bank' ),
                    'Accounts',
                    'manage_options',
                    'bcq-admin-accounts',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_accounts_page',
                    )
                );


                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Transactions', 'bitcoin-bank' ),
                    'Transactions',
                    'manage_options',
                    'bcq-admin-transactions',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_transactions_page',
                    )
                );

                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Cheques', 'bitcoin-bank' ),
                    'Cheques',
                    'manage_options',
                    'bcq-admin-cheques',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_cheques_page',
                    )
                );

                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Operations', 'bitcoin-bank' ),
                    'Operations',
                    'manage_options',
                    'bcq-admin-operations',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_operations_page',
                    )
                );

                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Wallets', 'bitcoin-bank' ),
                    'Wallets',
                    'manage_options',
                    'bcq-admin-wallets',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_wallets_page',
                    )
                );

                add_submenu_page(
					'bcq-admin-menu',
					esc_html__( 'Registrations', 'bitcoin-bank' ),
					'Registrations' . $sub_menu_bubble_notification,
					'manage_options',
					'bcq-admin-registration-status',
					array(
						'BCQ_BitcoinBank\Admin_Registrations_Page',
						'show_registrations_page',
					)
				);

                add_submenu_page(
                    'bcq-admin-menu',
                    esc_html__( 'Settings', 'bitcoin-bank' ),
                    'Settings',
                    'manage_options',
                    'bcq-admin-settings',
                    array(
                        'BCQ_BitcoinBank\Admin_Pages',
                        'admin_settings_page',
                    )
                );
			}
		}
	}

	public static function admin_settings_page() {
        $page = new Admin_Settings_Tab_Page();
        $html = $page->draw();
        echo $html;
	}

    public static function admin_financials_page() {
        $page = new Admin_Financials_Tab_Page();
        $html = $page->draw();
        echo $html;
    }

    public static function admin_accounts_page() {
        $page = new Admin_Accounts_Tab_Page();
        $html = $page->draw();
        echo $html;
    }

    public static function admin_transactions_page()
    {
        $panel = null;

        $account_id = Security_Filter::safe_read_get_request('account_id', Security_Filter::POSITIVE_INTEGER_ZERO);

        if(isset($account_id)) {
            $account_info_controller = new Account_Header_Controller( $account_id );
            $transaction_list_controller = new Account_Transaction_List_Controller( $account_id );
            $page = new Admin_Page();
            $page->add_content($account_info_controller);
            $page->add_content(new P());
            $page->add_content($transaction_list_controller);
            echo $page->draw();
        } else {
            $transaction_list_controller = new Transaction_List_Controller();
            $page = new Admin_Page($transaction_list_controller);
            echo $page->draw();
        }
    }

    public static function admin_cheques_page()
    {
        Cheque_Handler::check_expired();

        $cheque_id = Security_Filter::safe_read_get_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);

        if(isset($cheque_id)) {
            $controller = new Cheque_Details_Controller($cheque_id);
            $page = new Admin_Page($controller);
            echo $page->draw();
        } else {
            $controller = new Cheque_List_Controller();
            $page = new Admin_Page($controller);
            echo $page->draw();
        }
    }

    public static function admin_clients_page()
    {
        $clients_id = Security_Filter::safe_read_get_request('client_id', Security_Filter::POSITIVE_INTEGER_ZERO);

        if(isset($clients_id)) {
            $controller_client_details = new Client_Details_Controller($clients_id);
            $controller_account_list = new Account_List_Controller($clients_id);

            $page = new Admin_Page($controller_client_details);
            $page->add_content(new P());
            $page->add_content($controller_account_list);
            echo $page->draw();
        } else {
            $controller = new Client_List_Controller();
            $page = new Admin_Page($controller);
            echo $page->draw();
        }
    }

    public static function admin_operations_page() {
        $page = new Admin_Operations_Tab_Page();
        $html = $page->draw();
        echo $html;
    }

    public static function admin_wallets_page() {
        $page = new Admin_Wallets_Tab_Page();
        $html = $page->draw();
        echo $html;
    }
}
