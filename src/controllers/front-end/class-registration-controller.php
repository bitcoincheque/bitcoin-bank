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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlComponents\Html_Text;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Registration_Controller extends Membership_Controller {

	/** @var Registration_View | Register_User_Data_View | General_Message_View */
	public $view;

	private $insert_data_if_secret_ok = false;
	private $user_bcq_data_invalid = false;

	/**
	 * Construction.
	 */
	public function __construct() {
		$secret          = null;
		$secret_required = false;

		$action = Security_Filter::safe_read_post_request( 'action', Security_Filter::STRING_KEY_NAME );
		if ( Bitcoin_Bank_Plugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER === $action ) {
			$event   = Security_Filter::safe_read_post_request( self::REG_EVENT, Security_Filter::STRING_KEY_NAME );
			$reg_id  = Security_Filter::safe_read_post_request( self::REG_ID, Security_Filter::POSITIVE_INTEGER_ZERO );
			$post_id = Security_Filter::safe_read_post_request( self::REG_POST_ID, Security_Filter::POSITIVE_INTEGER );
			$nonce   = Security_Filter::safe_read_post_request( self::REG_NONCE, Security_Filter::ALPHA_NUM );
		} else {
			$event  = null;
			$reg_id = null;
			$nonce  = null;

			/* The link code can contain alphanumeric and hyphen and underscore characters. */
			$verification_status = Security_Filter::safe_read_get_request( 'verification', Security_Filter::STRING_KEY_NAME );

			if ( isset( $verification_status ) ) {
				/* Don't flood the debug log due to hacking attempt. Write to buffer until nonce confirmed. */
				Debug_Logger::pause_wp_debug_logging();

				$data = $this->decode_email_url_data( $verification_status );

				if ( isset( $data ) ) {
					$event = 'confirm_email';

					$reg_id          = Security_Filter::safe_read_array_item( $data, 'r', Security_Filter::POSITIVE_INTEGER_ZERO );
					$secret          = Security_Filter::safe_read_array_item( $data, 's', Security_Filter::ALPHA_NUM );
					$secret_required = true;

					$this->insert_data_if_secret_ok = true;
				} else {
					$this->user_bcq_data_invalid = true;
				}
			}

			$post_id = get_the_ID();
		}

		$reg_type = Registration_Db_Table::REG_TYPE_USER_REGISTRATION;

		if($this->user_bcq_data_invalid === False) {
			parent::__construct( 'BCQ_BitcoinBank\Registration_View', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required );
		} else {
			parent::__construct( 'BCQ_BitcoinBank\General_Message_View', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required );
		}

		$this->set_init_callback( 'Registering' );
		$this->register_event( 'Registering', 'init', 'post' );
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

					$this->register_event( 'confirm_email', 'get', 'get' );
					$this->set_event( 'confirm_email' );
				}
			}
		}

		return $nonce_protected_data;
	}

	/**
	 * @param $event
	 */
	public function registering_init( $event ) {
		if ( is_user_logged_in() ) {
			$current_wp_user    = get_current_user_id();
			$registered_wp_user = $this->get_wp_user_id();
			if ( $current_wp_user !== $registered_wp_user ) {
				$this->log_out_user();
			}
		}

		if ( ! is_user_logged_in() ) {
			if ( 'button_register_click' === $event ) {
				switch ( $this->get_state() ) {
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
						$username = $this->view->username->get_text();
						$password = $this->view->password->get_text();

						if ( $this->check_register_user_data( $username, $password ) ) {
							if ( $this->register_username_password( $username, $password ) ) {
								if ( $this->has_all_required_info() ) {
									if ( ! $this->email_exist() ) {
										if ( ! $this->user_exist() ) {
											$first_name = null;
											$last_name  = null;

											if ( $this->check_and_save_name() ) {
												$access_options = get_option( Settings_Access_Options::OPTION_NAME );
												if ( $access_options[ Settings_Access_Options::APPROVE_NEW_USERS ] ) {
													$this->set_state( Registration_Db_Table::STATE_APPROVAL_PENDING );

													$post_id = $this->get_page_id();
													Statistics::statistics_completed( $post_id );

													$linking_options = new Settings_Linking_Options();
													$login_url       = $linking_options->get_login_url();
													$login_url       = add_query_arg( 'bcq_registering', 'completed', $login_url );

													wp_redirect( $login_url );
													exit();
												} else {
													if ( $this->create_new_user() ) {
														$wp_user_id = $this->model->get_data( Registration_Db_Table::WP_USER_ID );
														$this->send_email_notification_new_user( $wp_user_id );

														$post_id = $this->get_page_id();
														Statistics::statistics_completed( $post_id );

														$login_url = wp_login_url();
														$login_url = add_query_arg( 'bcq_registering', 'completed', $login_url );

														wp_redirect( $login_url );
														exit();
													} else {
														$this->response_set_error( esc_html__( 'Username has already been registered. Log in or register using another e-mail.', 'bitcoin-bank' ) );
													}
												}
											}
										} else {
											$msg = esc_html__( 'Username already taken. Please select another username.', 'bitcoin-bank' );
											$this->response_set_error( $msg );
										}
									} else {
										$email           = $this->get_email();
										$linking_options = new Settings_Linking_Options();
										$login_url       = $linking_options->get_login_url();
										$login_url       = add_query_arg( 'bcq_registering', 'emailexist', $login_url );
										$login_url       = add_query_arg( 'bcq_email', $email, $login_url );
										wp_redirect( $login_url );
										exit();
									}
								} else {
									$this->response_set_error( esc_html__( 'Error registering. Please retry or contact site admin if problem persists.', 'bitcoin-bank' ) );
								}
							} else {
								$this->response_set_error( 'Server error.' );
							}
						}
						break;

					case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
						$linking_options = new Settings_Linking_Options();
						$login_url       = $linking_options->get_login_url();
						$login_url       = add_query_arg( 'bcq_registering', 'completed', $login_url );
						wp_redirect( $login_url );
						exit();
					break;

					default:
						Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
				}
			} elseif ( 'ConfirmEmail_get' === $event ) {
				switch ( $this->get_state() ) {
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
						if ( $this->email_exist() ) {
							$email           = $this->get_email();
							$linking_options = new Settings_Linking_Options();
							$login_url       = $linking_options->get_login_url();
							$login_url       = add_query_arg( 'bcq_registering', 'emailexist', $login_url );
							$login_url       = add_query_arg( 'bcq_email', $email, $login_url );
							wp_redirect( $login_url );
							exit();
						}
						break;

					case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
						$linking_options = new Settings_Linking_Options();
						$login_url       = $linking_options->get_login_url();
						$login_url       = add_query_arg( 'bcq_registering', 'completed', $login_url );
						wp_redirect( $login_url );
						exit();
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
	public function button_start_registration_click() {
		$this->hide_input_error_indications();

		$email          = $this->view->email->get_text();
		$terms_accepted = $this->read_terms_from_view();
		$email          = trim( $email );

		if ( defined('BITCOIN_BANK_DEBUG_DONT_SEND_EMAIL')) {
			if ( ! strstr($email, '@' ) ) {
				$email = $email . '@xxx.xx';
			}
		}

		if ( $this->check_Registration_email_data( $email ) ) {
			if ( $this->check_registration_terms_accepted( $terms_accepted ) ) {
				if ( $this->register_email( $email ) ) {
					$this->set_state( Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT );

					$controller = new General_Message_Controller();
					$this->reload_controller( $controller );
					$s               = new Strong( $email );
					$formatted_email = $s->draw_html();
					/* translators: %s: E-mail address. */
					$msg = sprintf( esc_html__( 'An e-mail has been sent to %s. Please check your e-mail.', 'bitcoin-bank' ), $formatted_email );
					$controller->view->status_bar_header->set_status_html( $msg, Status_Bar::STATUS_SUCCESS );

					if ( defined('BITCOIN_BANK_DEBUG_DONT_SEND_EMAIL')) {
						$link = new Html_Text( '<a href="' . $this->verification_link . '">' . $this->verification_link . '</a>' );
						$controller->view->status_bar_footer->set_status_text( $link, Status_Bar::STATUS_INFO );
					}

					$post_id = $this->get_page_id();
					Statistics::statistics_register( $post_id );
					Statistics::statistics_verify_email( $post_id );
				} else {
					$message = $this->get_error_message();
					/* This message may have email in <strong>, use Html setter */
					$this->view->status_bar_footer->set_status_html( $message, Status_Bar::STATUS_ERROR );
				}
			} else {
				$message = $this->get_server_context_data( 'response_message' );
				$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
			}
		} else {
			$message = $this->get_server_context_data( 'response_message' );
			$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
		}

		$this->show_onput_error_indications();
	}

	/**
	 *
	 */
	public function confirm_email_get() {
		if ( is_user_logged_in() ) {
			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
					$controller = new Login_Controller();
					$this->reload_controller( $controller );
					$controller->view->status_bar_header->set_status_text( esc_html__( 'Registration link has been used and user created. You can now log in.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		} else {
			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
					$controller = new Login_Controller();
					$this->reload_controller( $controller );
					$controller->view->status_bar_header->set_status_text( esc_html__( 'Registration link has been used and user created. You can now log in.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );
					break;

				case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
				case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
					if ( ! $this->email_exist() ) {

						$this->reload_view( 'BCQ_BitcoinBank\Register_User_Data_View' );
						$s               = new Strong( $this->get_email() );
						$formatted_email = $s->draw_html();
						/* translators: %s: E-mail address. */
						$msg = sprintf( esc_html__( 'You are registering using e-mail address %s', 'bitcoin-bank' ), $formatted_email );
						$this->view->status_bar_header->set_status_html( $msg, Status_Bar::STATUS_SUCCESS );
					} else {
						$email = $this->get_email();

						/* Create a new registration record to remove old registration data. However, not saved. */
						$this->create_new_registration_record( Registration_Db_Table::REG_TYPE_USER_REGISTRATION, get_the_ID() );

						$controller = new Login_Controller();
						$this->reload_controller( $controller );
						$s               = new Strong( $email );
						$formatted_email = $s->draw_html();
						/* translators: %s: E-mail address. */
						$msg = sprintf( esc_html__( 'User account with e-mail %s already exists. You can now log in.', 'bitcoin-bank' ), $formatted_email );
						$controller->view->status_bar_header->set_status_html( $msg, Status_Bar::STATUS_ERROR );
					}
					$this->set_state( Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED );
					break;

				case Registration_Db_Table::STATE_APPROVAL_PENDING:
					$this->send_email_notification_new_user();

					$this->reload_view( 'BCQ_BitcoinBank\General_Message_View' );

					$design_options = get_option( Settings_Form_Options::OPTION_NAME );
					$header_text    = $design_options[ Settings_Form_Options::PENDING_APPROVAL_HEADER_TEXT ];
					$message_text   = $design_options[ Settings_Form_Options::PENDING_APPROVAL_MESSAGE_TEXT ];
					$this->view->header->set_content( $header_text );
					$this->view->status_bar_footer->set_text( $message_text );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		}
	}

	/**
	 *
	 */
	public function button_register_click() {
		$this->hide_input_error_indications();

		if ( ! is_user_logged_in() ) {
			switch ( $this->get_state() ) {
				case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
				case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
					if ( $this->email_exist() ) {
						$this->reload_view( 'BCQ_BitcoinBank\Registration_View' );
						$message = $this->get_server_context_data( 'response_message' );
						$this->view->status_bar_header->set_status_text( $message, Status_Bar::STATUS_ERROR );
					} else {
						$s               = new Strong( $this->get_email() );
						$formatted_email = $s->draw_html();
						/* translators: %s: E-mail address. */
						$msg = sprintf( esc_html__( 'You are registering using e-mail address %s', 'bitcoin-bank' ), $formatted_email );
						$this->view->status_bar_header->set_status_html( $msg, Status_Bar::STATUS_SUCCESS );

						$message = $this->get_server_context_data( 'response_message' );
						$this->view->status_bar_footer->set_status_text( $message, Status_Bar::STATUS_ERROR );
					}
					break;

				case Registration_Db_Table::STATE_APPROVAL_PENDING:
					$this->send_email_notification_new_user();

					$this->reload_view( 'BCQ_BitcoinBank\General_Message_View' );

					$design_options = get_option( Settings_Form_Options::OPTION_NAME );
					$header_text    = $design_options[ Settings_Form_Options::PENDING_APPROVAL_HEADER_TEXT ];
					$message_text   = $design_options[ Settings_Form_Options::PENDING_APPROVAL_MESSAGE_TEXT ];
					$this->view->header->set_content( $header_text );
					$this->view->status_bar_footer->set_text( $message_text );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled state ' . $this->get_state() );
			}
		}

		$this->show_onput_error_indications();
	}

	/**
	 * @param null $parameters
	 *
	 * @return string
	 */
	public function draw_view( $parameters = null ) {
		if ( ! is_user_logged_in() ) {
			$post_id = $this->get_page_id();
			Statistics::statistics_page_view( $post_id );
		}

		if($this->user_bcq_data_invalid === true)
		{
			$msg = esc_html__( 'Error in registration link. Ensure the complete link is copied.', 'bitcoin-bank' );
			$this->view->status_bar_header->set_status_html($msg, Status_Bar::STATUS_ERROR);
		}

		return parent::draw_view( $parameters );
	}
}
