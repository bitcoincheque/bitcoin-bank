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

if ( ! class_exists( 'WP_PluginFramework\Plugin_Container' ) ) {
	include_once __DIR__ . '/../../vendor/wppluginframework/wp-plugin-framework/src/WP_PluginFramework/class-plugin-container.php';
}

use WP_PluginFramework;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Models\Version_Info;
use WP_PluginFramework\Database\Wp_Db_Interface;

/**
 * Summary.
 *
 * Description.
 */
class Bitcoin_Bank_Plugin extends WP_PluginFramework\Plugin_Container {

	public static $plugin_version;

	/**
	 * Construction.
	 */
	public function __construct() {
		/* Setting plugin file manually for quicker start-up */
		self::$plugin_base_file_path = BCQ_BITCOIN_BANK_PLUGIN_FILE;

		/* Need to specify namespace as we have different namespace on the plugin framework */
		self::$plugin_namespace = __NAMESPACE__;

        if(is_admin()) {
            self::$auto_loader_includes = array(
                'data_types',
                'include',
                'models',
                'pages/admin',
                'controllers/admin',
                'controllers/front-end',
                'views/admin',
                'views/front-end',
            );
        } else {
            self::$auto_loader_includes = array(
                'data_types',
                'include',
                'models',
                'pages/front-end',
                'controllers/rest-money-account-api/',
                'controllers/rest-teller-client-api/',
                'controllers/front-end',
                'views/front-end',
            );
        }

		parent::__construct();
	}

	/**
	 * @return Bitcoin_Bank_Plugin|WP_PluginFramework\Plugin_Container|null
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 *
	 */
	protected function includes() {
		 parent::includes();

		require_once 'class-compact-widget.php';
	}

