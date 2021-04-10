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

use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Login_Controller extends Membership_Controller {

	/** @var Login_View | Logout_View */
	public $view;

	/**
	 * Construction.
	 */
	public function __construct() {
		$secret          = null;
		$secret_required = false;

		$event      = Security_Filter::safe_read_post_request( self::REG_EVENT, Security_Filter::STRING_KEY_NAME );
		$nonce      = Security_Filter::safe_read_post_request( self::REG_NONCE, Security_Filter::ALPHA_NUM );
		$event_type = Security_Filter::safe_read_post_request( '_event_type', Security_Filter::STRING_KEY_NAME );

		$reg_id   = null;
		$reg_type = Registration_Db_Table::REG_TYPE_LOGIN;

		if ( 'click' === $event_type ) {
			$post_id = Security_Filter::safe_read_post_request( self::REG_POST_ID, Security_Filter::POSITIVE_INTEGER );
		} else {
			$post_id = get_the_ID();
		}

		$key = 'bcq_bitcoin_bank_user_created';
		if ( isset( $GLOBALS[ $key ] ) ) {
			/*
			 * If user has just been created, and we reloaded controller to log in, must recreate the nonce as the one
			 * created by the registration controller is not valid for this controller.
			 */
			$nonce = $this->create_nonce( $reg_type, $post_id );
		}

		$this->set_init_callback( 'init_login' );

		if ( is_user_logged_in() ) {
			parent::__construct( 'BCQ_BitcoinBank\Logout_View', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required );
		} else {
			parent::__construct( 'BCQ_BitcoinBank\Login_View', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required );
		}

		$this->register_event( 'init_login', 'init', 'post' );
	}

	/**
	 * @param $event
	 */
	public function init_login_init( $event ) {
		if ( is_user_logged_in() ) {
			if ( 'button_logout_click' === $event ) {
				$this->log_out_user();
				$logout_url = Membership::get_redirect_logout();
				if ( false !== $logout_url ) {
					wp_redirect( $logout_url );
					exit();
				}
			}
		} else {
			if ( 'button_login_click' === $event ) {
				switch ( $this->get_state() ) {
					case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
						$username = $this->view->username->get_text();
						$password = $this->view->password->get_text();
						$remember = $this->view->remember->get_value();

						if ( $this->check_login_data( $username, $password ) ) {
							if ( $this->log_in_user( $username, $password, $remember ) ) {
								$this->response_set_logged_in();

                                $login_redirect = Security_Filter::safe_read_post_request( 'login_redirect', Security_Filter::URL );
                                $login_redirect = filter_var($login_redirect, FILTER_SANITIZE_URL);
								if($login_redirect)
                                {
                                    wp_redirect($login_redirect);
                                    exit();
                                }

							} else {
								$this->response_set_error( esc_html__( 'Error. Wrong username or password.', 'bitcoin-bank' ) );
							}
						}
						break;

					default:
						Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
				}
			}
		}
	}

	/**
	 *
	 */
	public function button_login_click() {
		$this->hide_input_error_indications();

		if ( is_user_logged_in() ) {
            switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
					$this->reload_view( 'BCQ_BitcoinBank\Logout_View' );
					$message = $this->get_server_context_data( 'response_message' );
					$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_SUCCESS );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		} else {
			$message = $this->get_server_context_data( 'response_message' );
			if ( ! isset( $message ) ) {
				/* Should really have a message here, write to debug log. */
				Debug_Logger::write_debug_error( 'Could not log in and no error message to user.' );
			}
			$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
		}

		$this->show_onput_error_indications();
	}

	/**
	 *
	 */
	public function button_logout_click() {
		if ( ! is_user_logged_in() ) {
			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
					$this->reload_view( 'BCQ_BitcoinBank\Login_View' );
					$message = $this->get_server_context_data( 'response_message' );
					$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_SUCCESS );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		}
	}

	/**
	 * @param null $parameters
	 *
	 * @return string
	 */
	protected function draw_view( $parameters = null ) {
		$registering_status = Security_Filter::safe_read_get_request( 'bcq_registering', Security_Filter::ALPHA_NUM );
		if ( 'completed' === $registering_status ) {
			$parameters['status_message'] = sprintf( esc_html__( 'Registration completed. You can now log in.', 'bitcoin-bank' ) );
		} elseif ( 'completed2' === $registering_status ) {
			$parameters['status_message'] = esc_html__( 'User already created. You can now log in.', 'bitcoin-bank' );
		} elseif ( 'emailexist' === $registering_status ) {
			$email = '<strong>' . Security_Filter::safe_read_get_request( 'bcq_email', Security_Filter::EMAIL ) . '</strong>';
			/* translators: %s: E-mail address. */
			$parameters['status_message'] = sprintf( esc_html__( 'User account with e-mail %s already exists. You can now log in.', 'bitcoin-bank' ), $email );
		}

		$registering_status = Security_Filter::safe_read_get_request( 'bcq_password', Security_Filter::ALPHA_NUM );
		if ( 'completed' === $registering_status ) {
			$parameters['status_message'] = esc_html__( 'Password has been changed. You can now log in.', 'bitcoin-bank' );
		}

        $cheque_id = Security_Filter::safe_read_get_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
        if( $cheque_id ) {
            $parameters['show_cheque'] = true;
            $parameters['cheque_id'] = $cheque_id;
            $parameters['access_code'] = Security_Filter::safe_read_get_request('access_code', Security_Filter::STRING_KEY_NAME);

            $parameters['status_message'] = esc_html__( 'You must log in to claim the cheque, or register to create an account.', 'bitcoin-bank' );
        }

		return parent::draw_view( $parameters );
	}
}
