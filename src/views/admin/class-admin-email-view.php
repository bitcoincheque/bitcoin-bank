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
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Text_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Email_View extends Admin_Std_View {

    /** @var Text_Line */
    public $bitcoin_cheque_reply_address;
	/** @var Text_Line */
	public $register_reply_address;
	/** @var Text_Line */
	public $register_subject;
	/** @var Text_Line */
	public $register_body;
	/** @var Check_Box */
	public $welcome_enable;
	/** @var Text_Line */
	public $welcome_reply_address;
	/** @var Text_Line */
	public $welcome_subject;
	/** @var Text_Line */
	public $welcome_body;
	/** @var Text_Line */
	public $password_reply_address;
	/** @var Text_Line */
	public $password_subject;
	/** @var Text_Line */
	public $password_body;
	/** @var Check_Box */
	public $notification_enable;
	/** @var Text_Line */
	public $notification_receiver;
	/** @var Text_Line */
	public $notification_reply_address;
	/** @var Text_Line */
	public $notification_subject;
	/** @var Text_Line */
	public $notification_body;
	/** @var Push_Button */
	public $std_submit;

	/**
	 * Admin_Email_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

        $this->bitcoin_cheque_reply_address = new Text_Line();
        $this->register_component( 'bitcoin_cheque_reply_address', $this->bitcoin_cheque_reply_address );

		$this->register_reply_address = new Text_Line();
		$this->register_component( 'register_reply_address', $this->register_reply_address );

		$this->register_subject = new Text_Line();
		$this->register_component( 'register_subject', $this->register_subject );

		$this->register_body = new Text_Box();
		$this->register_component( 'register_body', $this->register_body );

		$this->welcome_enable = new Check_Box();
		$this->register_component( 'welcome_enable', $this->welcome_enable );

		$this->welcome_reply_address = new Text_Line();
		$this->register_component( 'welcome_reply_address', $this->welcome_reply_address );

		$this->welcome_subject = new Text_Line();
		$this->register_component( 'welcome_subject', $this->welcome_subject );

		$this->welcome_body = new Text_Box();
		$this->register_component( 'welcome_body', $this->welcome_body );

		$this->password_reply_address = new Text_Line();
		$this->register_component( 'password_reply_address', $this->password_reply_address );

		$this->password_subject = new Text_Line();
		$this->register_component( 'password_subject', $this->password_subject );

		$this->password_body = new Text_Box();
		$this->register_component( 'password_body', $this->password_body );

		$this->notification_enable = new Check_Box();
		$this->register_component( 'notification_enable', $this->notification_enable );

		$this->notification_reply_address = new Text_Line();
		$this->register_component( 'notification_reply_address', $this->notification_reply_address );

		$this->notification_receiver = new Text_Line();
		$this->register_component( 'notification_receiver', $this->notification_receiver );

		$this->notification_subject = new Text_Line();
		$this->register_component( 'notification_subject', $this->notification_subject );

		$this->notification_body = new Text_Box();
		$this->register_component('notification_body', $this->notification_body );

		/* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->register_component( 'std_submit', $this->std_submit );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
        $category = array(
            'name'        => 'bitcoin_cheque',
            'header'      => esc_html__( 'Bitcoin Cheque e-mail.', 'bitcoin-bank' )
        );
        $this->add_input_form_category( $category );

		$category = array(
			'name'        => 'verify_email',
			'header'      => esc_html__( 'Registering via the self-service registration page', 'bitcoin-bank' ),
			'description' => esc_html__( 'This e-mail will be sent to visitors registering via the user handling page.', 'bitcoin-bank' ) . ' ' .
				esc_html__( 'The e-mail asks the visitor to confirm his e-mail address by clicking a link.', 'bitcoin-bank' ) . ' ' .
				esc_html__( 'This link will send the new user right back to the same user handling page to complete the registration.', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

		$category = array(
			'name'        => 'welcome',
			'header'      => esc_html__( 'Welcome e-mail', 'bitcoin-bank' ),
			'description' => esc_html__( 'This e-mail will be sent to user after user account has been approved.', 'bitcoin-bank' ) . ' ' .
				esc_html__( 'The link will automatically log in the user to show the remaining protected content.', 'bitcoin-bank' ) . ' ' .
				esc_html__( 'If user was registering using the self-service registration page, the user will be sent back to front page.', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

		$category = array(
			'name'        => 'reset_password',
			'header'      => esc_html__( 'Reset password', 'bitcoin-bank' ),
			'description' => esc_html__( 'This e-mail will be sent to user requesting a password reset link. This e-mail should also inform what the username is.', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

		$category = array(
			'name'        => 'notification',
			'header'      => esc_html__( 'Administrator notification', 'bitcoin-bank' ),
			'description' => esc_html__( 'Send e-mail to site admin or an other person when a new user has registered.', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

        /* translators: E-mail from/reply address. */
        $this->bitcoin_cheque_reply_address->set_property( 'label', esc_html__( 'From/reply:', 'bitcoin-bank' ) );
        $this->bitcoin_cheque_reply_address->set_property( 'description', esc_html__( 'From/reply address for Bitcoin Cheques. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ) );
        $this->bitcoin_cheque_reply_address->set_property( 'category', 'bitcoin_cheque' );

		/* translators: E-mail from/reply address. */
		$this->register_reply_address->set_property( 'label', esc_html__( 'From/reply:', 'bitcoin-bank' ) );
		$this->register_reply_address->set_property( 'description', esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ) );
		$this->register_reply_address->set_property( 'category', 'verify_email' );

		/* translators: E-mail message subject. */
		$this->register_subject->set_property( 'label', esc_html__( 'Subject:', 'bitcoin-bank' ) );
		$this->register_subject->set_property( 'category', 'verify_email' );

		/* translators: E-mail message body. */
		$this->register_body->set_property( 'label', esc_html__( 'Message:', 'bitcoin-bank' ) );
		$this->register_body->set_property( 'category', 'verify_email' );

		$this->welcome_enable->set_property( 'label', esc_html__( 'Enable welcome e-mail:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Email_Options::WELCOME_ENABLE ] = esc_html__( 'Yes', 'bitcoin-bank' );
		$this->welcome_enable->set_property( 'items', $items );
		$this->welcome_enable->set_property( 'category', 'welcome' );

		/* translators: E-mail from/reply address. */
		$this->welcome_reply_address->set_property( 'label', esc_html__( 'From/reply:', 'bitcoin-bank' ) );
		$this->welcome_reply_address->set_property( 'description', esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ) );
		$this->welcome_reply_address->set_property( 'category', 'welcome' );

		/* translators: E-mail message subject. */
		$this->welcome_subject->set_property( 'label', esc_html__( 'Subject:', 'bitcoin-bank' ) );
		$this->welcome_subject->set_property( 'category', 'welcome' );

		/* translators: E-mail message body. */
		$this->welcome_body->set_property( 'label', esc_html__( 'Message:', 'bitcoin-bank' ) );
		$this->welcome_body->set_property( 'category', 'welcome' );

		/* translators: E-mail from/reply address. */
		$this->password_reply_address->set_property( 'label', esc_html__( 'From/reply:', 'bitcoin-bank' ) );
		$this->password_reply_address->set_property( 'description', esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ) );
		$this->password_reply_address->set_property( 'category', 'reset_password' );

		/* translators: E-mail message subject. */
		$this->password_subject->set_property( 'label', esc_html__( 'Subject:', 'bitcoin-bank' ) );
		$this->password_subject->set_property( 'category', 'reset_password' );

		/* translators: E-mail message body. */
		$this->password_body->set_property( 'label', esc_html__( 'Message:', 'bitcoin-bank' ) );
		$this->password_body->set_property( 'category', 'reset_password' );

		$this->notification_enable->set_property( 'label', esc_html__( 'Enable notification:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Email_Options::NOTIFICATION_ENABLE ] = esc_html__( 'Yes', 'bitcoin-bank' );
		$this->notification_enable->set_property( 'items', $items );
		$this->notification_enable->set_property( 'category', 'notification' );

		/* translators: E-mail from/reply address. */
		$this->notification_reply_address->set_property( 'label', esc_html__( 'From/reply:', 'bitcoin-bank' ) );
		$this->notification_reply_address->set_property( 'description', esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ) );
		$this->notification_reply_address->set_property( 'category', 'notification' );

		/* translators: E-mail send to address. */
		$this->notification_receiver->set_property( 'label', esc_html__( 'Send to:', 'bitcoin-bank' ) );
		$this->notification_receiver->set_property( 'description', esc_html__( 'Send notification to this e-mail address. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ) );
		$this->notification_receiver->set_property( 'category', 'notification' );

		/* translators: E-mail message subject. */
		$this->notification_subject->set_property( 'label', esc_html__( 'Subject:', 'bitcoin-bank' ) );
		$this->notification_subject->set_property( 'category', 'notification' );

		/* translators: E-mail message body. */
		$this->notification_body->set_property( 'label', esc_html__( 'Message:', 'bitcoin-bank' ) );
		$this->notification_body->set_property( 'category', 'notification' );

       $this->add_form_input( 'bitcoin_cheque_reply_address', $this->bitcoin_cheque_reply_address );
		$this->add_form_input( 'register_reply_address', $this->register_reply_address );
		$this->add_form_input( 'register_subject', $this->register_subject );
		$this->add_form_input( 'register_body', $this->register_body );
		$this->add_form_input( 'welcome_enable', $this->welcome_enable );
		$this->add_form_input( 'welcome_reply_address', $this->welcome_reply_address );
		$this->add_form_input( 'welcome_subject', $this->welcome_subject );
		$this->add_form_input( 'welcome_body', $this->welcome_body );
		$this->add_form_input( 'password_reply_address', $this->password_reply_address );
		$this->add_form_input( 'password_subject', $this->password_subject );
		$this->add_form_input( 'password_body', $this->password_body );
		$this->add_form_input( 'notification_enable', $this->notification_enable );
		$this->add_form_input( 'notification_reply_address', $this->notification_reply_address );
		$this->add_form_input( 'notification_receiver', $this->notification_receiver );
		$this->add_form_input( 'notification_subject', $this->notification_subject );
		$this->add_form_input('notification_body', $this->notification_body );
		$this->add_button( 'std_submit', $this->std_submit );
		$this->std_submit->set_primary( true );

        parent::create_content( $parameters );
	}
}
