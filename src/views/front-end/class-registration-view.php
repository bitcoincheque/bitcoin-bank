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
use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\P;

class Registration_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var P */
	public $message;
	/** @var Text_Line */
	public $email;
	/** @var Check_Box */
	public $accept_terms;
	/** @var Push_Button */
	public $button_start_registration;
	/** @var Link */
	public $link2;
	/** @var Link */
	public $link1;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Registration_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->status_bar_header = new Status_Bar();
		$this->email = new Text_Line( esc_html__( 'E-mail address:', 'bitcoin-bank' ), '', 'email' );
		$design_options = get_option( Settings_Form_Options::OPTION_NAME );
		if ( $design_options[ Settings_Form_Options::REGISTER_MUST_ACCEPT_TERMS ] ) {
			$this->accept_terms = $this->make_terms_checkbox();
		}
		/* translators: Button label, start register as new user. */
		$this->button_start_registration = new Push_Button( esc_html__( 'Register', 'bitcoin-bank' ) );
		$this->status_bar_footer = new Status_Bar();
        parent::__construct( $id, $controller );
	}

	public function create_content( $parameters = null ) {
		$this->add_header( 'status_bar_header', $this->status_bar_header );

		$this->message = new P( esc_html__( 'Enter your e-mail address to register.', 'bitcoin-bank' ) );
		$this->add_header( 'msg', $this->message );

		$this->add_form_input( 'email', $this->email);

		if ( isset ($this->accept_terms) ) {
			$this->add_form_input( 'accept_terms', $this->accept_terms );
		}

		$this->add_button( 'button_start_registration', $this->button_start_registration );

		$linking_options   = new Settings_Linking_Options();
		$login_url         = $linking_options->get_login_url();
		$lost_password_url = $linking_options->get_lost_password_url();

		$this->link1 = new Link( $login_url, esc_html__( 'Log in', 'bitcoin-bank' ) );
		$this->add_footer( 'link1', $this->link1 );

		$this->link2 = new Link( $lost_password_url, esc_html__( 'Forgotten username or password?', 'bitcoin-bank' ) );
		$this->add_footer( 'link2', $this->link2 );

		$this->add_footer( 'status_bar_footer', $this->status_bar_footer );

		parent::create_content( $parameters );
	}
}
