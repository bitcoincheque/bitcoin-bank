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
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Mailer;
use WP_PluginFramework\Utils\Misc;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
abstract class Membership_Controller extends Std_Controller {

	const BCQ_BITCOIN_BANK_REGISTRATION_COOKIE_NAME = 'bitcoin_bank_registration';

	const SECRET_LENGTH = 20;

	/* Constants for AJAX POST and GET requests */
	const REG_ID           = 'rid';
	const REG_TYPE         = 'rt_ype';
	const REG_NONCE        = 'nonce';
	const REG_EVENT        = '_event';
	const REG_POST_ID      = 'post_id';




	const RESULT_OK                   = 0;
	const RESULT_NONCE_ERROR          = 1;
	const RESULT_CONFIRM_INVALID_LINK = 2;
	const RESULT_ERROR_UNDEFINED      = 3;
	const RESULT_CONFIRM_IS_DONE      = 4;
	const RESULT_USER_EXIST           = 5;
	const RESULT_CONFIRM_EXPIRED_LINK = 6;

	/** @var Login_View | Logout_View */
	public $view;

	private $has_data      = false;
	private $error_message = '';

	protected $test_mode = false;
	protected $verification_link = '';

	/**
	 * Membership_Controller constructor.
	 *
	 * @param $view
	 * @param $event
	 * @param $reg_id
	 * @param $reg_type
	 * @param $post_id
	 * @param $nonce
	 * @param $secret
	 * @param $secret_required
	 */
	public function __construct( $view, $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required ) {
		parent::__construct( 'BCQ_BitcoinBank\Registration_Db_Table', $view );

		if ( isset( $event ) ) {
			$this->has_data = false;

			if ( null !== $reg_id ) {
				if ( $this->model->load_data( Registration_Db_Table::PRIMARY_KEY, $reg_id ) ) {
					$this->has_data = false;

					if ( $secret_required ) {
						/* Don't require nonce from e-mail verification links. */
						$my_secret = $this->model->get_data( Registration_Db_Table::SECRET );
						if ( '' !== $my_secret ) {
							if ( $my_secret === $secret ) {
								$this->has_data = true;
							}
						}
					} else {
						$my_nonce = $this->model->get_data( Registration_Db_Table::NONCE );
						if ( '' !== $my_nonce ) {
							if ( $my_nonce === $nonce ) {
								$this->has_data = true;
							}
						}
					}

					$state = $this->model->get_data( Registration_Db_Table::STATE );
					if ( Registration_Db_Table::STATE_REGISTRATION_EXPIRED === $state
						|| Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED === $state ) {
						$this->has_data = $this->create_registration_record( $reg_type, $post_id );

						$this->model->set_data( Registration_Db_Table::STATE, $state );
					}
				} else {
					/* No registration record found, it may have been deleted due to expiration. */
					$this->has_data = $this->create_registration_record( $reg_type, $post_id );
				}
			} else {
				if ( isset( $nonce ) && isset( $reg_type ) && isset( $post_id ) ) {
					$nonce = $this->check_nonce_changed( $nonce );

					$nonce_string = $this->format_nonce_string( $reg_type, $post_id );

					if ( wp_verify_nonce( $nonce, $nonce_string ) ) {
						$this->model->set_data( Registration_Db_Table::NONCE, $nonce );

						$cookie = $this->membership_get_cookie();
						$this->model->set_data( Registration_Db_Table::COOKIE, $cookie );
						$this->model->set_data( Registration_Db_Table::TIMESTAMP, time() );
						$this->model->set_data( Registration_Db_Table::REG_TYPE, $reg_type );
						$this->model->set_data( Registration_Db_Table::POST_ID, $post_id );
						$this->has_data = true;
					} else {
						$this->model = new Registration_Db_Table();
					}
				} else {
					$this->model = new Registration_Db_Table();
				}
			}

			if ( false === $this->has_data ) {
				/* Recreate object to destroy existing object with user data */
				$this->model = new Registration_Db_Table();
			}
		} else {
			if ( ! ( $this->test_mode ) ) {
				$this->has_data = $this->create_registration_record( $reg_type, $post_id );
			}
		}

		$this->set_permission( false );
	}

	/**
	 *
	 */
	protected function add_hidden_fields() {
		$post_id = $this->get_page_id();
		if ( $post_id ) {
			$this->view->add_hidden_fields( self::REG_POST_ID, $post_id );
		}

		$reg_id = $this->get_reg_id();
		if ( $reg_id ) {
			$this->view->add_hidden_fields( self::REG_ID, $reg_id );
		}

		$reg_type = $this->get_reg_type();
		if ( $reg_type ) {
			$this->view->add_hidden_fields( self::REG_TYPE, $reg_type );
		}

		$nonce = $this->get_nonce();
		if ( $nonce ) {
			$this->view->add_hidden_fields( self::REG_NONCE, $nonce );
		}
	}

