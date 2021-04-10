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
	public $logout_page;
    /** @var Text_Line */
    public $login_redirect_page;
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
		parent::__construct( $id, $controller );

		$this->register_page_link = new Text_Line();
		$this->register_component( 'register_page_link', $this->register_page_link );

		$this->login_page_link = new Text_Line();
		$this->register_component( 'login_page_link', $this->login_page_link );

		$this->profile_page_link = new Text_Line();
		$this->register_component( 'profile_page_link', $this->profile_page_link );

		$this->password_page_link = new Text_Line();
		$this->register_component( 'password_page_link', $this->password_page_link );

		$this->terms_page = new Text_Line();
		$this->register_component( 'terms_page', $this->terms_page );

		$this->logout_page = new Text_Line();
		$this->register_component( 'logout_page', $this->logout_page );

        $this->login_redirect_page = new Text_Line();
        $this->register_component( 'login_redirect_page', $this->login_redirect_page );

        $this->account_list_page = new Text_Line();
        $this->register_component( 'account_list_page', $this->account_list_page );

        $this->transaction_page = new Text_Line();
        $this->register_component( 'transaction_page', $this->transaction_page );

        $this->cheque_details_page = new Text_Line();
        $this->register_component( 'cheque_details_page', $this->cheque_details_page );

        $this->send_cheque_page = new Text_Line();
        $this->register_component( 'send_cheque_page', $this->send_cheque_page );

        $this->receive_cheque_page = new Text_Line();
        $this->register_component( 'receive_cheque_page', $this->receive_cheque_page );

        /* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->register_component( 'std_submit', $this->std_submit );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$this->register_page_link->set_property( 'label', esc_html__( 'Register page:', 'bitcoin-bank' ) );

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page visitors can register as new users. You must put the shortcode %s on this page.', 'bitcoin-bank' ), '<b>[bcq_register]</b>' );
		$html_text = new Html_Text( $msg );
		$this->register_page_link->set_property( 'description', $html_text );

		$this->login_page_link->set_property( 'label', esc_html__( 'Login page:', 'bitcoin-bank' ) );

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page users can log in and out. You must put the shortcode %s on this page.', 'bitcoin-bank' ),'<b>[bcq_login]</b>' );
		$html_text = new Html_Text( $msg );
		$this->login_page_link->set_property( 'description', $html_text );

		$this->profile_page_link->set_property( 'label', esc_html__( 'Profile page:', 'bitcoin-bank' ) );

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page users can update their personal information. You must put the shortcode %s on this page.', 'bitcoin-bank' ), '<b>[bcq_profile]</b>' );
		$html_text = new Html_Text( $msg );
		$this->profile_page_link->set_property( 'description', $html_text );

		$this->password_page_link->set_property( 'label', esc_html__( 'Change/reset password page:', 'bitcoin-bank' ) );

		/* translators: %s will show the shortcode. */
		$msg = sprintf( esc_html__( 'On this page users can change their password. If not logged in, the user can ask for a password reset link, which will be sent by e-mail. You must put the shortcode %s on this page.', 'bitcoin-bank' ), '<b>[bcq_password]</b>' );
		$html_text = new Html_Text( $msg );
		$this->password_page_link->set_property( 'description', $html_text );

		$this->terms_page->set_property( 'label', esc_html__( 'Terms and condition page:', 'bitcoin-bank' ) );
		$this->terms_page->set_property( 'description', esc_html__( 'On this page you can write your terms and conditions, which new users must accept to register.', 'bitcoin-bank' ) );

		$this->logout_page->set_property( 'label', esc_html__( 'Logged out redirect link:', 'bitcoin-bank' ) );
		$this->logout_page->set_property( 'description', esc_html__( 'After user has logged out, he will be redirected to this page.', 'bitcoin-bank' ) );

        $this->login_redirect_page->set_property( 'label', esc_html__( 'Logged in redirect link:', 'bitcoin-bank' ) );
        $this->login_redirect_page->set_property( 'description', esc_html__( 'After user has logged in he will be redirected to this page.', 'bitcoin-bank' ) );

        $this->account_list_page->set_property( 'label', esc_html__( 'Account page:', 'bitcoin-bank' ) );

        $this->transaction_page->set_property( 'label', esc_html__( 'Transaction page:', 'bitcoin-bank' ) );

        $this->cheque_details_page->set_property( 'label', esc_html__( 'Cheque details page:', 'bitcoin-bank' ) );

        $this->send_cheque_page->set_property( 'label', esc_html__( 'Send cheque page:', 'bitcoin-bank' ) );

        $this->receive_cheque_page->set_property( 'label', esc_html__( 'Receive cheque page:', 'bitcoin-bank' ) );

		$this->add_form_input( 'register_page_link', $this->register_page_link );
		$this->add_form_input( 'login_page_link', $this->login_page_link );
		$this->add_form_input( 'profile_page_link', $this->profile_page_link );
		$this->add_form_input( 'password_page_link', $this->password_page_link );
		$this->add_form_input( 'terms_page', $this->terms_page );
		$this->add_form_input( 'logout_page', $this->logout_page );
        $this->add_form_input( 'login_redirect_page', $this->login_redirect_page );
        $this->add_form_input( 'account_list_page', $this->account_list_page );
        $this->add_form_input( 'transaction_page', $this->transaction_page );
        $this->add_form_input( 'cheque_details_page', $this->cheque_details_page );
        $this->add_form_input( 'send_cheque_page', $this->send_cheque_page );
        $this->add_form_input( 'receive_cheque_page', $this->receive_cheque_page );
		$this->add_button( 'std_submit', $this->std_submit );
		$this->std_submit->set_primary( true );

        parent::create_content( $parameters );
	}
}
