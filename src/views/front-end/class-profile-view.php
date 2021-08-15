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
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\Status_Bar;

class Profile_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Text_Line */
	public $user_login;
	/** @var Text_Line */
	public $first_name;
	/** @var Text_Line */
	public $last_name;
	/** @var Text_Line */
	public $user_email;
	/** @var Push_Button */
	public $std_submit;
	/** @var Link */
	public $link_change_password;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Profile_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->status_bar_header = new Status_Bar();
		$prop['readonly'] = true;
		$this->user_login = new Text_Line( esc_html__( 'Username', 'bitcoin-bank' ), '', 'user_login', $prop );
		$this->first_name = new Text_Line( esc_html__( 'First name', 'bitcoin-bank' ), '', 'first_name' );
		$this->last_name = new Text_Line( esc_html__( 'Last name', 'bitcoin-bank' ), '', 'last_name' );
		$this->user_email = new Text_Line( esc_html__( 'E-mail', 'bitcoin-bank' ), '', 'user_email' );
		/* translators: Button label. */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->status_bar_footer = new Status_Bar();
		parent::__construct( $id, $controller );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$this->add_header('status_bar_header', $this->status_bar_header);

		$this->user_login->set_property( 'description', esc_html__( 'Usernames cannot be changed.', 'bitcoin-bank' ) );
		$this->add_form_input( 'text_user_login',  $this->user_login);

		$this->add_form_input( 'text_first_name', $this->first_name);

		$this->add_form_input(  'text_last_name', $this->last_name);

		$this->add_form_input( 'text_email',  $this->user_email);

		$this->add_button( 'std_submit', $this->std_submit );

		$linking_options   = new Settings_Linking_Options();
		$lost_password_url = $linking_options->get_lost_password_url();
		/* translators: Link label. */
		$this->link_change_password = new Link( $lost_password_url, esc_html__( 'Change password', 'bitcoin-bank' ) );
		$this->add_footer( 'link_change_password', $this->link_change_password );

		$this->add_footer( 'std_status_bar', $this->status_bar_footer );

		parent::create_content( $parameters );
	}
}
