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

use WP_PluginFramework\Models\Option_Model;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Settings_Linking_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_linking_option';

	const REGISTER_PAGE_LINK   = 'RegisterPageLink';
	const LOGIN_PAGE_LINK      = 'LoginPageLink';
	const PROFILE_PAGE_LINK    = 'ProfilePageLink';
	const PASSWORD_PAGE_LINK   = 'PasswordPageLink';
	const TERMS_PAGE           = 'TermsPage';
    const LOGOUT_PAGE_REDIRECT = 'LogoutPageRedirect';
    const LOGIN_PAGE_REDIRECT  = 'LoginPageRedirect';
	const ACCOUNT_LIST_PAGE    = 'AccountListPage';
    const TRANSACTION_PAGE     = 'TransactionPage';
    const CHEQUE_DETAILS_PAGE  = 'ChequeDetailsPage';
    const SEND_CHEQUE_PAGE     = 'SendChequePage';
    const RECEIVE_CHEQUE_PAGE  = 'ReceiveChequePage';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::REGISTER_PAGE_LINK   => array(
			'data_type'     => 'String_Type',
			'default_value' => '/register',
		),
		self::LOGIN_PAGE_LINK      => array(
			'data_type'     => 'String_Type',
			'default_value' => '/login',
		),
		self::PROFILE_PAGE_LINK    => array(
			'data_type'     => 'String_Type',
			'default_value' => '/profile',
		),
		self::PASSWORD_PAGE_LINK   => array(
			'data_type'     => 'String_Type',
			'default_value' => '/password',
		),
		self::TERMS_PAGE           => array(
			'data_type'     => 'String_Type',
			'default_value' => '/terms',
		),
        self::LOGOUT_PAGE_REDIRECT => array(
            'data_type'     => 'String_Type',
            'default_value' => '/',
        ),
        self::LOGIN_PAGE_REDIRECT => array(
            'data_type'     => 'String_Type',
            'default_value' => null,
        ),
		self::ACCOUNT_LIST_PAGE => array(
			'data_type'     => 'String_Type',
			'default_value' => '/account-overview',
		),
        self::TRANSACTION_PAGE => array(
            'data_type'     => 'String_Type',
            'default_value' => '/transactions',
        ),
        self::CHEQUE_DETAILS_PAGE => array(
            'data_type'     => 'String_Type',
            'default_value' => '/cheque-details',
        ),
        self::SEND_CHEQUE_PAGE => array(
            'data_type'     => 'String_Type',
            'default_value' => '/send-cheque',
        ),
        self::RECEIVE_CHEQUE_PAGE => array(
            'data_type'     => 'String_Type',
            'default_value' => '/receive-cheque',
        ),
	);

	/**
	 * Settings_Linking_Options constructor.
	 *
	 * @param null $model_name
	 */
	public function __construct( $model_name = null ) {
		parent::__construct( $model_name );
		$this->load_data();
	}

	/**
	 * @param $link_id
	 * @param bool    $check_link
	 *
	 * @return bool|false|string
	 */
	public function get_complete_link_url( $link_id, $check_link = false ) {
		$url             = false;
		$link_is_my_site = false;

		$raw_link = $this->get_data( $link_id );

		if ( isset( $raw_link ) ) {
			$raw_link = trim( $raw_link );
			if ( $raw_link ) {
				if ( '/' === $raw_link ) {
					return get_site_url();
				}

				$link = $raw_link;

				$wp_post = get_page_by_path( $link );
				if ( ! $wp_post ) {
					if ( '/' === $link[0] ) {
						$link_is_my_site = true;
					} else {
						$site_url = get_site_url();
						if ( substr( $link, 0, strlen( $site_url ) ) === $site_url ) {
							$link_is_my_site = true;
							$link            = substr( $link, strlen( $site_url ) );
							$wp_post         = get_page_by_path( $link );
						}
					}
				}

				if ( ! $wp_post ) {
					if ( $link_is_my_site ) {
						$url_parse = wp_parse_url( $link );
						if ( array_key_exists( 'query', $url_parse ) ) {
							parse_str( $url_parse['query'], $query_args );
							foreach ( $query_args as $key => $value ) {
								$key = strtolower( $key );
								if ( ( 'p' === $key ) || ( 'page_id' === $key ) ) {
									$post_id = intval( $value );
									$wp_post = get_post( $post_id );
									if ( $wp_post ) {
										break;
									}
								}
							}
						}
					}
				}

				if ( $wp_post ) {
					return get_permalink( $wp_post );
				} else {
					if ( ! $link_is_my_site ) {
						$validated_url = wp_http_validate_url( $raw_link );
						if ( $validated_url ) {
							$url = $validated_url;
						} else {
							if ( $check_link ) {
								Debug_Logger::write_debug_warning( 'Invalid login url ' . $raw_link );
							} else {
								$url = $raw_link;
							}
						}
					} else {
						if ( $check_link ) {
							Debug_Logger::write_debug_warning( 'Invalid login page ' . $raw_link );
						} else {
							$url = $raw_link;
						}
					}
				}
			}
		}

		if ( false !== $url ) {
			if ( '' !== $url ) {
				if ( ( 'http://' === substr( $url, 0, 7 ) ) || ( 'https://' === substr( $url, 0, 8 ) ) ) {
					return $url;
				} else {
					if ( '/' !== ( substr( $url, 0, 1 ) ) && ( '\\' !== substr( $url, 0, 1 ) ) ) {
						$url = '/' . $url;
					}

					return site_url() . $url;
				}
			} else {
				return '';
			}
		}

		return $url;
	}

	/**
	 * @param $link_id
	 *
	 * @return |null
	 */
	public function get_link( $link_id ) {
		return $this->get_data( $link_id );
	}

	/**
	 * @return bool|false|string
	 */
	public function get_register_url() {
		$linking_options = new Settings_Linking_Options();
		$url             = $linking_options->get_complete_link_url( self::REGISTER_PAGE_LINK, true );

		if ( ! $url ) {
			$url = wp_registration_url();
		}

		return $url;
	}

	/**
	 * @return bool|false|string
	 */
	public function get_login_url() {
		$linking_options = new Settings_Linking_Options();
		$url             = $linking_options->get_complete_link_url( self::LOGIN_PAGE_LINK, true );

		if ( ! $url ) {
			$url = wp_login_url();
		}

		return $url;
	}

	/**
	 * @return bool|false|string
	 */
	public function get_lost_password_url() {
		$linking_options = new Settings_Linking_Options();
		$url             = $linking_options->get_complete_link_url( self::PASSWORD_PAGE_LINK, true );

		if ( ! $url ) {
			$url = wp_lostpassword_url();
		}

		return $url;
	}
}
