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

use WP_PluginFramework\Models\Option_Model;

class Settings_Form_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_form_options';

	const LOGIN_HEADER_TEXT           = 'login_header_text';
	const LOGIN_MESSAGE_TEXT          = 'login_message_text';
	const LOGIN_SHOW_LOGIN_FORM       = 'login_show_login_form';
	const LOGIN_SHOW_REGISTRATION     = 'login_show_registration'; /* New from 2.1 */
	const LOGIN_SHOW_REGISTER_BTN     = 'login_show_register_btn'; /* Obsolete from 2.1, keep it for a while. */
	const LOGIN_SHOW_REGISTER_AS_LINK = 'login_show_register_as_link'; /* Obsolete from 2.1, keep it for a while. */
	const LOGIN_SHOW_FORGOTTEN_LINK   = 'login_show_forgotten_link';

	/* Values for LOGIN_SHOW_REGISTRATION */
	const SHOW_REG_VALUE_OFF          = 'show_reg_value_off';
	const SHOW_REG_VALUE_FORM_BUTTON  = 'show_reg_value_form_button';
	const SHOW_REG_VALUE_PAGE_BUTTON  = 'show_reg_value_page_button';
	const SHOW_REG_VALUE_PAGE_LINK    = 'show_reg_value_page_link';

	const REGISTER_HEADER_TEXT         = 'register_header_text';
	const REGISTER_MESSAGE_TEXT        = 'register_message_text';
	const REGISTER_COLLECT_FIRST_NAME  = 'register_collect_first_name';
	const REGISTER_COLLECT_LAST_NAME   = 'register_collect_last_name';
	const REGISTER_FIRST_NAME_REQUIRED = 'register_first_name_required';
	const REGISTER_LAST_NAME_REQUIRED  = 'register_last_name_required';
	const REGISTER_MUST_ACCEPT_TERMS   = 'register_must_accept_terms';

	const VERIFY_EMAILS                 = 'verify_emails';
	const CHECK_EMAIL_HEADER_TEXT       = 'check_email_header_text';
	const CHECK_EMAIL_MESSAGE_TEXT      = 'check_email_message_text';

	const PENDING_APPROVAL_HEADER_TEXT  = 'pending_approval_header_text';
	const PENDING_APPROVAL_MESSAGE_TEXT = 'pending_approval_message_text';

	const SHOW_NOTIFICATION_BAR         = 'show_notification_bar';
	const NOTIFICATION_AT_VERIFICATION  = 'notification_bar_at_verification';
	const STICKY_NOTIFICATION_BAR       = 'sticky_notification_bar';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::LOGIN_HEADER_TEXT             => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::LOGIN_MESSAGE_TEXT            => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::LOGIN_SHOW_LOGIN_FORM         => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::LOGIN_SHOW_REGISTRATION => array(
			'data_type'     => 'String_Type',
			'default_value' => self::SHOW_REG_VALUE_FORM_BUTTON,
		),
		self::LOGIN_SHOW_REGISTER_BTN       => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::LOGIN_SHOW_REGISTER_AS_LINK   => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::LOGIN_SHOW_FORGOTTEN_LINK     => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::REGISTER_HEADER_TEXT          => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::REGISTER_MESSAGE_TEXT         => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::REGISTER_COLLECT_FIRST_NAME   => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::REGISTER_FIRST_NAME_REQUIRED  => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::REGISTER_COLLECT_LAST_NAME    => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::REGISTER_LAST_NAME_REQUIRED   => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
		),
		self::REGISTER_MUST_ACCEPT_TERMS    => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::VERIFY_EMAILS                 => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::CHECK_EMAIL_HEADER_TEXT       => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::CHECK_EMAIL_MESSAGE_TEXT      => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PENDING_APPROVAL_HEADER_TEXT  => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PENDING_APPROVAL_MESSAGE_TEXT => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::SHOW_NOTIFICATION_BAR         => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::NOTIFICATION_AT_VERIFICATION  => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::STICKY_NOTIFICATION_BAR       => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
	);

	/**
	 * @return array
	 */
	public function get_meta_data_list( $columns = array() ) {
		$metadata = static::$meta_data;

		/* translators: Headline text. */
		$metadata[ self::LOGIN_HEADER_TEXT ]['default_value']  = esc_html__( 'Read more', 'bitcoin-bank' );
		$metadata[ self::LOGIN_MESSAGE_TEXT ]['default_value'] = esc_html__( 'You must log in to read the rest of this article. Please log in or register as a user.', 'bitcoin-bank' );

		$metadata[ self::REGISTER_HEADER_TEXT ]['default_value']  = esc_html__( 'Register to read more', 'bitcoin-bank' );
		$metadata[ self::REGISTER_MESSAGE_TEXT ]['default_value'] = esc_html__( 'Enter your e-mail address to register.', 'bitcoin-bank' );

		$metadata[ self::CHECK_EMAIL_HEADER_TEXT ]['default_value'] = esc_html__( 'Check your e-mail', 'bitcoin-bank' );

		/* translators: %s: E-mail address. */
		$metadata[ self::CHECK_EMAIL_MESSAGE_TEXT ]['default_value'] = sprintf( esc_html__( 'An e-mail has been sent to %s.', 'bitcoin-bank' ), '<b>%email%</b>' );

		/* translators: %s: Title of article to read. */
		$metadata[ self::CHECK_EMAIL_MESSAGE_TEXT ]['default_value'] .= '<br><br>' . sprintf( esc_html__( 'Please check your e-mail. You will receive a message with a link to read the remaining article %s.', 'bitcoin-bank' ), '<b>%title%</b>' );

		$metadata[ self::PENDING_APPROVAL_HEADER_TEXT ]['default_value'] = esc_html__( 'Approval pending', 'bitcoin-bank' );

		/* translators: %s: E-mail address. */
		$metadata[ self::PENDING_APPROVAL_MESSAGE_TEXT ]['default_value'] = esc_html__( 'Please wait for your user account to be approved. This may take some time.', 'bitcoin-bank' );

		/* translators: %s: Title of article to read. */
		$metadata[ self::PENDING_APPROVAL_MESSAGE_TEXT ]['default_value'] .= '<br><br>' . esc_html__( 'You will receive an e-mail when your user account has been approved.', 'bitcoin-bank' );

		return $metadata;
	}
}
