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

/**
 * Summary.
 *
 * Description.
 */
class Settings_Email_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_email_options';

    const BITCOIN_CHEQUE_REPLY_ADDRESS = 'bitcoin_cheque_reply_address';

	const REGISTER_VERIFY_EMAILS  = 'register_verify_emails';
	const REGISTER_REPLAY_ADDRESS = 'register_reply_address';
	const REGISTER_SUBJECT        = 'register_subject';
	const REGISTER_BODY           = 'register_body';

	const WELCOME_ENABLE         = 'welcome_enable';
	const WELCOME_REPLAY_ADDRESS = 'welcome_reply_address';
	const WELCOME_SUBJECT        = 'welcome_subject';
	const WELCOME_BODY           = 'welcome_body';

	const PASSWORD_REPLAY_ADDRESS = 'password_reply_address';
	const PASSWORD_SUBJECT        = 'password_subject';
	const PASSWORD_BODY           = 'password_body';

	const NOTIFICATION_ENABLE         = 'notification_enable';
	const NOTIFICATION_SEND_TO        = 'notification_receiver';
	const NOTIFICATION_REPLAY_ADDRESS = 'notification_reply_address';
	const NOTIFICATION_SUBJECT        = 'notification_subject';
	const NOTIFICATION_BODY           = 'notification_body';


	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
        self::BITCOIN_CHEQUE_REPLY_ADDRESS => array(
            'data_type'     => 'String_Type',
            'default_value' => '',
        ),
		self::REGISTER_REPLAY_ADDRESS         => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::REGISTER_SUBJECT                => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::REGISTER_BODY                   => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::WELCOME_ENABLE                  => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::WELCOME_REPLAY_ADDRESS          => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::WELCOME_SUBJECT                 => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::WELCOME_BODY                    => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PASSWORD_REPLAY_ADDRESS         => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PASSWORD_SUBJECT                => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PASSWORD_BODY                   => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::NOTIFICATION_ENABLE             => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::NOTIFICATION_SEND_TO            => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::NOTIFICATION_REPLAY_ADDRESS     => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::NOTIFICATION_SUBJECT            => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::NOTIFICATION_BODY               => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
	);

	/**
	 * @return array
	 */
	public function get_meta_data_list( $columns = array() ) {
		$metadata = static::$meta_data;

		/* translators: Text sent in registration e-mail, subject line. %s: Name of site. */
		$metadata[ self::REGISTER_SUBJECT ]['default_value'] = sprintf( esc_html__( 'Registration at %s', 'bitcoin-bank' ), '%site_name%' );
		/* translators: Text sent in registration e-mail, message body. %s: Name of site. */
		$metadata[ self::REGISTER_BODY ]['default_value'] = '<p>' . sprintf( esc_html__( 'Follow this link to complete your registration at %s.', 'bitcoin-bank' ), '<strong>%site_name%</strong>' ) . '</p>&#13;&#10;';
		/* translators: Text sent in registration e-mail, message body. */
		$metadata[ self::REGISTER_BODY ]['default_value'] .= '<p>' . esc_html__( 'Click or copy the link into your web browser:', 'bitcoin-bank' ) . '</p>&#13;&#10;<p>%link%</p>';

		/* translators: Text sent in registration e-mail, subject line. %s: Name of site. */
		$metadata[ self::WELCOME_SUBJECT ]['default_value'] = sprintf( esc_html__( 'Welcome to %s', 'bitcoin-bank' ), '%site_name%' );
		/* translators: Text sent in registration e-mail, message body. %s: Name of site. */
		$metadata[ self::WELCOME_BODY ]['default_value'] = '<p>' . sprintf( esc_html__( 'Your user account has now been approved at %s.', 'bitcoin-bank' ), '<strong>%site_name%</strong>' ) . '</p>&#13;&#10;';
		/* translators: Text sent in registration e-mail, message body. */
		$metadata[ self::WELCOME_BODY ]['default_value'] .= '<p>' . esc_html__( 'Click or copy the link into your web browser to continue reading:', 'bitcoin-bank' ) . '</p>&#13;&#10;<p>%link%</p>';

		/* translators: Text sent in password reset e-mail, subject line. */
		$metadata[ self::PASSWORD_SUBJECT ]['default_value'] = esc_html__( 'Recover username and reset password', 'bitcoin-bank' );
		/* translators: Text sent in password reset e-mail, message body. %s: Name of site. */
		$metadata[ self::PASSWORD_BODY ]['default_value'] .= '<p>' . sprintf( esc_html__( 'You have requested to recover your username or reset your password at %s.', 'bitcoin-bank' ), '<strong>%site_name%</strong>' ) . '</p>&#13;&#10;';
		/* translators: Text sent in password reset e-mail, message body. %s: Username. */
		$metadata[ self::PASSWORD_BODY ]['default_value'] .= '<p>' . sprintf( esc_html__( 'Your username is %s', 'bitcoin-bank' ), '<strong>%username%</strong>' ) . '</p>&#13;&#10;';
		/* translators: Text sent in password reset e-mail, message body. */
		$metadata[ self::PASSWORD_BODY ]['default_value'] .= '<p>' . esc_html__( 'Use this link to reset your password:', 'bitcoin-bank' ) . '</p>&#13;&#10;';
		$metadata[ self::PASSWORD_BODY ]['default_value'] .= '<p>%link%</p>&#13;&#10;';
		/* translators: Text sent in password reset e-mail, message body. */
		$metadata[ self::PASSWORD_BODY ]['default_value'] .= '<p>' . esc_html__( 'Please note the link can only be used once and will expire after 24 hours.', 'bitcoin-bank' ) . '</p>';

		/* translators: Text sent in admin notification e-mail, subject line. */
		$metadata[ self::NOTIFICATION_SUBJECT ]['default_value'] = esc_html__( 'Notification of new user registration', 'bitcoin-bank' );
		/* translators: Text sent in admin notification e-mail, message body. %s: Name of site. */
		$metadata[ self::NOTIFICATION_BODY ]['default_value'] = '<p>' . sprintf( esc_html__( 'A new member has registered at %s.', 'bitcoin-bank' ), '<strong>%site_name%</strong>' ) . '</p>&#13;&#10;';
		/* translators: Text sent in admin notification e-mail, message body. %s: Username. */
		$metadata[ self::NOTIFICATION_BODY ]['default_value'] .= '<p>' . sprintf( esc_html__( 'Username: %s', 'bitcoin-bank' ), '%username%' ) . '</p>';

		return $metadata;
	}
}
