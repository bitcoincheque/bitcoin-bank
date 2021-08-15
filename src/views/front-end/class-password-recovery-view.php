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
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\P;

class Password_Recovery_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var P */
	public $header;
	/** @var Text_Line */
	public $email;
	/** @var Push_Button */
	public $button_send_password;
	/** @var Link */
	public $link2;
	/** @var Link */
	public $link1;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Password_Recovery_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->status_bar_header = new Status_Bar();
		$this->email = new text_Line( null, '', 'email' );
		/* translators: Button label. */
		$this->button_send_password = new Push_Button( esc_html__( 'Send e-mail', 'bitcoin-bank' ) );
		$this->status_bar_footer = new Status_Bar();
		parent::__construct( $id, $controller );
	}

	public function create_content( $parameters = null ) {
		$this->add_header( 'status_bar_header', $this->status_bar_header );

		$this->header = new P( esc_html__( 'Enter your e-mail and we will send your username and password reset link.', 'bitcoin-bank' ));
		$this->add_header( 'header', $this->header );

		$this->add_form_input(
			'email',
			$this->email,
			esc_html__( 'E-mail address:', 'bitcoin-bank' )
		);

		$this->add_button( 'button_send_password', $this->button_send_password );

		$linking_options = new Settings_Linking_Options();
		$login_url       = $linking_options->get_login_url();
		$this->link1 = new Link( $login_url, esc_html__( 'Log in', 'bitcoin-bank' ) );
		$this->add_footer( 'link1', $this->link1 );

		$register_url    = $linking_options->get_register_url();
		/* translators: Link label. */
		$this->link2 = new Link( $register_url, esc_html__( 'Register', 'bitcoin-bank' ) );
		$this->add_footer( 'link2', $this->link2 );

		$this->add_footer( 'status_bar_footer', $this->status_bar_footer );

		parent::create_content( $parameters );
	}
}