	/**
	 * @param $reg_type
	 * @param $post_id
	 *
	 * @return bool
	 */
	protected function create_new_registration_record( $reg_type, $post_id ) {
		$this->model = new Registration_Db_Table();

		return $this->create_registration_record( $reg_type, $post_id );
	}

	/**
	 * @return bool|float|int|string|null
	 */
	public function membership_get_cookie() {
		return Security_Filter::safe_read_cookie_string( self::BCQ_BITCOIN_BANK_REGISTRATION_COOKIE_NAME, Security_Filter::ALPHA_NUM );
	}

	/**
	 * @param $reg_type
	 * @param $post_id
	 *
	 * @return bool
	 */
	private function create_registration_record( $reg_type, $post_id ) {
		$nonce = $this->create_nonce( $reg_type, $post_id );

		if ( $nonce ) {
			$this->model->set_data( Registration_Db_Table::NONCE, $nonce );

			$cookie = $this->membership_get_cookie();
			$this->model->set_data( Registration_Db_Table::COOKIE, $cookie );
			$this->model->set_data( Registration_Db_Table::TIMESTAMP, time() );
			$this->model->set_data( Registration_Db_Table::REG_TYPE, $reg_type );
			$this->model->set_data( Registration_Db_Table::POST_ID, $post_id );

			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $nonce
	 */
	private function update_nonce_changed( $nonce ) {
		 /**
		  * After logout and login the nonce for the current session is not valid any more. The login state is changed
		  * during the init phase, and nonce needs to be updated before executing code in handlers. Create a new nonce
		  * and store it for nonce verification during this http request.
		  */
		if ( $nonce ) {
			$reg_type  = $this->get_reg_type();
			$post_id   = $this->get_page_id();
			$new_nonce = $this->create_nonce( $reg_type, $post_id );
			if ( $new_nonce ) {
				$key             = 'bcq_new_nonce_' . $nonce;
				$GLOBALS[ $key ] = $new_nonce;
			}

			$new_wp_nonce = $this->create_wp_nonce();
			if ( $new_wp_nonce ) {
				$this->set_server_context_data( 'new_wp_nonce', $new_wp_nonce );
			}
		}
	}

	/**
	 * @param array $nonce_protected_data
	 *
	 * @return bool
	 */
	protected function check_wp_nonce( $nonce_protected_data = array() ) {
		$new_wp_nonce = $this->get_server_context_data( 'new_wp_nonce' );
		if ( $new_wp_nonce ) {
			$nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] = $new_wp_nonce;
		}

		return parent::check_wp_nonce( $nonce_protected_data );
	}

	/**
	 * @param $nonce
	 *
	 * @return mixed
	 */
	private function check_nonce_changed( $nonce ) {
		/* In case we have been logged in by Init function, the nonce has changed */
		$key = 'bcq_new_nonce_' . $nonce;
		if ( isset( $GLOBALS[ $key ] ) ) {
			/* New nonce has been created by Init function during the login */
			$nonce = $GLOBALS[ $key ];
		}
		return $nonce;
	}

	/**
	 * @param $reg_type
	 * @param $post_id
	 *
	 * @return bool|false|string
	 */
	protected function create_nonce( $reg_type, $post_id ) {
		$nonce_string = $this->format_nonce_string( $reg_type, $post_id );

		if ( $nonce_string ) {
			$nonce = wp_create_nonce( $nonce_string );
			return $nonce;
		}

		return false;
	}

	/**
	 * @param $reg_type
	 * @param $post_id
	 *
	 * @return bool|string
	 */
	private function format_nonce_string( $reg_type, $post_id ) {
		if ( is_numeric( $reg_type ) && is_numeric( $post_id ) ) {
			$nonce_string = 'bitcoin-bank-' . $reg_type . '-' . $post_id;
			return $nonce_string;
		} else {
			if ( ! is_numeric( $reg_type ) ) {
				Debug_Logger::write_debug_error( 'Missing reg_type.' );
			}

			if ( isset( $post_id ) ) {
				Debug_Logger::write_debug_error( 'Missing post_id. Type=' . gettype( $post_id ) . ' Value=' . $post_id );
			} else {
				Debug_Logger::write_debug_error( 'Missing post_id.' );
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function has_user_data() {
		return $this->has_data;
	}

	/**
	 * @param $username
	 * @param $password
	 *
	 * @return bool
	 */
	protected function check_login_data( $username, $password ) {
		$result = false;

		if ( isset( $username ) && ( '' !== $username ) ) {
			if ( validate_username( $username ) ) {
				if ( isset( $password ) && ( '' !== $password ) ) {
					$result = true;
				} else {
					$this->response_set_error( esc_html__( 'You must enter your password to log in.', 'bitcoin-bank' ) );
					$this->response_set_input_error( 'password' );

				}
			} else {
				$this->response_set_error( esc_html__( 'Error. Entered username has invalid characters or is wrong.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'username' );
			}
		} else {
			if ( ! $password ) {
				$this->response_set_error( esc_html__( 'You must enter your username and password to log in.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'username' );
				$this->response_set_input_error( 'password' );
			} else {
				$this->response_set_error( esc_html__( 'You must enter your username to log in.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'username' );
			}
		}

		return $result;
	}

	/**
	 * @return int
	 */
	protected function check_reset_email_secret() {
		Debug_Logger::write_debug_note();

		switch ( $this->model->get_data( Registration_Db_Table::STATE ) ) {
			case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
				$this->model->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM );
				$this->model->save_data();
				$result = self::RESULT_OK;
				break;

			case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
				$result = self::RESULT_OK;
				break;

			case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
				$result = self::RESULT_CONFIRM_IS_DONE;
				break;

			case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
				$result = self::RESULT_CONFIRM_EXPIRED_LINK;
				break;

			default:
				$result = self::RESULT_ERROR_UNDEFINED;
				break;
		}

		return $result;
	}

	/**
	 * @param $password
	 * @param $confirm_password
	 *
	 * @return bool
	 */
	protected function check_password_change_data( $password, $confirm_password ) {
		if ( ( $password ) && ( $password === $confirm_password ) ) {
			return true;
		} else {
			if ( ( ! $password ) && ( ! $confirm_password ) ) {
				$this->response_set_error( esc_html__( 'You must enter a new password.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'password1' );
				$this->response_set_input_error( 'password2' );
			} elseif ( ! $password ) {
				$this->response_set_error( esc_html__( 'You must enter same password in both inputs.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'password1' );
			} elseif ( ! $confirm_password ) {
				$this->response_set_error( esc_html__( 'You must enter same password in both inputs.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'password2' );
			} else {
				$this->response_set_error( esc_html__( 'Entered passwords does not match. You must enter same password in both inputs.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'password1' );
				$this->response_set_input_error( 'password2' );
			}
		}

		return false;
	}

	/**
	 * @param $email
	 *
	 * @return bool|false|int
	 */
	protected function check_send_password_link_data( $email ) {
		if ( ! $email ) {
			$this->response_set_error( esc_html__( 'You must enter your e-mail address.', 'bitcoin-bank' ) );
			$this->response_set_input_error( 'email' );
		} else {
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$wp_user_id = email_exists( $email );
				if ( $wp_user_id ) {
					return $wp_user_id;
				} else {
					$this->response_set_error( esc_html__( 'Error. This e-mail has no user account. Please enter the e-mail address used during registration.', 'bitcoin-bank' ) );
					$this->response_set_input_error( 'email' );
				}
			} else {
				$this->response_set_error( esc_html__( 'Error. Invalid e-mail address format. Please enter a correct e-mail address.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'email' );
			}
		}

		return false;
	}

	/**
	 * @param $email
	 *
	 * @return bool
	 */
	protected function check_Registration_email_data( $email ) {
		if ( $email ) {
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				return true;
			} else {
				$this->response_set_error( esc_html__( 'E-mail address has wrong format. Please write a correct e-mail address.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'email' );
			}
		} else {
			$this->response_set_error( esc_html__( 'You must enter your e-mail address to register.', 'bitcoin-bank' ) );
			$this->response_set_input_error( 'email' );
		}

		return false;
	}

	/**
	 * @param $terms_accepted
	 *
	 * @return bool
	 */
	protected function check_registration_terms_accepted( $terms_accepted ) {
		if ( isset( $terms_accepted ) && ( 1 === $terms_accepted ) ) {
			return true;
		} else {
			$this->response_set_error( esc_html__( 'You must accept the terms and conditions to register.', 'bitcoin-bank' ) );
			$this->response_set_input_error( 'accept_terms' );
		}

		return false;
	}

	/**
	 * @param $username
	 * @param $password
	 *
	 * @return bool
	 */
	protected function check_register_user_data( $username, $password ) {
		if ( $username && $password ) {
			if ( validate_username( $username ) ) {
				$register_option = get_option( Settings_Security_Options::OPTION_NAME );

				if ( ( $register_option[ Settings_Security_Options::USERNAME_NOT_EMAIL ] ) && ( filter_var( $username, FILTER_VALIDATE_EMAIL ) ) ) {
					$this->response_set_error( esc_html__( 'You can not use e-mail as username. Please select another username.', 'bitcoin-bank' ) );
					$this->response_set_input_error( 'username' );
				} else {
					if ( isset( $password ) ) {
						if ( ! username_exists( $username ) ) {
							return true;
						} else {
							$this->response_set_error( esc_html__( 'Error. This username has already been taken. Please select another username.', 'bitcoin-bank' ) );
							$this->response_set_input_error( 'username' );
						}
					} else {
						$this->response_set_error( esc_html__( 'You must select a password to register.', 'bitcoin-bank' ) );
						$this->response_set_input_error( 'password' );
					}
				}
			} else {
				$this->response_set_error( esc_html__( 'Error. Entered username has invalid characters.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'username' );
			}
		} else {
			if ( ! $username && ! $password ) {
				$this->response_set_error( esc_html__( 'You must select username and password to register.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'username' );
				$this->response_set_input_error( 'password' );
			} elseif ( ! $username ) {
				$this->response_set_error( esc_html__( 'You must select a username to register.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'username' );
			} elseif ( ! $password ) {
				$this->response_set_error( esc_html__( 'You must select a password to register.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'password' );
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function check_and_save_name() {
		$first_name = '';
		$last_name  = '';

		$design_option = get_option( Settings_Form_Options::OPTION_NAME );
		if ( $design_option[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
			$first_name = $this->view->first_name->GetText();
		}

		if ( $design_option[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ]
			&& $design_option[ Settings_Form_Options::REGISTER_FIRST_NAME_REQUIRED ]
			&& ( ! $first_name ) ) {
			/* First name is required but not set. */
			$this->response_set_error( esc_html__( 'First name is required field.', 'bitcoin-bank' ) );
			$this->response_set_input_error( 'first_name' );

		} else {
			$design_option = get_option( Settings_Form_Options::OPTION_NAME );
			if ( $design_option[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
				$last_name = $this->view->last_name->GetText();
			}

			if ( $design_option[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ]
				&& $design_option[ Settings_Form_Options::REGISTER_LAST_NAME_REQUIRED ]
				&& ( ! $last_name ) ) {
				/* Last name is required but not set. */
				$this->response_set_error( esc_html__( 'Last name is required field.', 'bitcoin-bank' ) );
				$this->response_set_input_error( 'last_name' );
			} else {
				$this->model->set_data( Registration_Db_Table::FIRST_NAME, $first_name );
				$this->model->set_data( Registration_Db_Table::LAST_NAME, $last_name );
				$result = $this->model->save_data();

				if ( false === $result ) {
					$this->response_set_error( esc_html__( 'Undefined error. Illegal characters in name.', 'bitcoin-bank' ) );
					$this->response_set_input_error( 'first_name' );
					$this->response_set_input_error( 'last_name' );
				}

				return $result;
			}
		}
		return false;
	}

	/**
	 * @param $username
	 * @param $password
	 *
	 * @return bool
	 */
	protected function register_username_password( $username, $password ) {
		Debug_Logger::write_debug_note();

		if ( ( '' !== $username ) && ( '' !== $password ) ) {
			$password_hash = wp_hash_password( $password );
			$this->model->set_data( Registration_Db_Table::USERNAME, $username );
			$this->model->set_data( Registration_Db_Table::PASSWORD, $password_hash );
			if ( $this->model->save_data() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $email
	 *
	 * @return bool
	 */
	public function register_email( $email ) {
		Debug_Logger::write_debug_note( Debug_Logger::obfuscate( $email ) );

		$result = false;

		if ( '' !== $email ) {
			$this->model->set_data( Registration_Db_Table::EMAIL, $email );

			$secret = $this->model->get_data( Registration_Db_Table::SECRET );
			if ( '' === $secret ) {
				$secret = Misc::random_string( self::SECRET_LENGTH );
				$this->model->set_data( Registration_Db_Table::SECRET, $secret );
			}

			$retry = $this->model->get_data( Registration_Db_Table::RETRY_COUNTER );
			$retry++;
			$this->model->set_data( Registration_Db_Table::RETRY_COUNTER, $retry );

			if ( $this->model->save_data() ) {
				$reg_id        = $this->model->get_data( Registration_Db_Table::PRIMARY_KEY );
				$retry_counter = $this->model->get_data( Registration_Db_Table::RETRY_COUNTER );
				$post_id       = $this->model->get_data( Registration_Db_Table::POST_ID );

				if ( $retry_counter < 5 ) {
					$data      = array();
					$data['r'] = $reg_id;
					$data['s'] = $secret;
					$url_data  = $this->encode_email_url_data( $data );

					$this->verification_link = get_permalink( $post_id );
					$this->verification_link = add_query_arg( 'verification', $url_data, $this->verification_link );

					$reg_type = $this->model->get_data( Registration_Db_Table::REG_TYPE );

    				$result = $this->send_email_register_verification( $email, $this->verification_link, $post_id );

					if ( false === $result ) {
						$s               = new Strong( $email );
						$formatted_email = $s->draw_html();
						/* translators: %s: E-mail address. */
						$this->error_message = sprintf( esc_html__( 'Error sending e-mail to %s. Retry later or contact site admin if problem persists.', 'bitcoin-bank' ), $formatted_email );
					}

					$this->has_data = true;
				} else {
					$this->error_message = esc_html__( 'Error. Maximum number of e-mail sent.', 'bitcoin-bank' );
				}
			}
		}

		return $result;
	}

	/**
	 * @param $empty_value
	 *
	 * @return array
	 */
	protected function get_email_key_list( $empty_value ) {
		$variables               = array();
		$variables['name']       = $empty_value;
		$variables['username']   = $empty_value;
		$variables['first_name'] = $empty_value;
		$variables['last_name']  = $empty_value;
		$variables['email']      = $empty_value;
		$variables['title']      = $empty_value;
		$variables['site_name']  = $empty_value;
		$variables['site_url']   = $empty_value;
		$variables['link']       = $empty_value;
		return $variables;
	}

	/**
	 * @param $link
	 * @param $post_id
	 * @param $escaping
	 *
	 * @return array
	 */
	protected function prepare_variables( $link, $post_id, $escaping ) {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$username   = $current_user->user_login;
			$first_name = $current_user->first_name;
			$last_name  = $current_user->last_name;
			$email      = $current_user->user_email;
		} else {
			$username   = $this->get_username();
			$first_name = $this->get_first_name();
			$last_name  = $this->get_last_name();
			$email      = $this->get_email();
		}

		$variables = $this->get_email_key_list( 'N/A' );

		$variables['name']       = $this->get_username_or_names( $username, $first_name, $last_name );
		$variables['username']   = $username;
		$variables['first_name'] = $first_name;
		$variables['last_name']  = $last_name;
		$variables['email']      = $email;
		if ( $post_id ) {
			if ( $escaping ) {
				$variables['title'] = get_the_title( $post_id );
			} else {
				$post               = get_post( $post_id );
				$variables['title'] = isset( $post->post_title ) ? $post->post_title : '';
			}
		}
		if ( $escaping ) {
			$variables['site_name'] = get_bloginfo( 'name' );
		} else {
			$variables['site_name'] = html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		}
		$variables['site_url'] = site_url();
		if ( $link ) {
			$variables['link'] = $href = '<a href="' . $link . '">' . $link . '</a>';
		}

		return $variables;
	}

	/**
	 * @param $text
	 * @param $variables
	 *
	 * @return mixed
	 */
	protected function replace_email_variables( $text, $variables ) {
		foreach ( $variables as $variable => $value ) {
			$text = str_replace( '{' . $variable . '}', $value, $text );
			$text = str_replace( '%' . $variable . '%', $value, $text );
		}
		return $text;
	}

	/**
	 * @param $email
	 * @param $link
	 * @param $post_id
	 *
	 * @return bool
	 */
	private function send_email_register_verification( $email, $link, $post_id ) {
		Debug_Logger::write_debug_note( Debug_Logger::obfuscate( $email ), $post_id );

		$options = get_option( Settings_Email_Options::OPTION_NAME );

		$subject = $options[ Settings_Email_Options::REGISTER_SUBJECT ];
		$body    = $options[ Settings_Email_Options::REGISTER_BODY ];

		$variables = $this->prepare_variables( $link, $post_id, false );
		$subject   = $this->replace_email_variables( $subject, $variables );
		$body      = $this->replace_email_variables( $body, $variables );

		return $this->send_email( $email, $options[ Settings_Email_Options::REGISTER_REPLAY_ADDRESS ], $subject, $body );
	}

	/**
	 * @return int
	 */
	public function confirm_email() {
		Debug_Logger::write_debug_note();

		switch ( $this->model->get_data( Registration_Db_Table::STATE ) ) {
			case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
				$this->model->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED );
				$this->model->save_data();
				$result = self::RESULT_OK;
				break;

			case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
				$result = self::RESULT_CONFIRM_IS_DONE;
				break;

			case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
				$result = self::RESULT_USER_EXIST;
				break;

			case Registration_Db_Table::STATE_REGISTRATION_EXPIRED:
				$result = self::RESULT_CONFIRM_EXPIRED_LINK;
				break;

			default:
				$result = self::RESULT_ERROR_UNDEFINED;
				break;
		}

		return $result;
	}

	/**
	 * @param $array_data
	 *
	 * @return string
	 */
	protected function encode_email_url_data( $array_data ) {
		/* Add random characters in start to obfuscate the string, or it will always start with same characters. */
		$seed          = chr( wp_rand( 32, 122 ) ) . chr( wp_rand( 32, 122 ) );
		$json          = $seed . wp_json_encode( $array_data );
		$json_bas64    = base64_encode( $json );
		$json_bas64url = rtrim( strtr( $json_bas64, '+/', '-_' ), '=' );
		return $json_bas64url;
	}

	/**
	 * @param $url_data
	 *
	 * @return array|mixed|object|null
	 */
	protected function decode_email_url_data( $url_data ) {
		$json = base64_decode( str_pad( strtr( $url_data, '-_', '+/' ), strlen( $url_data ) % 4, '=', STR_PAD_RIGHT ) );
		/* Remove any random characters in front of the json string itself, these are for obfuscating. */
		$json_start_pos = strpos( $json, '{' );
		$json           = substr( $json, $json_start_pos );
		$array_data     = json_decode( $json, true );
		return $array_data;
	}

	/**
	 * @param $email
	 * @param $wp_user_id
	 *
	 * @return bool
	 */
	protected function send_email_reset_link( $email, $wp_user_id ) {
		Debug_Logger::write_debug_note( Debug_Logger::obfuscate( $email ) );

		$options = get_option( Settings_Email_Options::OPTION_NAME );

		$user_info = get_userdata( $wp_user_id );
		$username  = $user_info->user_login;

		$secret = Misc::random_string( self::SECRET_LENGTH );
		$this->model->set_data( Registration_Db_Table::SECRET, $secret );
		$this->model->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT );
		$this->model->set_data( Registration_Db_Table::USERNAME, $username );
		$this->model->set_data( Registration_Db_Table::EMAIL, $email );
		$this->model->set_data( Registration_Db_Table::WP_USER_ID, $wp_user_id );
		$this->model->save_data();
		$reg_id = $this->model->get_data( Registration_Db_Table::PRIMARY_KEY );

		$post_id = $this->get_page_id();

		$data      = array();
		$data['r'] = $reg_id;
		$data['s'] = $secret;
		$url_data  = $this->encode_email_url_data( $data );

		$link = get_permalink( $post_id );
		$link = add_query_arg( 'verification', $url_data, $link );

		$subject = $options[ Settings_Email_Options::PASSWORD_SUBJECT ];
		$body    = $options[ Settings_Email_Options::PASSWORD_BODY ];

		$variables = $this->prepare_variables( $link, $post_id, false );
		$subject   = $this->replace_email_variables( $subject, $variables );
		$body      = $this->replace_email_variables( $body, $variables );

		return $this->send_email( $email, $options[ Settings_Email_Options::PASSWORD_REPLAY_ADDRESS ], $subject, $body );
	}

	/**
	 * @param null $wp_user_id
	 *
	 * @return bool
	 */
	protected function send_email_notification_new_user( $wp_user_id = null ) {
		Debug_Logger::write_debug_note();

		$options = get_option( Settings_Email_Options::OPTION_NAME );

		if ( $options[ Settings_Email_Options::NOTIFICATION_ENABLE ] ) {
			$subject = $options[ Settings_Email_Options::NOTIFICATION_SUBJECT ];
			$body    = $options[ Settings_Email_Options::NOTIFICATION_BODY ];

			$post_id   = $this->get_page_id();
			$variables = $this->prepare_variables( null, $post_id, false );
			if ( null !== $wp_user_id ) {
				$user_info             = get_userdata( $wp_user_id );
				$username              = $user_info->user_login;
				$variables['username'] = $username;
			}

			$subject = $this->replace_email_variables( $subject, $variables );
			$body    = $this->replace_email_variables( $body, $variables );

			$send_to = $options[ Settings_Email_Options::NOTIFICATION_SEND_TO ];
			if ( '' === $send_to ) {
				$send_to = get_option( 'admin_email' );
			}

			return $this->send_email(
				$send_to,
				$options[ Settings_Email_Options::NOTIFICATION_REPLAY_ADDRESS ],
				$subject,
				$body
			);
		}
	}

	/**
	 * @param $to
	 * @param $from
	 * @param $subject
	 * @param $body
	 *
	 * @return bool
	 */
	private function send_email( $to, $from, $subject, $body ) {
		$email = array(
			'to'      => $to,
			'from'    => $from,
			'subject' => $subject,
			'body'    => $body,
		);

		$email = apply_filters( 'bitcoin_bank_email', $email );

		if ( defined('BITCOIN_BANK_DEBUG_DONT_SEND_EMAIL')) {
			$email = true;
		} else {
			if ( gettype( $email ) === 'array' ) {
				$mailer = new Mailer( $email['to'] );
				$mailer->set_from_address( $email['from'] );
				$mailer->aet_subject( $email['subject'] );
				$mailer->set_body( $email['body'] );

				return $mailer->send();
			}
		}

		return ( true === $email );
	}

	/**
	 * @param $password
	 * @param $wp_user_id
	 *
	 * @return bool
	 */
	protected function update_password( $password, $wp_user_id ) {
		Debug_Logger::write_debug_note();

		if ( is_user_logged_in() ) {
			/*
			 * Need to catch the cookie used for authentication. The cookie will be set by wp_set_auth_cookie.
			 * This will not be installed until next browser request, and we may need it for nonce generation.
			 */
			add_action( 'set_logged_in_cookie', 'BCQ_BitcoinBank\Membership_Controller::install_logged_in_cookie' );
		}

		$user_data['ID']        = $wp_user_id;
		$user_data['user_pass'] = $password;
		$user_id                = wp_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return false;
		} else {
			if ( is_user_logged_in() ) {
				/* After password change the nonce for this session is not valid any more. */
				$old_nonce = $this->get_nonce();
				$this->update_nonce_changed( $old_nonce );
			}
			return true;
		}
	}

	/**
	 * @return bool
	 */
	public function has_all_required_info() {
		$username = $this->model->get_data( Registration_Db_Table::USERNAME );
		$password = $this->model->get_data( Registration_Db_Table::PASSWORD );
		$email    = $this->model->get_data( Registration_Db_Table::EMAIL );

		return ( $username && $password && $email );
	}

	/**
	 * @param $state
	 */
	public function set_state( $state ) {
		$this->model->set_data( Registration_Db_Table::STATE, $state );
		$this->model->save_data();
	}

	/**
	 * @return |null
	 */
	public function get_state() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::STATE );
		} else {
			return null;
		}
	}

	/**
	 * @return bool
	 */
	public function user_exist() {
		$username = $this->model->get_data( Registration_Db_Table::USERNAME );

		$user_id = username_exists( $username );
		if ( false === $user_id ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @return bool
	 */
	public function email_exist() {
		$email = $this->model->get_data( Registration_Db_Table::EMAIL );

		$user_id = email_exists( $email );
		if ( false === $user_id ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @return bool|int|\WP_Error
	 */
	public function create_new_user() {
		Debug_Logger::write_debug_note();

		$result = false;

		$username = $this->model->get_data( Registration_Db_Table::USERNAME );

		if ( validate_username( $username ) ) {
			$email = $this->model->get_data( Registration_Db_Table::EMAIL );

			$temp_password = wp_hash_password( Misc::random_string( 8 ) );

			$wp_user_id = wp_create_user( $username, $temp_password, $email );

			if ( ! is_wp_error( $wp_user_id ) ) {
				$password_hash = $this->model->get_data( Registration_Db_Table::PASSWORD );
				$this->set_hashed_wp_password( $password_hash, $wp_user_id );

				$this->model->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_REGISTRATION_USER_CREATED );
				$this->model->set_data( Registration_Db_Table::WP_USER_ID, $wp_user_id );
				$this->model->save_data();

				$user_data['ID']         = $wp_user_id;
				$user_data['first_name'] = addslashes( $this->model->get_data( Registration_Db_Table::FIRST_NAME ) );
				$user_data['last_name']  = addslashes( $this->model->get_data( Registration_Db_Table::LAST_NAME ) );

				$result = wp_update_user( $user_data );
				if ( is_wp_error( $result ) ) {
					Debug_Logger::write_debug_error( 'Error updating user.' );
				}

				$result = true;
			}
		}

		return $result;
	}

	/**
	 * @param $password_hashed
	 * @param $user_id
	 */
	private function set_hashed_wp_password( $password_hashed, $user_id ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->users,
			array(
				'user_pass'           => $password_hashed,
				'user_activation_key' => '',
			),
			array( 'ID' => $user_id )
		);
		wp_cache_delete( $user_id, 'users' );
	}

	/**
	 * @return bool
	 */
	public function log_in_registered_user() {
		Debug_Logger::write_debug_note();

		$username = $this->model->get_data( Registration_Db_Table::USERNAME );

		$user = get_user_by( 'login', $username );
		if ( ! ( false === $user ) ) {
			/*
			 * Need to catch the cookie used for authentication. The cookie will be set by wp_set_auth_cookie.
			 * This will not be installed until next browser request, and we may need it for nonce generation.
			 */
			add_action( 'set_logged_in_cookie', 'BCQ_BitcoinBank\Membership_Controller::install_logged_in_cookie' );

			$user_id = $user->ID;
			wp_set_current_user( $user_id, $username );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', $username, $user );

			/*
			 After login the nonce is not valid any more.
				Create a new nonce and store it for nonce verification during this http request. */
			$old_nonce = $this->get_nonce();
			$this->update_nonce_changed( $old_nonce );

			Debug_Logger::write_debug_note( 'User ' . Debug_Logger::obfuscate( $username ) . ' logged in.' );

			return true;
		} else {
			Debug_Logger::write_debug_note( 'User ' . Debug_Logger::obfuscate( $username ) . ' failed to log in.' );

			return false;
		}
	}

	/**
	 * @param $logged_in_cookie
	 */
	public static function install_logged_in_cookie( $logged_in_cookie ) {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
	}

	/**
	 * @param $username
	 * @param $password
	 * @param $remember
	 *
	 * @return bool
	 */
	public function log_in_user( $username, $password, $remember ) {
		if ( isset( $remember ) && ( 1 === $remember ) ) {
			$remember = true;
		} else {
			$remember = false;
		}

		$credentials = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $credentials, false );

		if ( is_wp_error( $user ) ) {
			return false;
		} else {
			/*
			 * Need to catch the cookie used for authentication. The cookie will be set by wp_set_auth_cookie.
			 * This will not be installed until next browser request, and we may need it for nonce generation.
			 */
			add_action( 'set_logged_in_cookie', 'BCQ_BitcoinBank\Membership_Controller::install_logged_in_cookie' );

			$user_id = $user->ID;
			wp_set_current_user( $user_id, $username );
			wp_set_auth_cookie( $user_id, $remember );
			do_action( 'wp_login', $username, $user );

			/*
			 After login the nonce is not valid any more.
				Create a new nonce and store it for nonce verification during this http request. */
			$old_nonce = $this->get_nonce();
			$this->update_nonce_changed( $old_nonce );

			return true;
		}
	}

	/**
	 * @return bool
	 */
	public function log_out_user() {
		wp_logout();
		wp_set_current_user( 0 );

		$_COOKIE[ LOGGED_IN_COOKIE ] = '';

		/* After logout the nonce for this session is not valid any more. */
		$old_nonce = $this->get_nonce();
		$this->update_nonce_changed( $old_nonce );

		return true;
	}

	/**
	 * @return |null
	 */
	public function get_reg_id() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::PRIMARY_KEY );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_reg_type() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::REG_TYPE );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_page_id() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::POST_ID );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_nonce() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::NONCE );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_secret() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::SECRET );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_username() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::USERNAME );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_first_name() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::FIRST_NAME );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_last_name() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::LAST_NAME );
		} else {
			return null;
		}
	}

	/**
	 * @param $username
	 * @param $first_name
	 * @param $last_name
	 *
	 * @return string
	 */
	public function get_username_or_names( $username, $first_name, $last_name ) {
		if ( ( '' === $first_name ) && ( '' === $last_name ) ) {
			$name = $username;
		} else {
			if ( '' === $first_name ) {
				$name = $last_name;
			} elseif ( '' === $last_name ) {
				$name = $first_name;
			} else {
				$name = $first_name . ' ' . $last_name;
			}
		}
		return $name;
	}

	/**
	 * @return |null
	 */
	public function get_password() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::PASSWORD );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_email() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::EMAIL );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_wp_user_id() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::WP_USER_ID );
		} else {
			return null;
		}
	}

	/**
	 * @return |null
	 */
	public function get_cookie() {
		if ( $this->has_data ) {
			return $this->model->get_data( Registration_Db_Table::COOKIE );
		} else {
			return null;
		}
	}

	/**
	 * @return string
	 */
	public function get_error_message() {
		if ( $this->has_data ) {
			if ( isset( $this->error_message ) && ( '' !== $this->error_message ) ) {
				return $this->error_message;
			}
		}
		return esc_html__( 'Undefined error.', 'bitcoin-bank' );
	}

	/**
	 * @param $msg
	 */
	protected function response_set_error( $msg ) {
		$this->set_server_context_data( 'response_status', 'error' );
		$this->set_server_context_data( 'response_color', 'red' );
		$this->set_server_context_data( 'response_message', $msg );
	}

	/**
	 * @param $input_name
	 */
	protected function response_set_input_error( $input_name ) {
		$error_input                = $this->get_server_context_data( 'error_input', array() );
		$error_input[ $input_name ] = true;
		$this->set_server_context_data( 'error_input', $error_input );
	}

	/**
	 *
	 */
	protected function hide_input_error_indications() {
		$this->view->hide_input_error_indications();
	}

	/**
	 *
	 */
	protected function show_onput_error_indications() {
		$error_inputs = $this->get_server_context_data( 'error_input' );
		if ( $error_inputs ) {
			$this->view->show_input_error_indications( $error_inputs );
		}
	}

	/**
	 *
	 */
	protected function response_set_logged_in() {
		$this->set_server_context_data( 'have_logged_in', 1 );
	}

	/**
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	protected function load_model_values( $values = array() ) {
		/*
		 * Model values only loaded by this class itself. Override this function to prevent base class from loading
		 * model values.
		 */
		return $values;
	}

	/**
	 */
	protected function init_view( $values ) {
		parent::init_view( $values );

		$this->add_hidden_fields();
	}

	/**
	 * @return int
	 */
	protected function read_terms_from_view() {
		$terms_accepted = 1;
		$design_options = get_option( Settings_Form_Options::OPTION_NAME );
		if ( $design_options[ Settings_Form_Options::REGISTER_MUST_ACCEPT_TERMS ] ) {
			$terms_accepted = $this->view->accept_terms->get_value();
		}
		return $terms_accepted;
	}
}
