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

use WP_PluginFramework\Views\Admin_Std_View;
use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;

class Admin_Security_View extends Admin_Std_View {

	/** @var Check_Box */
	public $username_not_email;
	/** @var Push_Button */
	public $std_submit;

	/**
	 * Admin_Security_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$this->username_not_email = new Check_Box();
		$this->register_component( 'username_not_email', $this->username_not_email );

		/* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->register_component( 'std_submit', $this->std_submit );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$this->username_not_email->set_property( 'label', esc_html__( 'E-mail as username:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Security_Options::USERNAME_NOT_EMAIL ] = esc_html__( 'Forbid users to register using their e-mail address as username.', 'bitcoin-bank' );
		$this->username_not_email->set_property( 'items', $items );
		$this->username_not_email->set_property( 'description', esc_html__( 'If a visitor tries to register using an e-mail address as username, he will be asked to correct it by enter a non-email.', 'bitcoin-bank' ) );

		$this->add_form_input( 'username_not_email', $this->username_not_email );
		$this->add_button( 'std_submit', $this->std_submit );
		$this->std_submit->set_primary( true );

		parent::create_content( $parameters );
	}
}