	/**
	 *
	 */
	protected function init_hooks() {
		parent::init_hooks();

        add_action( 'init', array( 'BCQ_BitcoinBank\Bank_Pages', 'set_cheque_coockie' ) );

        add_action( 'bitcoin_bank_hourly_event', array( 'BCQ_BitcoinBank\Membership', 'schedule_event' ) );
        add_action( 'bitcoin_bank_hourly_event', array( 'BCQ_BitcoinBank\Cheque_Handler', 'schedule_event' ) );

        add_action('wp_ajax_nopriv_bcf_bitcoinbank_get_cheque_png', array( 'BCQ_BitcoinBank\Cheque_Download', 'output_cheque_picture_png_file' ));
        add_action('wp_ajax_bcf_bitcoinbank_get_cheque_png', array( 'BCQ_BitcoinBank\Cheque_Download', 'output_cheque_picture_png_file' ));

        add_action('wp_ajax_nopriv_bcf_bitcoinbank_download_cheque_png', array( 'BCQ_BitcoinBank\Cheque_Download', 'download_cheque_picture_png_file' ));
        add_action('wp_ajax_bcf_bitcoinbank_download_cheque_png', array( 'BCQ_BitcoinBank\Cheque_Download', 'download_cheque_picture_png_file' ));

        add_action('wp_ajax_nopriv_bcf_bitcoinbank_download_cheque_file', array( 'Cheque_Handler\Cheque_Download', 'download_cheque_file' ));
        add_action('wp_ajax_bcf_bitcoinbank_download_cheque_file', array( 'BCQ_BitcoinBank\Cheque_Download', 'download_cheque_file' ));

        add_action('wp_login', array( 'BCQ_BitcoinBank\Accounting', 'check_user_has_bank_account' ), 10, 2);

		add_filter( 'login_url', array( 'BCQ_BitcoinBank\Membership', 'redirect_login' ) );
		add_filter( 'logout_redirect', array( 'BCQ_BitcoinBank\Membership', 'redirect_logout' ) );
		add_filter( 'register_url', array( 'BCQ_BitcoinBank\Membership', 'redirect_register' ) );
		add_filter( 'lostpassword_url', array( 'BCQ_BitcoinBank\Membership', 'redirect_lost_password' ) );

        if(is_admin()) {
            add_action( 'admin_menu', array( 'BCQ_BitcoinBank\Admin_Pages', 'admin_menu' ) );
        } else {
            add_action('rest_api_init', array(new Rest_Money_Account_Api_Accounts, 'register_routes'));
            add_action('rest_api_init', array(new Rest_Money_Account_Api_Issued_Cheques, 'register_routes'));
            //add_action( 'rest_api_init', array( new Rest_Money_Account_Api_Info(), 'info' ) );
            add_action('rest_api_init', array(new Rest_Money_Account_Api_Public_Keys, 'register_routes'));
            add_action('rest_api_init', array(new Rest_Money_Account_Api_Transactions, 'register_routes'));

            add_action('rest_api_init', array(new Rest_Teller_Client_API_Accounts, 'register_routes'));
            add_action('rest_api_init', array(new Rest_Teller_Client_API_Cheques, 'register_routes'));
            add_action('rest_api_init', array(new Rest_Teller_Client_API_Transactions, 'register_routes'));

            add_shortcode( 'bcq_register', array( 'BCQ_BitcoinBank\Membership', 'registration_page' ) );
            add_shortcode( 'bcq_login', array( 'BCQ_BitcoinBank\Membership', 'membership_login' ) );
            add_shortcode( 'bcq_profile', array( 'BCQ_BitcoinBank\Membership', 'profile_page' ) );
            add_shortcode( 'bcq_reset_password', array( 'BCQ_BitcoinBank\Membership', 'password_page' ) );
            add_shortcode( 'bcq_password', array( 'BCQ_BitcoinBank\Membership', 'password_page' ) );
            add_shortcode( 'bcq_compact_login', array( 'BCQ_BitcoinBank\Compact_Widget', 'draw_compact_widget' ) ); // 'BCQ_BitcoinBank\bcq_compact_login_widget');
            add_shortcode( 'bcf_bitcoinbank_list_user_transactions', array( 'BCQ_BitcoinBank\Bank_Pages', 'transaction_list_page' ) );

            add_shortcode( 'bcq_client_home', array( 'BCQ_BitcoinBank\Bank_Pages', 'client_home' ) );
            add_shortcode( 'bcq_client_profile', array( 'BCQ_BitcoinBank\Bank_Pages', 'client_profile' ) );
            add_shortcode( 'bcq_account_list', array( 'BCQ_BitcoinBank\Bank_Pages', 'account_list' ) );
            add_shortcode( 'bcq_account_header', array( 'BCQ_BitcoinBank\Bank_Pages', 'account_header' ) );
            add_shortcode( 'bcq_account_deposit', array( 'BCQ_BitcoinBank\Bank_Pages', 'account_deposit' ) );
            add_shortcode( 'bcq_account_withdraw', array( 'BCQ_BitcoinBank\Bank_Pages', 'account_withdraw' ) );
            add_shortcode( 'bcq_transactions_list', array( 'BCQ_BitcoinBank\Bank_Pages', 'transactions_list' ) );
            add_shortcode( 'bcq_cheque_list', array( 'BCQ_BitcoinBank\Bank_Pages', 'cheque_list' ) );
            add_shortcode( 'bcq_cheque_details', array( 'BCQ_BitcoinBank\Bank_Pages', 'cheque_details' ) );
            add_shortcode( 'bcq_cheque_send', array( 'BCQ_BitcoinBank\Bank_Pages', 'cheque_send' ) );
            add_shortcode( 'bcq_cheque_receive', array( 'BCQ_BitcoinBank\Bank_Pages', 'cheque_receive' ) );
        }
	}

	/**
	 * @param array $plugin_data
	 */
	static function get_plugin_data( $plugin_data = array() ) {
		$plugin_data['Edition']     = BCQ_BITCOIN_BANK_EDITION_NAME;
		$plugin_data['EditionCode'] = BCQ_BITCOIN_BANK_EDITION_CODE;
		$plugin_data['EditionRev']  = BCQ_BITCOIN_BANK_EDITION_REV;
		parent::get_plugin_data( $plugin_data );
	}

	/**
	 * @return int|null
	 */
	public function get_debug_enable() {
		if ( ! isset( self::$debug_enable ) ) {
			$options            = get_option( Settings_Advanced_Options::OPTION_NAME );
			self::$debug_enable = $options[ Settings_Advanced_Options::ENABLE_DEBUG_LOG ];
		}
		return self::$debug_enable;
	}

	/**
	 * @return int|null
	 */
	public function get_debug_level() {
		if ( ! isset( self::$debug_level ) ) {
			self::$debug_level = Debug_Logger::WARNING;
			$options           = get_option( Settings_Advanced_Options::OPTION_NAME );
			if ( $options[ Settings_Advanced_Options::ENABLE_EXTRA_DEBUG_LOGGING ] ) {
				self::$debug_level = Debug_Logger::NOTE;
			}
		}
		return self::$debug_level;
	}

