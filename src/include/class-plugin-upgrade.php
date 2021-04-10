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

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Utils\Debug_Logger;

class Plugin_Upgrade {

	/**
	 * @return array|bool
	 */
	static function get_previous_version_data() {
		$previous_plugin_version_data = false;
		$option                       = get_option( 'bcq_bitcoinbank_textfading_option' );
		if ( isset( $option ) ) {
			if ( false !== $option ) {
				if ( is_array( $option ) ) {
					if ( array_key_exists( 'fade_height', $option ) ) {
						/* Version 1 detected. */
						$previous_plugin_version_data            = array();
						$previous_plugin_version_data['Edition'] = 'Standard';
						$previous_plugin_version_data['Version'] = '1';
					}
				}
			}
		}
		return $previous_plugin_version_data;
	}

	/**
	 * @param $plugin_slug
	 * @param $plugin_version_data
	 * @param bool                $current_plugin_version_data
	 */
	static function upgrade( $plugin_slug, $plugin_version_data, $current_plugin_version_data = false ) {
		/* Remove additional info like beta version appended to string. */
		$plugin_version_data['Version'] = trim( $plugin_version_data['Version'] );
		$plugin_version_data['Version'] = strstr( $plugin_version_data['Version'], ' ', true );

		$current_plugin_version_data['Version'] = trim( $current_plugin_version_data['Version'] );
		$current_plugin_version_data['Version'] = strstr( $current_plugin_version_data['Version'], ' ', true );

		if ( false === $current_plugin_version_data ) {
			/* Check if any options from previous version exists. */
			$current_plugin_version_data = self::get_previous_version_data();
		}

		if ( false !== $current_plugin_version_data ) {
			if ( $plugin_version_data !== $current_plugin_version_data ) {
				if ( '0.0.1' === $current_plugin_version_data['Version'] ) {
					$next_version = '0.0.2';
					Debug_Logger::write_debug_note( 'Step upgrade from ' . $current_plugin_version_data['Version'] . ' to ' . $next_version );
					if ( ! self::upgrade_from_0_0_1_to_0_0_2() ) {
						Debug_Logger::write_debug_warning( 'Upgrading ' . $plugin_slug . ' from ' . $current_plugin_version_data['Version'] . ' to ' . $next_version . ' failed. Using default settings.' );
					}
					$current_plugin_version_data['Version'] = $next_version;
				}

				/* Add more upgrade handlers as needed */

			}

			if ( $current_plugin_version_data !== $plugin_version_data ) {
				Debug_Logger::write_debug_warning( 'Unhandled upgrade of ' . $plugin_slug . ' from version ' . Plugin_Container::get_plugin_version_name( false, $current_plugin_version_data ) . ' to ' . Plugin_Container::get_plugin_version_name( false, $plugin_version_data ) . '.' );
			}
		}
	}

	static function upgrade_from_0_0_1_to_0_0_2() {
		return true;
	}
}
