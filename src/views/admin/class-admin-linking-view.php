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
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Html_Text;
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\A;

class Admin_Linking_View extends Admin_Std_View {

	const REGISTER_PAGE_LINK   = 'RegisterPageLink';
	const LOGIN_PAGE_LINK      = 'LoginPageLink';
	const PROFILE_PAGE_LINK    = 'ProfilePageLink';
	const PASSWORD_PAGE_LINK   = 'PasswordPageLink';
	const TERMS_PAGE           = 'TermsPage';
	const LOGOUT_PAGE_REDIRECT = 'LogoutPage';

	/** @var Text_Line */
	public $header1;
	/** @var Text_Line */
	public $register_page_link;
	/** @var Text_Line */
	public $login_page_link;
	/** @var Text_Line */
	public $profile_page_link;
	/** @var Text_Line */
	public $password_page_link;
	/** @var Text_Line */
	public $terms_page;
	/** @var Text_Line */
	public $login_redirect_page;
	/** @var Text_Line */
	public $logout_page;
	/** @var Text_Line */
	public $account_list_page;
	/** @var Text_Line */
	public $transaction_page;
	/** @var Text_Line */
	public $cheque_details_page;
	/** @var Text_Line */
	public $send_cheque_page;
	/** @var Text_Line */
	public $receive_cheque_page;
	/** @var Push_Button */
	public $std_submit;

	/**
	 * Admin_Linking_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		$this->register_page_link = new Text_Line();
		$this->login_page_link = new Text_Line();
		$this->profile_page_link = new Text_Line();
		$this->password_page_link = new Text_Line();
		$this->terms_page = new Text_Line();
		$this->login_redirect_page = new Text_Line();
		$this->logout_page = new Text_Line();
		$this->account_list_page = new Text_Line();
		$this->transaction_page = new Text_Line();
		$this->cheque_details_page = new Text_Line();
		$this->send_cheque_page = new Text_Line();
		$this->receive_cheque_page = new Text_Line();

		/* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->std_submit->set_primary();

		/* translators: Button label */
		$this->create_login_page = new Push_Button( esc_html__( 'Create', 'bitcoin-bank' ) );
		$this->edit_login_page = new A(esc_html__( 'Edit', 'bitcoin-bank' ), 'http://localhost/wordpress-latest/wp-admin/post.php?post=8&action=edit' );
		$this->view_login_page = new A(esc_html__( 'View', 'bitcoin-bank' ), 'http://localhost/wordpress-latest/login/' );

		parent::__construct( $id, $controller );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Register page:', 'bitcoin-bank' ),
				array( 'for' => 'register_page_link' ))
		);

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page visitors can register as new users. You must put the shortcode %s on this page.', 'bitcoin-bank' ), '<b>[rml_register]</b>' );
		$html_text = new Html_Text( $msg );
		$cell = array(
			$this->register_page_link,
			'&nbsp;',
			$this->create_login_page,
			'&nbsp;',
			$this->edit_login_page,
			'&nbsp;',
			$this->view_login_page,
			new P($html_text)
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Login page:', 'bitcoin-bank' ),
				array( 'for' => 'login_page_link' ))
		);

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page users can log in and out. You must put the shortcode %s on this page.', 'bitcoin-bank' ),'<b>[rml_login]</b>' );
		$html_text = new Html_Text( $msg );
		$cell = array(
			$this->login_page_link,
			new P($html_text)
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Profile page:', 'bitcoin-bank' ),
				array( 'for' => 'profile_page_link' ))
		);

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page users can update their personal information. You must put the shortcode %s on this page.', 'bitcoin-bank' ), '<b>[rml_profile]</b>' );
		$html_text = new Html_Text( $msg );
		$cell = array(
			$this->profile_page_link,
			new P($html_text)
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Change/reset password page:', 'bitcoin-bank' ),
				array( 'for' => 'password_page_link' ))
		);

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page users can change their password. If not logged in, the user can ask for a password reset link, which will be sent by e-mail. You must put the shortcode %s on this page.', 'bitcoin-bank' ), '<b>[rml_password]</b>' );
		$html_text = new Html_Text( $msg );
		$cell = array(
			$this->password_page_link,
			new P($html_text)
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Terms and condition page:', 'bitcoin-bank' ),
				array( 'for' => 'terms_page' ))
		);

		$cell = array(
			$this->terms_page,
			new P(esc_html__( 'On this page you can write your terms and conditions, which new users must accept to register.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Logout redirect link:', 'bitcoin-bank' ),
				array( 'for' => 'logout_page' ))
		);

		$cell = array(
			$this->logout_page,
			new P(esc_html__( 'After user has logged out, he will be redirected to this page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Login redirect link:', 'bitcoin-bank' ),
				array( 'for' => 'logout_redirect_page' ))
		);

		$cell = array(
			$this->logout_page,
			new P(esc_html__( 'After user has logged in, he will be redirected to this page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Account page:', 'bitcoin-bank' ),
				array( 'for' => 'account_list_page' ))
		);

		$cell = array(
			$this->account_list_page,
			new P(esc_html__( 'Account page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Transaction list page:', 'bitcoin-bank' ),
				array( 'for' => 'transaction_page' ))
		);

		$cell = array(
			$this->transaction_page,
			new P(esc_html__( 'Transaction list page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Send cheque page:', 'bitcoin-bank' ),
				array( 'for' => 'cheque_details_page' ))
		);

		$cell = array(
			$this->cheque_details_page,
			new P(esc_html__( 'Send cheque page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Receive cheque page:', 'bitcoin-bank' ),
				array( 'for' => 'send_cheque_page' ))
		);

		$cell = array(
			$this->send_cheque_page,
			new P(esc_html__( 'Receive cheque page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Cheque detail page:', 'bitcoin-bank' ),
				array( 'for' => 'receive_cheque_page' ))
		);

		$cell = array(
			$this->receive_cheque_page,
			new P(esc_html__( 'Cheque detail page.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		parent::create_content( $parameters );
	}
}
