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
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Hr;

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
		$this->google_read = new Check_Box(esc_html__( 'Visitors from search engines like Google can read protected content without login.', 'bitcoin-bank' ));
		$this->approve_new_users = new Check_Box(esc_html__( 'All new users must be approved.', 'bitcoin-bank' ));
		$this->approve_proxy = new Text_Box();
		/* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->std_submit->set_primary();
		parent::__construct( $id, $controller );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Search Engine settings', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Search engine access:', 'bitcoin-bank' ),
				array( 'for' => 'google_read' ))
		);
		$cell = array(
			$this->google_read,
			new P(esc_html__( 'This will also allow search engines to read and index your entire page and could improve your ratings on searches.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		$this->add_content(new Hr());
		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Approve new users', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'Approve new user before membership.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Approve new users:', 'bitcoin-bank' ),
				array( 'for' => 'approve_new_users' ))
		);
		$cell = array(
			$this->approve_new_users,
			new P(esc_html__( 'Pending approval are listed in the Registrations page', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Users with approval rights:', 'bitcoin-bank' ),
				array( 'for' => 'approve_proxy' ))
		);
		$cell = array(
			$this->approve_proxy,
			new P(esc_html__( 'List of user who can approve new users. One username per line. These user can access the Registration page. Leave blank to restrict approval to site admin.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		parent::create_content( $parameters );
	}
}
