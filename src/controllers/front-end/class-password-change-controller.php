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

use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlComponents\Status_Bar;

/**
 * Summary.
 *
 * Description.
 */
class Password_Change_Controller extends Membership_Controller {

	/** @var Password_Change_View */
	public $view;

	/**
	 * Construction.
	 */
	public function __construct() {
		$secret          = null;
		$secret_required = false;

		$action = Security_Filter::safe_read_post_request( 'action', Security_Filter::STRING_KEY_NAME );
		if ( Bitcoin_Bank_Plugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER === $action ) {
			$event   = Security_Filter::safe_read_post_request( '_event', Security_Filter::STRING_KEY_NAME );
			$reg_id  = Security_Filter::safe_read_post_request( self::REG_ID, Security_Filter::POSITIVE_INTEGER_ZERO );
			$nonce   = Security_Filter::safe_read_post_request( self::REG_NONCE, Security_Filter::ALPHA_NUM );
			$post_id = Security_Filter::safe_read_post_request( self::REG_POST_ID, Security_Filter::POSITIVE_INTEGER );
		} else {
			$event   = Security_Filter::safe_read_get_request( self::REG_EVENT, Security_Filter::STRING_KEY_NAME );
			$reg_id  = Security_Filter::safe_read_get_request( self::REG_ID, Security_Filter::POSITIVE_INTEGER_ZERO );
			$nonce   = Security_Filter::safe_read_get_request( self::REG_NONCE, Security_Filter::ALPHA_NUM );
			$post_id = get_the_ID();
		}

		$reg_type = Registration_Db_Table::REG_TYPE_PASSWORD_RECOVERY;

		parent::__construct( 'BCQ_BitcoinBank\Password_Change_View', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required );

		$this->set_permission( true );
	}

	/**
	 *
	 */
	public function button_save_password_click() {
		$this->hide_input_error_indications();

		$password         = $this->view->password1->get_text();
		$confirm_password = $this->view->password2->get_text();

		if ( $this->check_password_change_data( $password, $confirm_password ) ) {
			$wp_user_id = get_current_user_id();
			if ( $this->update_password( $password, $wp_user_id ) ) {
				$this->reload_view( 'BCQ_BitcoinBank\General_Message_View' );
				$this->view->status_bar_footer->set_status_text( esc_html__( 'Password has been changed.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );

				/*
				 * Log-out nonce will change after password change, need update existing log-out
				 * links in the browser next time ajax is loaded.
				 */
				$logout_url = wp_logout_url();
				$logout_url = str_replace( '&amp;', '&', $logout_url );
				$this->view->update_client_dom( 'a.bcq_logout_href', 'attr', array( 'href', $logout_url ) );
			} else {
				$this->view->status_bar_footer->set_status_text( esc_html__( 'Error saving password. Please retry with another password.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );
			}
		} else {
			$message = $this->get_server_context_data( 'response_message' );
			$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
		}

		$this->show_onput_error_indications();
	}
}
