<?php
/** Bitcoin Bank plugin for WordPress.
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

use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;

class Register_User_Data_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Text_Line */
	public $username;
	/** @var Text_Line */
	public $password;
	/** @var Text_Line */
	public $first_name;
	/** @var Text_Line */
	public $last_name;
	/** @var Push_Button */
	public $button_register;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Register_User_Data_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->status_bar_header = new Status_Bar();
		$this->username = new Text_Line( esc_html__( 'Username:', 'bitcoin-bank' ), '', 'username' );
		$this->password = new Text_Line( esc_html__( 'Password:', 'bitcoin-bank' ), '', 'password', array( 'type' => 'password' ) );

		$register_option = get_option( Settings_Form_Options::OPTION_NAME );
		if ( $register_option[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
			/* translators: A person's first name. */
			$this->first_name = new Text_Line( esc_html__( 'First name:', 'bitcoin-bank' ), '', 'first_name' );
		}

		if ( $register_option[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
			/* translators: A person's last name. */
			$this->last_name = new Text_Line( esc_html__( 'Last name:', 'bitcoin-bank' ), '', 'last_name' );
		}

		/* translators: Button label, start register as new user. */
		$this->button_register = new Push_Button( esc_html__( 'Register', 'bitcoin-bank' ), Push_Button::METHOD_POST );
		$this->status_bar_footer = new Status_Bar();
		parent::__construct( $id, $controller );
	}

	public function create_content( $parameters = null ) {
		$this->set_method( self::SEND_METHOD_POST );

		/* translators: Headline for registration form. */
		$h = new H( 3, esc_html__( 'Complete your registration', 'bitcoin-bank' ) );
		$this->add_header( 'header', $h );

		$this->add_header( 'status_bar_header', $this->status_bar_header );

		$p2 = new P( esc_html__( 'Please select your username and password.', 'bitcoin-bank' ) );
		$this->add_header( 'header2', $p2 );

		$this->add_form_input( 'username', $this->username );

		$this->add_form_input( 'password', $this->password );

		if( isset ($this->first_name) ) {
			$this->add_form_input( 'first_name', $this->first_name );
		}

		if( isset ($this->last_name) ) {
			$this->add_form_input( 'last_name', $this->last_name );
		}

		$this->add_button( 'button_register', $this->button_register );

		$this->add_footer( 'status_bar_footer', $this->status_bar_footer );

		parent::create_content( $parameters );
	}
}
