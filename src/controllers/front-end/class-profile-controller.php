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

use WP_PluginFramework\Controllers\Std_Controller;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Profile_Controller extends Std_Controller {

	/** @var Profile_View */
	public $view;

	/**
	 * Construction.
	 */
	public function __construct() {
		parent::__construct( 'BCQ_BitcoinBank\Profile_Model', 'BCQ_BitcoinBank\Profile_View' );
		$this->set_permission( true );
	}

	/**
	 * @param $event_source
	 *
	 * @return array|null
	 */
	protected function read_nonce_protected_data( $event_source ) {
		$nonce_protected_data = parent::read_nonce_protected_data( $event_source );

		if ( $this->get_server_context_data( 'have_logged_in' ) ) {
			$nonce_protected_data = $this->get_nonce_protected_data();
			$nonce_protected_data = $this->calculate_wp_nonce( $nonce_protected_data );

			$this->register_event( 'ButtonLogin', 'click', 'post' );
		}

		return $nonce_protected_data;
	}

	/**
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	protected function load_values( $values = array() ) {
		if ( $this->get_server_context_data( 'have_logged_in' ) ) {
			return $this->load_model_values( $values );
		} else {
			return parent::load_values( $values );
		}
	}

	/**
	 * @param $data_record
	 */
	public function handle_save_success( $data_record ) {
		$this->view->status_bar_footer->set_status_text( esc_html__( 'Your profile has been updated.', 'bitcoin-bank' ), Status_Bar::STATUS_SUCCESS );

		/*
		 Update compact widget with new username. */
		/* Select login username or full name if it exists. */
		$wp_user_id = get_current_user_id();
		$user_info  = get_userdata( $wp_user_id );
		$username   = $user_info->user_login;
		$first_name = $user_info->first_name;
		$last_name  = $user_info->last_name;

		if ( ( '' === $first_name ) && ( '' === $last_name ) ) {
			$display_name = $username;
		} else {
			$display_name = $first_name . ' ' . $last_name;
		}

		$this->view->update_client_dom( 'div.bcq_login_widget span', 'text', $display_name );
	}

	/**
	 *
	 */
	public function button_login_click() {
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
