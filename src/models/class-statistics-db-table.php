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

/**
 * Summary.
 *
 * Description.
 */
class Statistics_Db_Table extends Active_Record {

	/* Database table name: */
	const TABLE_NAME = 'bcq_bitcoin_bank_statistics';

	/* List of table field names: */
	const POST_ID   = 'post_id';
	const PAGE_VIEW = 'page_view';
	const REGISTER  = 'register';
	const VERIFY    = 'verify';
	const COMPLETED = 'completed';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::POST_ID   => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
		self::PAGE_VIEW => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
		self::REGISTER  => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
		self::VERIFY    => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
		self::COMPLETED => array(
			'data_type'     => 'Unsigned_Integer_Type',
			'default_value' => 0,
		),
	);
}
