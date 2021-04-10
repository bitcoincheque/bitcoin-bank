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

/**
 * Summary.
 *
 * Description.
 */
class Password_Recovery_Change_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Text_Line */
	public $password1;
	/** @var Text_Line */
	public $password2;
	/** @var Push_Button */
	public $button_save_password;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Password_Recovery_Change_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$this->status_bar_header = new Status_Bar();
		$this->register_component( 'status_bar_header', $this->status_bar_header );

		$this->password1 = new Text_Line( esc_html__( 'New password:', 'bitcoin-bank' ), '', 'password', array( 'type' => 'password' ) );
		$this->register_component( 'password1', $this->password1 );

		$this->password2 = new Text_Line( esc_html__( 'Confirm password:', 'bitcoin-bank' ), '', 'confirm_password', array( 'type' => 'password' ) );
		$this->register_component( 'password2', $this->password2 );

		/* translators: Button label. */
		$this->button_save_password = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ), Push_Button::METHOD_POST );
		$this->register_component( 'button_save_password', $this->button_save_password );

		$this->status_bar_footer = new Status_Bar();
		$this->register_component( 'status_bar_footer', $this->status_bar_footer );
	}

	public function create_content( $parameters = null ) {
		$this->set_method( self::SEND_METHOD_POST );
		$this->add_header( 'status_bar_header', $this->status_bar_header );
		$this->add_form_input( 'password1', $this->password1 );
		$this->add_form_input( 'password2', $this->password2 );
		$this->add_button( 'button_save_password', $this->button_save_password );
		$this->add_footer( 'status_bar_footer', $this->status_bar_footer );
		parent::create_content( $parameters );
	}
}
