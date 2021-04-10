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

use WP_PluginFramework\Models\Active_Record;

use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Registration_Db_Table extends Active_Record {

	/* Database table name: */
	const TABLE_NAME = 'bcq_bitcoin_bank_registration';

	/* List of table field names: */
	const TIMESTAMP     = 'timestamp';
	const REG_TYPE      = 'reg_type';
	const STATE         = 'state';
	const USERNAME      = 'username';
	const PASSWORD      = 'password';
	const EMAIL         = 'email';
	const WP_USER_ID    = 'wp_user';
	const RETRY_COUNTER = 'retries';
	const COOKIE        = 'cookie';
	const NONCE         = 'nonce';
	const POST_ID       = 'post_id';
	const SECRET        = 'secret';
	const FIRST_NAME    = 'first_name';
	const LAST_NAME     = 'last_name';

    /* Registration types: */
	const REG_TYPE_NOT_SET                = 0;
	const REG_TYPE_USER_REGISTRATION      = 2;
	const REG_TYPE_PASSWORD_RECOVERY      = 3;
	const REG_TYPE_LOGIN                  = 4;
	const REG_TYPE_LOGOUT                 = 5;
	const REG_TYPE_PROFILE                = 6;

	/* State values: */
	const STATE_REGISTRATION_STARTED         = 0;
	const STATE_REGISTRATION_EMAIL_SENT      = 1;
	const STATE_REGISTRATION_EMAIL_CONFIRMED = 2;
	const STATE_REGISTRATION_MORE_INFO       = 3;
	const STATE_REGISTRATION_USER_CREATED    = 4;
	const STATE_RESET_PASSWORD_EMAIL_SENT    = 5;
	const STATE_RESET_PASSWORD_EMAIL_CONFIRM = 6;
	const STATE_RESET_PASSWORD_DONE          = 7;
	const STATE_RESET_PASSWORD_EXPIRED       = 8;
	const STATE_REGISTRATION_EXPIRED         = 9;
	const STATE_REGISTRATION_NOT_SET         = 10;
	const STATE_APPROVAL_PENDING             = 11;
	const STATE_APPROVAL_DECLINED            = 12;

	private $my_state = null;

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::TIMESTAMP     => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::REG_TYPE      => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => self::REG_TYPE_NOT_SET,
		),
		self::STATE         => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => self::STATE_REGISTRATION_NOT_SET,
		),
		self::USERNAME      => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PASSWORD      => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::EMAIL         => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::WP_USER_ID    => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
		self::RETRY_COUNTER => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
		self::COOKIE        => array(
			'data_type'     => 'String_Type',
			'default_value' => null,
		),
		self::NONCE         => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::POST_ID       => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => null,
		),
		self::SECRET        => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::FIRST_NAME    => array(
			'data_type'     => 'String_Type',
			'data_size'     => 256,
			'default_value' => '',
		),
		self::LAST_NAME     => array(
			'data_type'     => 'String_Type',
			'data_size'     => 256,
			'default_value' => '',
		),
	);

	/**
	 * @param $pending_approval
	 */
	static function set_pending_approval_counter( $pending_approval ) {
		set_transient( 'bitcoin_bank_registering_approval_pending', $pending_approval );
	}

	/**
	 * @return mixed
	 */
	static function get_pending_approval_counter() {
		$pending_approval = get_transient( 'bitcoin_bank_registering_approval_pending' );
		if ( false === $pending_approval ) {
			$reg_data         = new Registration_Db_Table();
			$pending_approval = $reg_data->load_data( self::STATE, self::STATE_APPROVAL_PENDING );
			set_transient( 'bitcoin_bank_registering_approval_pending', $pending_approval );
		}
		return $pending_approval;
	}

	/**
	 * @param null $conditions
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function load_data( $conditions = null, $value = null ) {
		$result = parent::load_data( $conditions, $value );

		$this->my_state = $this->get_data( self::STATE );

		return $result;
	}

	/**
	 * @return mixed
	 */
	public function save_data() {
		$new_state = $this->get_data( self::STATE );

		if ( $new_state !== $this->my_state ) {
			if ( self::STATE_APPROVAL_PENDING === $this->my_state ) {
				$this->change_approval_pending( -1 );
			}

			if ( self::STATE_APPROVAL_PENDING === $new_state ) {
				$this->change_approval_pending( 1 );
			}
		}

		return parent::save_data();
	}

	/**
	 * @param $diff
	 */
	protected function change_approval_pending( $diff ) {
		$pending_approval  = self::get_pending_approval_counter();
		$pending_approval += $diff;
		self::set_pending_approval_counter( $pending_approval );
	}
}
