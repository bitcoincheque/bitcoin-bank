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
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Password_Recovery_Controller extends Membership_Controller {

	/** @var Password_Recovery_View | Password_Change_View*/
	public $view;

	private $insert_data_if_secret_ok = false;

	/**
	 * Construction.
	 */
	public function __construct() {
		$secret          = null;
		$secret_required = false;
		$set_event       = null;

		$action = Security_Filter::safe_read_post_request( 'action', Security_Filter::STRING_KEY_NAME );
		if ( Bitcoin_Bank_Plugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER === $action ) {
			$event = Security_Filter::safe_read_post_request( '_event', Security_Filter::STRING_KEY_NAME );

			$reg_id  = Security_Filter::safe_read_post_request( self::REG_ID, Security_Filter::POSITIVE_INTEGER_ZERO );
			$nonce   = Security_Filter::safe_read_post_request( self::REG_NONCE, Security_Filter::ALPHA_NUM );
			$post_id = Security_Filter::safe_read_post_request( self::REG_POST_ID, Security_Filter::POSITIVE_INTEGER );
		} else {
			$event  = null;
			$reg_id = null;
			$nonce  = null;

			/* The link code can contain alphanumeric and hyphen and underscore characters. */
			$verification_status = Security_Filter::safe_read_get_request( 'verification', Security_Filter::STRING_KEY_NAME );

			if ( isset( $verification_status ) ) {
				$data = $this->decode_email_url_data( $verification_status );

				if ( isset( $data ) ) {
					$event     = 'password_link';
					$set_event = $event;

					$reg_id          = Security_Filter::safe_read_array_item( $data, 'r', Security_Filter::POSITIVE_INTEGER_ZERO );
					$secret          = Security_Filter::safe_read_array_item( $data, 's', Security_Filter::ALPHA_NUM );
					$secret_required = true;

					$this->insert_data_if_secret_ok = true;
				}
			}

			$post_id = get_the_ID();
		}

		$reg_type = Registration_Db_Table::REG_TYPE_PASSWORD_RECOVERY;

		parent::__construct( 'BCQ_BitcoinBank\Password_Recovery_View', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required );

		if ( isset( $set_event ) ) {
			$this->set_event( $set_event );
		}

		$this->register_event( 'init_password_change', 'init', 'post' );
	}

	/**
	 * @param $event
	 */
	public function init_password_change_init( $event ) {
		if ( ! is_user_logged_in() ) {
			if ( 'button_save_password_click' === $event ) {
				$password1 = $this->view->password1->get_text();
				$password2 = $this->view->password2->get_text();

				switch ( $this->get_state() ) {
					case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
						if ( $this->check_password_change_data( $password1, $password2 ) ) {
							$wp_user_id = $this->get_wp_user_id();
							wp_set_password( $password1, $wp_user_id );

							$this->set_state( Registration_Db_Table::STATE_RESET_PASSWORD_DONE );

							$linking_options = new Settings_Linking_Options();
							$login_url       = $linking_options->get_login_url();
							$login_url       = add_query_arg( 'bcq_password', 'completed', $login_url );
							wp_redirect( $login_url );
							exit();
						}
						break;

					default:
						Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
				}
			}
		}
	}

	/**
	 * @param $event_source
	 *
	 * @return array|null
	 */
	protected function read_nonce_protected_data( $event_source ) {
		$nonce_protected_data = parent::read_nonce_protected_data( $event_source );

		if ( ! $nonce_protected_data ) {
			/* Get url contains no nonce protected data. Must recreate it before executing events. The events check for nonce.  */
			if ( $this->insert_data_if_secret_ok ) {
				if ( $this->has_user_data() ) {
					$nonce_protected_data = $this->get_nonce_protected_data();
					$nonce_protected_data = $this->calculate_wp_nonce( $nonce_protected_data );

					$this->register_event( 'password_link', 'get', 'get' );
					$this->set_event( 'password_link' );
				}
			}
		}

		return $nonce_protected_data;
	}

	/**
	 *
	 */
	public function button_send_password_click() {
		$this->hide_input_error_indications();

		if ( ! is_user_logged_in() ) {
			$email = $this->view->email->get_text();

			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
					$wp_user_id = $this->check_send_password_link_data( $email );
					if ( false !== $wp_user_id ) {
						if ( $this->send_email_reset_link( $email, $wp_user_id ) ) {
							$this->set_state( Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT );

							$this->reload_view( 'BCQ_BitcoinBank\General_Message_View' );
							$s               = new Strong( $email );
							$formatted_email = $s->draw_html();
							/* translators: %s: E-mail address. */
							$msg = sprintf( esc_html__( 'E-mail sent to %s with username and password reset link.', 'bitcoin-bank' ), $formatted_email );
							$this->view->status_bar_header->set_status_html( $msg, Status_Bar::STATUS_SUCCESS );

							$msg = esc_html__( 'In case you don\'t see the e-mail, also check your e-mail application\'s spam folder.', 'bitcoin-bank' );
							$this->view->status_bar_footer->set_status_text( $msg, Status_Bar::STATUS_SUCCESS );
						} else {
							$s               = new Strong( $email );
							$formatted_email = $s->draw_html();
							/* translators: %s: E-mail address. */
							$msg = sprintf( esc_html__( 'Error sending e-mail to %s. Retry later or contact site admin if problem persists.', 'bitcoin-bank' ), $formatted_email );
							$this->view->status_bar_footer->set_status_html( $msg, Status_Bar::STATUS_ERROR );
						}
					} else {
						$message = $this->get_server_context_data( 'response_message' );
						$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
					}
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		}

		$this->show_onput_error_indications();
	}

	/**
	 *
	 */
	public function button_save_password_click() {
		$this->hide_input_error_indications();

		if ( ! is_user_logged_in() ) {
			$password1 = $this->view->password1->get_text();
			$password2 = $this->view->password2->get_text();

			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
					if ( $this->check_password_change_data( $password1, $password2 ) ) {
						$wp_user_id = $this->get_wp_user_id();
						wp_set_password( $password1, $wp_user_id );

						$this->set_state( Registration_Db_Table::STATE_RESET_PASSWORD_DONE );

						$controller = new Login_Controller();
						$this->reload_controller( $controller );
						$this->view->status_bar_header->set_status_text( esc_html__( 'Password has been changed. You can now log in.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );
					} else {
						$message = $this->get_server_context_data( 'response_message' );
						$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
					}
					break;

				case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
					$controller = new Login_Controller();
					$this->reload_controller( $controller );
					$controller->view->status_bar_footer->set_status_text( esc_html__( 'This password reset link has been used. The link can only be used once. You can now log in or you need to request a new password reset link.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );
					break;

				case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
					$this->view->status_bar_footer->set_status_text( esc_html__( 'This password reset link has expired. You must request a new e-mail with a new reset link.', 'bitcoin-bank' ), Status_Bar::STATUS_ERROR );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );

			}
		}

		$this->show_onput_error_indications();
	}

	/**
	 *
	 */
	public function password_link_get() {
		$this->hide_input_error_indications();

		if ( ! is_user_logged_in() ) {
			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
				case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
					$wp_user_id = $this->get_wp_user_id();
					if ( $wp_user_id ) {
						$this->set_init_callback( 'init_password_change' );
						$this->reload_view( 'BCQ_BitcoinBank\Password_Recovery_Change_View' );
						$s                  = new Strong( $this->get_username() );
						$formatted_username = $s->draw_html();
						/* translators: %s: Username. */
						$msg = sprintf( esc_html__( 'Select a new password for username %s.', 'bitcoin-bank' ), $formatted_username );
						$this->view->status_bar_header->set_status_html( $msg, Status_Bar::STATUS_SUCCESS );
					} else {
						$s               = new Strong( $this->get_email() );
						$formatted_email = $s->draw_html();
						/* translators: %s: E-mail address. */
						$msg = sprintf( esc_html__( 'No user with e-mail address %s.', 'bitcoin-bank' ), $formatted_email );
						$this->view->status_bar_footer->set_status_html( $msg, Status_Bar::STATUS_ERROR );
					}
					$this->set_state( Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM );
					break;

				case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
					$reg_type = Registration_Db_Table::REG_TYPE_PASSWORD_RECOVERY;
					$post_id  = get_the_ID();
					$this->create_new_registration_record( $reg_type, $post_id );
					$this->reload_view();
					$this->view->status_bar_header->set_status_text( esc_html__( 'This password reset link has been used. The link can only be used once. You can now log in or you need to request a new password reset link.', 'bitcoin-bank' ), Status_Bar::STATUS_ERROR );
					break;

				case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
					$reg_type = Registration_Db_Table::REG_TYPE_PASSWORD_RECOVERY;
					$post_id  = get_the_ID();
					$this->create_new_registration_record( $reg_type, $post_id );
					$this->reload_view();
					$this->view->status_bar_header->set_status_text( esc_html__( 'This password reset link has been used. The link can only be used once. You can now log in or you need to request a new password reset link.', 'bitcoin-bank' ), Status_Bar::STATUS_ERROR );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		}

		$this->show_onput_error_indications();
	}
}