	/**
	 *
	 */
	public static function activate_plugin() {
		parent::activate_plugin();

		if ( ! wp_next_scheduled( 'bitcoin_bank_hourly_event' ) ) {
			$result = wp_schedule_event( time(), 'hourly', 'bitcoin_bank_hourly_event' );

			/* WordPress before 5.1.0 return null for success. */
			if ( ( true !== $result ) && ( null !== $result ) ) {
				Debug_Logger::write_debug_error( 'Cron job bitcoin_bank_hourly_event did not start.' );
			}
		} else {
			Debug_Logger::write_debug_warning( 'Cron job bitcoin_bank_hourly_event was not disabled during last plugin deactivation.' );
		}
    }

	/**
	 *
	 */
	public static function deactivate_plugin() {
		if ( ! wp_next_scheduled( 'bitcoin_bank_hourly_event' ) ) {
			Debug_Logger::write_debug_error( 'Cron job bitcoin_bank_hourly_event was not running.' );
		}

		wp_clear_scheduled_hook( 'bitcoin_bank_hourly_event' );

		parent::deactivate_plugin();
	}

    public static function install_plugin() {
	    parent::install_plugin();
        Bank_Installer::install_bank();
    }

	/**
	 * @param $previous_plugin_version_data
	 */
	public static function check_upgrade( $previous_plugin_version_data, $check_reinstalled = false ) {
		if ( ( true === $check_reinstalled ) || ( false === $previous_plugin_version_data ) ) {
			static::install_plugin();

			/* First version did not set version option */
			$previous_plugin_version_data = Plugin_Upgrade::get_previous_version_data();
			if ( false !== $previous_plugin_version_data ) {
				static::upgrade( $previous_plugin_version_data );
			}
		} else {
			$my_plugin_version_data = static::get_plugin_version_data();
			if ( $my_plugin_version_data !== $previous_plugin_version_data ) {
				static::upgrade( $previous_plugin_version_data );
			}
		}
	}

	/**
	 * @param $previous_plugin_version_data
	 */
	public static function upgrade( $previous_plugin_version_data ) {
		parent::upgrade( $previous_plugin_version_data );

		$plugin_slug         = self::get_plugin_slug();
		$plugin_version_data = parent::get_plugin_version_data();

		Plugin_Upgrade::upgrade( $plugin_slug, $plugin_version_data, $previous_plugin_version_data );
	}

	/**
	 *
	 */
	static function uninstall_plugin() {
		$plugin_version_name = parent::get_plugin_version_name();
		$plugin_version_data = parent::get_plugin_version();
		$plugin_slug         = self::get_plugin_slug();

		$advanced_options = get_option( Settings_Advanced_Options::OPTION_NAME );

		if ( $advanced_options[ Settings_Advanced_Options::UNINSTALL_KEEP_DB_DATA ] ) {
			Debug_Logger::write_debug_note( 'Uninstalling ' . $plugin_slug . ' version ' . $plugin_version_name . '.' );

			self::upgrade_version_info( $plugin_version_data, Version_Info::PLUGIN_INSTALL_STATE_UNINSTALLED );
		} else {
			parent::uninstall_plugin();

			$debug_log_path = get_home_path() . 'wp-content/bitcoinbank_plugin.log';
			if ( file_exists( $debug_log_path ) ) {
				unlink( $debug_log_path );
			}

			/* Delete any version 1 options and data. Previous version did not uninstall all data. */
			delete_option( 'bcq_bitcoinbank_textfading_option' );
			delete_option( 'bcq_bitcoinbank_text_fade_option' );
			delete_option( 'bcq_bitcoinbank_searchengines_option' );
			delete_option( 'bcq_bitcoinbank_debuglog_option' );
			delete_option( 'bcq_bitcoinbank_linking_option' );
			delete_option( 'bcq_bitcoinbank_redirect_option' );
			delete_option( 'bcq_bitcoinbank_email_verification_option' );
			delete_option( 'bcq_bitcoinbank_email_reset_password_option' );
			delete_option( 'bcq_bitcoinbank_email_register_notification_option' );

			$database = new Wp_Db_Interface();
			if ( $database->table_exist( 'bcq_bitcoinbank_registration' ) ) {
				$database->remove_table( 'bcq_bitcoinbank_registration' );
			}
			if ( $database->table_exist( 'bcq_bitcoinbank_statistics' ) ) {
				$database->remove_table( 'bcq_bitcoinbank_statistics' );
			}
		}
	}
}
