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

use WP_PluginFramework\Views\Admin_Panel_Std_View;
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\Status_Bar;

class Admin_Statistics_View extends Admin_Panel_Std_View {

	/** @var Text_Line */
	public $text_user_login;
	/** @var Text_Line */
	public $text_first_name;
	/** @var Text_Line */
	public $text_last_name;
	/** @var Text_Line */
	public $text_email;
	/** @var Push_Button */
	public $button_update;
	/** @var Link */
	public $link_change_password;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Admin_Statistics_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->text_user_login = new Text_Line( 'Username:' );
		$this->register_component( 'text_user_login', $this->text_user_login );

		$this->text_first_name = new Text_Line( 'First name:' );
		$this->register_component( 'text_first_name', $this->text_first_name );

		$this->text_last_name = new Text_Line( 'Last name:' );
		$this->register_component( 'text_last_name', $this->text_last_name );

		$this->text_email = new Text_Line( 'E-mail:' );
		$this->register_component( 'text_email', $this->text_email );

		$this->button_update = new Push_Button( 'Save' );
		$this->register_component( 'button_update', $this->button_update );

		$this->link_change_password = new Link( '/password', 'Change password' );
		$this->register_component( 'link_change_password', $this->link_change_password );

		$this->status_bar_footer = new Status_Bar();
		$this->register_component( 'status_bar_footer', $this->status_bar_footer );

		parent::__construct( $id, $controller );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$this->text_user_login->set_text( $parameters['user_login'] );
		$this->text_first_name->set_text( $parameters['first_name'] );
		$this->text_last_name->set_text( $parameters['last_name'] );
		$this->text_email->set_text( $parameters['user_email'] );

		parent::create_content( $parameters );
	}
}
