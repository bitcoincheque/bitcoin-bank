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

use WP_PluginFramework\Views\Admin_Std_View;
use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Text_Box;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Access_View extends Admin_Std_View {

	const GOOGLE_READ       = 'google_read';
	const APPROVE_NEW_USERS = 'approve_new_users';

	/** @var Check_Box */
	public $google_read;
	/** @var Check_Box */
	public $approve_new_users;
	/** @var Check_Box */
	public $approve_proxy;
	/** @var Push_Button */
	public $std_submit;

	/**
	 * Admin_Access_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$this->google_read = new Check_Box();
		$this->register_component( 'google_read', $this->google_read );

		$this->approve_new_users = new Check_Box();
		$this->register_component( 'approve_new_users', $this->approve_new_users );

		$this->approve_proxy = new Text_Box();
		$this->register_component( 'approve_proxy', $this->approve_proxy );

		/* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->register_component( 'std_submit', $this->std_submit );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$category = array(
			'name'   => 'seo',
			'header' => esc_html__( 'Search Engine settings', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

		$category = array(
			'name'        => 'user_approve',
			'header'      => esc_html__( 'Approve new users', 'bitcoin-bank' ),
			'description' => esc_html__( 'Approve new user before membership.', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

		$this->google_read->set_property( 'label', esc_html__( 'Search engine access:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Access_Options::GOOGLE_READ ] = esc_html__( 'Visitors from search engines like Google can read protected content without login.', 'bitcoin-bank' );
		$this->google_read->set_property( 'items', $items );
		$this->google_read->set_property( 'description', esc_html__( 'This will also allow search engines to read and index your entire page and could improve your ratings on searches.', 'bitcoin-bank' ) );
		$this->google_read->set_property( 'category', 'seo' );

		$this->approve_new_users->set_property( 'label', esc_html__( 'Approve new users:', 'bitcoin-bank' ) );
		$items2 = array();
		$items2[ Settings_Access_Options::APPROVE_NEW_USERS ] = esc_html__( 'All new users must be approved.', 'bitcoin-bank' );
		$this->approve_new_users->set_property( 'items', $items2 );
		$this->approve_new_users->set_property( 'description', esc_html__( 'Pending approval are listed in the Registrations page', 'bitcoin-bank' ) );
		$this->approve_new_users->set_property( 'category', 'user_approve' );

		$this->approve_proxy->set_property( 'label', esc_html__( 'Users with approval rights:', 'bitcoin-bank' ) );
		$registration_link = '<a href="">Registration</a>';
		$this->approve_proxy->set_property( 'description', esc_html__( 'List of user who can approve new users. One username per line. These user can access the Registration page. Leave blank to restrict approval to site admin.', 'bitcoin-bank' ) );
		$this->approve_proxy->set_property( 'category', 'user_approve' );

		$this->add_form_input( 'google_read', $this->google_read );

		$this->add_form_input( 'approve_new_users', $this->approve_new_users );

		$this->add_form_input( 'approve_proxy', $this->approve_proxy );

		$this->add_button( 'std_submit', $this->std_submit );

		$this->std_submit->set_primary( true );

		parent::create_content( $parameters );
	}
}
