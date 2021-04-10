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

use WP_PluginFramework\Models\Model;

class Profile_Model extends Model {

	const USER_LOGIN = 'user_login';
	const FIRST_NAME = 'first_name';
	const LAST_NAME  = 'last_name';
	const USER_EMAIL = 'user_email';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::USER_LOGIN => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
			'readonly'      => true,
		),
		self::FIRST_NAME => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::LAST_NAME  => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::USER_EMAIL => array(
			'data_type'     => 'Email_Type',
			'default_value' => '',
			'required'      => true,
		),
	);

	/**
	 * @param $field_name_list
	 *
	 * @return bool|int
	 */
	public function load_data_record( $field_name_list, $query_parameters=null  ) {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();

			$user_data = array(
				self::USER_LOGIN => $current_user->user_login,
				self::FIRST_NAME => $current_user->first_name,
				self::LAST_NAME  => $current_user->last_name,
				self::USER_EMAIL => $current_user->user_email,
			);

			$this->add_data_record( $user_data );

			return 1;
		}

		return false;
	}

	/**
	 * @param $field_name_list
	 */
	public function load_column( $field_name_list ) {    }

	/**
	 * @param $index
	 *
	 * @return bool|null
	 */
	public function save_data_index( $index ) {
		if ( is_user_logged_in() ) {
			$data_record = $this->get_data_record();

			$user_data = wp_get_current_user();

			$user_data->first_name = addslashes( $data_record[ self::FIRST_NAME ] );
			$user_data->last_name  = addslashes( $data_record[ self::LAST_NAME ] );
			$user_data->user_email = addslashes( $data_record[ self::USER_EMAIL ] );

			$user_id = wp_update_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				return false;
			} else {
				return true;
			}
		}

		return null;
	}

	/**
	 * @param $key
	 *
	 * @return |null
	 */
	public function is_required_field( $key ) {
		$register_options = get_option( Settings_Form_Options::OPTION_NAME );

		switch ( $key ) {
			case static::FIRST_NAME:
				return $register_options[ Settings_Form_Options::REGISTER_FIRST_NAME_REQUIRED ];
				break;

			case static::LAST_NAME:
				return $register_options[ Settings_Form_Options::REGISTER_LAST_NAME_REQUIRED ];
				break;

			default:
				return parent::is_required_field( $key );
				break;
		}
	}
}
