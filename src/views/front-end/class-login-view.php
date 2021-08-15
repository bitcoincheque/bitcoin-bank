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

use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\Status_Bar;

class Login_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Text_Line */
	public $username;
	/** @var Text_Line */
	public $password;
	/** @var Check_Box */
	public $remember;
	/** @var Push_Button */
	public $button_login;
	/** @var Link */
	public $link2;
	/** @var Link */
	public $link1;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Login_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->status_bar_header = new Status_Bar();
		$this->username = new Text_Line( esc_html__( 'Username:', 'bitcoin-bank' ), '', 'username' );
		$this->password = new Text_Line( esc_html__( 'Password:', 'bitcoin-bank' ), '', 'password', array( 'type' => 'password' ) );
		/* translators: Login form, checkbox label. */
		$this->remember = new Check_Box( esc_html__( 'Remember me', 'bitcoin-bank' ), 0, 'remember' );
		/* translators: Button label. */
		$this->button_login = new Push_Button( esc_html__( 'Log in', 'bitcoin-bank' ), Push_Button::METHOD_POST );
		$this->status_bar_footer = new Status_Bar();
		parent::__construct( $id, $controller );
	}

	public function create_content( $parameters = null ) {
		$this->set_method( self::SEND_METHOD_POST );
		$this->add_header( 'status_bar_header', $this->status_bar_header );
		$this->add_form_input( 'username', $this->username );
		$this->add_form_input( 'password',  $this->password);
		$this->add_form_input( 'remember',  $this->remember);
		$this->add_button( 'button_login',  $this->button_login);

		$linking_options = new Settings_Linking_Options();
		$register_url    = $linking_options->get_register_url();
		/* translators: Link label. */
		$this->link2 = new Link( $register_url, esc_html__( 'Register', 'bitcoin-bank' ) );
		$this->add_footer( 'link2',  $this->link2);

		$lost_password_url = $linking_options->get_lost_password_url();
		/* translators: Link label. */
		$this->link1 = new Link( $lost_password_url, esc_html__( 'Forgotten username or password?', 'bitcoin-bank' ) );
		$this->add_footer( 'link1',  $this->link1);

		$this->add_footer( 'status_bar_footer',  $this->status_bar_footer);

		parent::create_content( $parameters );
	}

	/**
	 * @param null $parameters
	 *
	 * @return string
	 */
	public function draw_view( $parameters = null ) {
		if(is_array($parameters))
		{
			if(isset($parameters['status_message']))
			{
				if ($parameters['status_message'])
				{
					$this->status_bar_header->set_status_html($parameters['status_message'], Status_Bar::STATUS_SUCCESS);
				}
			}
		}

		return parent::draw_view( $parameters );
	}
}
