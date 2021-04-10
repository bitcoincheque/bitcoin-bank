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

use WP_PluginFramework\Utils\Debug_Logger;

class Membership {

	/**
	 * @return string
	 */
	public static function registration_page() {
		if ( is_user_logged_in() ) {
			$controller = new Login_Controller();
		} else {
			$controller = new Registration_Controller();
		}

		return $controller->draw();
	}

	/**
	 * @return string
	 */
	public static function membership_login() {
		$controller = new Login_Redirect_Controller();

		return $controller->draw();

	}

	/**
	 * @return string
	 */
	public static function profile_page() {
		$html = '';
		if ( is_user_logged_in() ) {
			$controller = new Profile_Controller();
		} else {
			$html      .= '<p>' . esc_html__( 'You must log in to access your user profile.', 'bitcoin-bank' ) . '</p>';
			$controller = new Login_Controller();
		}

		$html .= $controller->draw();
		return $html;
	}

	/**
	 * @return string
	 */
	public static function password_page() {
		if ( is_user_logged_in() ) {
			$controller = new Password_Change_Controller();
		} else {
			$controller = new Password_Recovery_Controller();
		}

		return $controller->draw();
	}

	/**
	 *
	 */
	public static function schedule_event() {
		Debug_Logger::write_debug_note( 'ScheduleEvent.' );

		$clear_timeout    = 30 * 24 * 60 * 60;  // Timeout for when the record will be removed from database
		$register_timeout = 2 * 24 * 60 * 60;  // Timeout for valid registration link
		$password_timeout = 60 * 60;  // Timeout for valid password link

		$reg_data = new Registration_Db_Table();
		$count    = $reg_data->load_all_data();

		$pending_approval = 0;

		while ( $count > 0 ) {
			$state         = $reg_data->get_data_index( 0, Registration_Db_Table::STATE );
			$timestamp_obj = $reg_data->get_data_object( Registration_Db_Table::TIMESTAMP );
			$timestamp     = $timestamp_obj->get_value_seconds();

			$now = time();

			$deleted = false;

			if ( ( $timestamp + $clear_timeout ) < $now ) {
				if ( Registration_Db_Table::STATE_APPROVAL_PENDING === $state ) {
					$reg_data->fetch_data();
				} else {
					$reg_data->delete();
				}
			} else {
				switch ( $state ) {
					case Registration_Db_Table::STATE_REGISTRATION_STARTED:
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
					case Registration_Db_Table::STATE_REGISTRATION_MORE_INFO:
						if ( ( $timestamp + $register_timeout ) < $now ) {
							$reg_data->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_REGISTRATION_EXPIRED );
							$reg_data->save_data();
							$id = $reg_data->get_data( Registration_Db_Table::PRIMARY_KEY );
							Debug_Logger::write_debug_note( 'Expired registration data record for id=', $id );
						}
						break;

					case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
					case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
						if ( ( $timestamp + $password_timeout ) < $now ) {
							$reg_data->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED );
							$reg_data->save_data();
							$id = $reg_data->get_data( Registration_Db_Table::PRIMARY_KEY );
							Debug_Logger::write_debug_note( 'Expired password registration data record for id=', $id );
						}
						break;

					case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
					case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
					case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
					case Registration_Db_Table::STATE_REGISTRATION_EXPIRED:
					case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
					case Registration_Db_Table::STATE_APPROVAL_DECLINED:
						break;

					case Registration_Db_Table::STATE_APPROVAL_PENDING:
						$pending_approval++;
						break;

					default:
						$reg_id = $reg_data->get_data( Registration_Db_Table::PRIMARY_KEY );
						Debug_Logger::write_debug_error( 'Missing state handler for ' . $state . '. Delete reg_id ' . $reg_id );
						$reg_data->delete();
						$deleted = true;
						break;

				}

				if ( ! $deleted ) {
					$reg_data->fetch_data();
				}
			}
			$count--;
		}

		Registration_Db_Table::set_pending_approval_counter( $pending_approval );
	}

	/**
	 * @return bool|false|string
	 */
	public static function get_redirect_logout() {
		$linking_options = new Settings_Linking_Options();
		$url             = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGOUT_PAGE_REDIRECT, true );
		return $url;
	}

    /**
     * @return bool|false|string
     */
    public static function get_redirect_login() {
        $linking_options = new Settings_Linking_Options();
        $url             = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGOUT_PAGE_REDIRECT, true );
        return $url;
    }

	/**
	 * @param $url
	 *
	 * @return bool|false|mixed|string
	 */
	public static function redirect_logout( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGOUT_PAGE_REDIRECT, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		} else {
			$url = $_SERVER['HTTP_REFERER'];
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	public static function redirect_register( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::REGISTER_PAGE_LINK, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	public static function redirect_login( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGIN_PAGE_LINK, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	public static function redirect_lost_password( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::PASSWORD_PAGE_LINK, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		}

		return $url;
	}
}
