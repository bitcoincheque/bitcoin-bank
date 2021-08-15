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
		$this->bitcoin_cheque_reply_address = new Text_Line();
		$this->register_reply_address = new Text_Line();
		$this->register_subject = new Text_Line();
		$this->register_body = new Text_Box();
		$this->welcome_enable = new Check_Box(esc_html__( 'Yes', 'bitcoin-bank' ));
		$this->welcome_reply_address = new Text_Line();
		$this->welcome_subject = new Text_Line();
		$this->welcome_body = new Text_Box();
		$this->password_reply_address = new Text_Line();
		$this->password_subject = new Text_Line();
		$this->password_body = new Text_Box();
		$this->notification_enable = new Check_Box(esc_html__( 'Yes', 'bitcoin-bank' ));
		$this->notification_reply_address = new Text_Line();
		$this->notification_receiver = new Text_Line();
		$this->notification_subject = new Text_Line();
		$this->notification_body = new Text_Box();
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
		$this->add_content(new H(2, esc_html__( 'Bitcoin cheque e-mail address', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'Bitcoin Cheuqes are sent with this e-mail.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'From/reply:', 'bitcoin-bank' ),
				array( 'for' => 'bitcoin_cheque_reply_address' ))
		);
		$cell = array(
			$this->bitcoin_cheque_reply_address,
			new P(esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );
		$this->add_content( $grid );

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);

		$this->add_content($p);

		$this->add_content(new Hr());

		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Registering via forms put inside articles', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'This e-mail will be sent to visitors registering via the forms put inside articles.', 'bitcoin-bank' ) . ' ' .
			esc_html__( 'The e-mail asks the visitor to confirm his e-mail address by clicking a link.', 'bitcoin-bank' ) . ' ' .
			esc_html__( 'This link will send the new user right back to the article and let him read the remaining protected content.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Registering via the self-service registration page', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'This e-mail will be sent to visitors registering via the user handling page.', 'bitcoin-bank' ) . ' ' .
			esc_html__( 'The e-mail asks the visitor to confirm his e-mail address by clicking a link.', 'bitcoin-bank' ) . ' ' .
			esc_html__( 'This link will send the new user right back to the same user handling page to complete the registration.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'From/reply:', 'bitcoin-bank' ),
				array( 'for' => 'register_reply_address' ))
		);
		$cell = array(
			$this->register_reply_address,
			new P(esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Subject:', 'bitcoin-bank' ),
				array( 'for' => 'register_subject' ))
		);
		$grid->add_cell( $this->register_subject );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Message:', 'bitcoin-bank' ),
				array( 'for' => 'register_body' ))
		);
		$grid->add_cell( $this->register_body );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		$this->add_content(new Hr());
		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Welcome e-mail', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'This e-mail will be sent to user after user account has been approved.', 'bitcoin-bank' ) . ' ' .
			esc_html__( 'The link will automatically log in the user to show the remaining protected content.', 'bitcoin-bank' ) . ' ' .
			esc_html__( 'If user was registering using the self-service registration page, the user will be sent back to front page.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Send welcome e-mail:', 'bitcoin-bank' ),
				array( 'for' => 'welcome_enable' ))
		);
		$grid->add_cell( $this->welcome_enable );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'From/reply:', 'bitcoin-bank' ),
				array( 'for' => 'welcome_reply_address' ))
		);
		$cell = array(
			$this->welcome_reply_address,
			new P(esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Subject:', 'bitcoin-bank' ),
				array( 'for' => 'welcome_subject' ))
		);
		$grid->add_cell( $this->welcome_subject );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Message:', 'bitcoin-bank' ),
				array( 'for' => 'welcome_body' ))
		);
		$grid->add_cell( $this->welcome_body );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		$this->add_content(new Hr());
		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Reset password', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'This e-mail will be sent to user requesting a password reset link. This e-mail should also inform what the username is.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'From/reply:', 'bitcoin-bank' ),
				array( 'for' => 'password_reply_address' ))
		);
		$cell = array(
			$this->password_reply_address,
			new P(esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Subject:', 'bitcoin-bank' ),
				array( 'for' => 'password_subject' ))
		);
		$grid->add_cell( $this->password_subject );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Message:', 'bitcoin-bank' ),
				array( 'for' => 'password_body' ))
		);
		$grid->add_cell( $this->password_body );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		$this->add_content(new Hr());
		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Administrator notification', 'bitcoin-bank' )));
		$this->add_content(new P(esc_html__( 'Send e-mail to site admin or an other person when a new user has registered.', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Enable notification:', 'bitcoin-bank' ),
				array( 'for' => 'notification_enable' ))
		);
		$grid->add_cell( $this->notification_enable );


		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'From/reply:', 'bitcoin-bank' ),
				array( 'for' => 'notification_reply_address' ))
		);
		$cell = array(
			$this->notification_reply_address,
			new P(esc_html__( 'This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );
		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Send to:', 'bitcoin-bank' ),
				array( 'for' => 'notification_receiver' ))
		);
		$cell = array(
			$this->notification_receiver,
			new P(esc_html__( 'Send notification to this e-mail address. Optional field, leave blank to send to site admin.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Subject:', 'bitcoin-bank' ),
				array( 'for' => 'notification_subject' ))
		);
		$grid->add_cell( $this->notification_subject );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Message:', 'bitcoin-bank' ),
				array( 'for' => 'notification_body' ))
		);
		$grid->add_cell( $this->notification_body );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		parent::create_content( $parameters );
	}
}
