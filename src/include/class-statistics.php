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

class Statistics {

	/**
	 * @param $post_id
	 */
	public static function statistics_page_view( $post_id ) {
		self::register_event( $post_id, Statistics_Db_Table::PAGE_VIEW );
	}

	/**
	 * @param $post_id
	 */
	public static function statistics_register( $post_id ) {
		self::register_event( $post_id, Statistics_Db_Table::REGISTER );
	}

	/**
	 * @param $post_id
	 */
	public static function statistics_verify_email( $post_id ) {
		self::register_event( $post_id, Statistics_Db_Table::VERIFY );
	}

	/**
	 * @param $post_id
	 */
	public static function statistics_completed( $post_id ) {
		self::register_event( $post_id, Statistics_Db_Table::COMPLETED );
	}

	/**
	 * @param $post_id
	 * @param $event_type
	 */
	public static function register_event( $post_id, $event_type ) {
		if ( gettype( $post_id ) === 'integer' ) {
			$statistics_data = new Statistics_Db_Table();
			if ( $statistics_data->load_data( Statistics_Db_Table::POST_ID, $post_id ) ) {
				$number = $statistics_data->get_data( $event_type );
				$number = $number + 1;
				$statistics_data->set_data( $event_type, $number );
				$statistics_data->save_data();
			} else {
				$statistics_data->set_data( Statistics_Db_Table::POST_ID, $post_id );
				$statistics_data->set_data( $event_type, 1 );
				$statistics_data->save_data();
			}
		} else {
			Debug_Logger::write_debug_error( ' invalid data type:', gettype( $post_id ) );
		}
	}
}
