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

/**
 * Summary.
 *
 * Description.
 */
class Settings_Access_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_access_option';

	const GOOGLE_READ       = 'google_read';
	const APPROVE_NEW_USERS = 'approve_new_users';
	const APPROVE_PROXY     = 'approve_proxy';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::GOOGLE_READ       => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::APPROVE_NEW_USERS => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::APPROVE_PROXY     => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
	);
}
