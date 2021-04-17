<?php
/** Bitcoin Bank plugin for WordPress
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
 *  @package Bitcoin-Bank
 */

/*
Plugin Name: Bitcoin Bank
Plugin URI: https://www.bitcoindemobank.com
Description: Bitcoin Bank.
Version: 0.0.21
Author: Arild Hegvik
License: GNU GPLv3
License URI: license.txt
Text Domain: bitcoin-bank
Domain Path: /asset/languages/
*/

namespace BCQ_BitcoinBank
{
	defined( 'ABSPATH' ) || exit;

	if ( ! defined( 'BCQ_BITCOIN_BANK_PLUGIN_FILE' ) ) {
		define( 'BCQ_BITCOIN_BANK_PLUGIN_FILE', __FILE__ );
		define( 'BCQ_BITCOIN_BANK_EDITION_NAME', 'Standard' );
		define( 'BCQ_BITCOIN_BANK_EDITION_CODE', 'Std' );
		define( 'BCQ_BITCOIN_BANK_EDITION_REV', '1' );
	}

	if ( ! class_exists( 'Bitcoin_Bank_Plugin' ) ) {
		include_once __DIR__ . '/src/include/class-bitcoinbank-plugin.php';
	}

	Bitcoin_Bank_Plugin::instance();

}
