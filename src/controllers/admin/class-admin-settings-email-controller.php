<?php
/**
 * Bitcoin Bank plugin for WordPress.
 *
 *  Copyright (C) 2018 Arild Hegvik
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

use WP_PluginFramework\Controllers\Admin_Controller;
use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Settings_Email_Controller extends Admin_Controller {

	/**
	 * Construction.
	 */
	public function __construct() {
		parent::__construct( 'BCQ_BitcoinBank\Settings_Email_Options', 'BCQ_BitcoinBank\Admin_Email_View' );

		$this->tab_name = 'email';
	}

	/**
	 *
	 */
	protected function enqueue_script() {
		parent::enqueue_script();

		$plugin_version = Plugin_Container::get_plugin_version();

		$style_handler = 'bcq_bitcoin_bank_plugin_style_handler';
		$style_url     = plugins_url() . '/bitcoin-bank/asset/css/bitcoin-bank-style.css';
		wp_enqueue_style( $style_handler, $style_url, array(), $plugin_version );
	}
}
