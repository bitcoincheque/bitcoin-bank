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

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Views\Admin_Std_View;
use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Text_Box;
use WP_PluginFramework\HtmlComponents\Text_Line;

class Admin_Advanced_View extends Admin_Std_View {

	/** @var Check_Box */
	public $uninstall_keep_db_data;
	/** @var Check_Box */
	public $enable_debug_log;
	/** @var Check_Box */
	public $enable_extra_debug_logging;
	/** @var Text_Line */
	public $license_key;
	/** @var Check_Box */
	public $download_rc_versions;
	/** @var Check_Box */
	public $download_beta_versions;
	/** @var Check_Box */
	public $download_dev_versions;
	/** @var Push_Button */
	public $std_submit;

	/**
	 * Admin_Advanced_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$this->uninstall_keep_db_data = new Check_Box();
		$this->register_component( 'uninstall_keep_db_data', $this->uninstall_keep_db_data );

		$this->enable_debug_log = new Check_Box();
		$this->register_component( 'enable_debug_log', $this->enable_debug_log );

		$this->enable_extra_debug_logging = new Check_Box();
		$this->register_component( 'enable_extra_debug_logging', $this->enable_extra_debug_logging );

		if ( defined( 'BITCOIN_BANK_ENTER_LICENSE_EDITION' ) and (BITCOIN_BANK_ENTER_LICENSE_EDITION === True)) {
			$this->license_key = new Text_Line();
			$this->register_component( 'license_key', $this->license_key );
		}

		if ( defined( 'BITCOIN_BANK_DOWNLOAD_PRE_RELEASE' ) and (BITCOIN_BANK_DOWNLOAD_PRE_RELEASE === True)) {
			$this->download_rc_versions = new Check_Box();
			$this->register_component( 'download_rc_versions', $this->download_rc_versions );

			$this->download_beta_versions = new Check_Box();
			$this->register_component( 'download_beta_versions', $this->download_beta_versions );
		}

		if ( defined( 'BITCOIN_BANK_DOWNLOAD_DEV_VERSION' ) and (BITCOIN_BANK_DOWNLOAD_DEV_VERSION === True)) {
			$this->download_dev_versions = new Check_Box();
			$this->register_component( 'download_dev_versions', $this->download_dev_versions );
		}

		/* translators: Button label */
		$this->std_submit = new Push_Button( esc_html__( 'Save changes', 'bitcoin-bank' ) );
		$this->register_component( 'std_submit', $this->std_submit );
	}

	/**
	 * @param null $parameters
	 */
	public function create_content( $parameters = null ) {
		$category = array(
			'name'   => 'uninstall',
			'header' => esc_html__( 'Uninstall options', 'bitcoin-bank' ),
		);
		$this->add_input_form_category( $category );

        $plugin = Plugin_Container::instance();
        $plugin_slug = $plugin->get_plugin_slug();
		$log_file_url = '\wp-content\\' . $plugin_slug .'_plugin.log';
		$category     = array(
			'name'        => 'debug',
			'header'      => esc_html__( 'Debug logging', 'bitcoin-bank' ),
			/* translators: %s: Path and filename to log file. */
			'description' => sprintf( esc_html__( 'Enable logging of debug information from this plugin. In case you have problem with the plugin, this can give helpful information to solve it. Log will be saved to the file %s.', 'bitcoin-bank' ), $log_file_url ),
		);
		$this->add_input_form_category( $category );

		if ( defined( 'BITCOIN_BANK_ENTER_LICENSE_EDITION' ) and (BITCOIN_BANK_ENTER_LICENSE_EDITION === True)) {
			$category = array(
				'name'        => 'license',
				'header'      => esc_html__( 'Download licensed editions', 'bitcoin-bank' ),
				/* translators: %s: Path and filename to log file. */
				'description' => esc_html__( 'Download and install licensed premium editions. The download will be available in the list of installed plugins.', 'bitcoin-bank' )
			);
			$this->add_input_form_category( $category );
		}

		if (( defined( 'BITCOIN_BANK_DOWNLOAD_PRE_RELEASE' ) and (BITCOIN_BANK_DOWNLOAD_PRE_RELEASE === True))
		    or( defined( 'BITCOIN_BANK_DOWNLOAD_DEV_VERSION' ) and (BITCOIN_BANK_DOWNLOAD_DEV_VERSION === True))) {
			$category = array(
				'name'        => 'testing',
				'header'      => esc_html__( 'Download pre-release versions', 'bitcoin-bank' ),
				/* translators: %s: Path and filename to log file. */
			    'description' => esc_html__( 'Download and update of beta and release candidate versions ahead of public release. These versions are not recommended for production servers, but for testing ahead of final release. The download will be available in the list of installed plugins.', 'bitcoin-bank' )
			);
			$this->add_input_form_category( $category );
		}

		$this->uninstall_keep_db_data->set_property( 'label', esc_html__( 'Preserve plugin data:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Advanced_Options::UNINSTALL_KEEP_DB_DATA ] = esc_html__( 'Keep the plugin\'s settings and data after uninstall.', 'bitcoin-bank' );
		$this->uninstall_keep_db_data->set_property( 'items', $items );
		$this->uninstall_keep_db_data->set_property( 'description', esc_html__( 'If you want to uninstall this plugin and reinstall it later, this will save the settings, status and statistics data. Registered users will not be affected by this setting, that data is preserved by WordPress\'s own database tables.', 'bitcoin-bank' ) );
		$this->uninstall_keep_db_data->set_property( 'category', 'uninstall' );

		$this->enable_debug_log->set_property( 'label', esc_html__( 'Enable debug log:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Advanced_Options::ENABLE_DEBUG_LOG ] = esc_html__( 'Switch on logging of debug info.', 'bitcoin-bank' );
		$this->enable_debug_log->set_property( 'items', $items );
		$this->enable_debug_log->set_property( 'description', esc_html__( 'This will switch on error and warning messages.', 'bitcoin-bank' ) );
		$this->enable_debug_log->set_property( 'category', 'debug' );

		$this->enable_extra_debug_logging->set_property( 'label', esc_html__( 'Log extra debug info:', 'bitcoin-bank' ) );
		$items = array();
		$items[ Settings_Advanced_Options::ENABLE_EXTRA_DEBUG_LOGGING ] = esc_html__( 'Switch on extra debug info.', 'bitcoin-bank' );
		$this->enable_extra_debug_logging->set_property( 'items', $items );
		$this->enable_extra_debug_logging->set_property( 'description', esc_html__( 'Note! Do not the enable extra debug logging continuously as the log will grow very large over time.', 'bitcoin-bank' ) );
		$this->enable_extra_debug_logging->set_property( 'category', 'debug' );

		if ( defined( 'BITCOIN_BANK_ENTER_LICENSE_EDITION' ) and ( BITCOIN_BANK_ENTER_LICENSE_EDITION === true ) ) {
			$this->license_key->set_property('label', esc_html__('License key:', 'bitcoin-bank'));
			$this->license_key->set_property('description', esc_html__('License key to be provided by plugin developer. Go to www.bitcoincheque.org', 'bitcoin-bank'));
			$this->license_key->set_property('category', 'license');
		}

		if (( defined( 'BITCOIN_BANK_DOWNLOAD_PRE_RELEASE' ) and (BITCOIN_BANK_DOWNLOAD_PRE_RELEASE === True))
		    or( defined( 'BITCOIN_BANK_DOWNLOAD_DEV_VERSION' ) and (BITCOIN_BANK_DOWNLOAD_DEV_VERSION === True))) {
			if ( defined( 'BITCOIN_BANK_DOWNLOAD_PRE_RELEASE' ) and ( BITCOIN_BANK_DOWNLOAD_PRE_RELEASE === true ) ) {
				$this->download_rc_versions->set_property( 'label', esc_html__( 'Release candidates:', 'bitcoin-bank' ) );
				$items = array();
				$items[ Settings_Advanced_Options::DOWNLOAD_RC_VERSIONS ] = esc_html__( 'Download and install release candidates.', 'bitcoin-bank' );
				$this->download_rc_versions->set_property( 'Items', $items );
				$this->download_rc_versions->set_property( 'category', 'testing' );

				$this->download_beta_versions->set_property( 'label', esc_html__( 'Beta versions:', 'bitcoin-bank' ) );
				$items                                                      = array();
				$items[ Settings_Advanced_Options::DOWNLOAD_BETA_VERSIONS ] = esc_html__( 'Download and install beta test versions.', 'bitcoin-bank' );
				$this->download_beta_versions->set_property( 'Items', $items );
				$this->download_beta_versions->set_property( 'category', 'testing' );
			}

			if ( defined( 'BITCOIN_BANK_DOWNLOAD_DEV_VERSION' ) and ( BITCOIN_BANK_DOWNLOAD_DEV_VERSION === true ) ) {
				$this->download_dev_versions->set_property( 'label', esc_html__( 'Development version:', 'bitcoin-bank' ) );
				$items = array();
				$items[ Settings_Advanced_Options::DOWNLOAD_DEV_VERSIONS ] = esc_html__( 'Download and install latest development versions.', 'bitcoin-bank' );
				$this->download_dev_versions->set_property( 'Items', $items );
				$this->download_dev_versions->set_property( 'category', 'testing' );
			}
		}

		$this->add_form_input( 'uninstall_keep_db_data', $this->uninstall_keep_db_data );

		$this->add_form_input( 'enable_debug_log', $this->enable_debug_log );

		$this->add_form_input( 'enable_extra_debug_logging', $this->enable_extra_debug_logging );

		if ( defined( 'BITCOIN_BANK_ENTER_LICENSE_EDITION' ) and ( BITCOIN_BANK_ENTER_LICENSE_EDITION === true ) ) {
			$this->add_form_input( 'license_key', $this->license_key );
		}

		if ( defined( 'BITCOIN_BANK_DOWNLOAD_PRE_RELEASE' ) and ( BITCOIN_BANK_DOWNLOAD_PRE_RELEASE === true ) ) {
			$this->add_form_input( 'download_rc_versions', $this->download_rc_versions );
			$this->add_form_input( 'download_beta_versions', $this->download_beta_versions );
		}

		if ( defined( 'BITCOIN_BANK_DOWNLOAD_DEV_VERSION' ) and ( BITCOIN_BANK_DOWNLOAD_DEV_VERSION === true ) ) {
			$this->add_form_input( 'download_dev_versions', $this->download_dev_versions );
		}

		$this->add_button( 'std_submit', $this->std_submit );

		$this->std_submit->set_primary( true );

		parent::create_content( $parameters );
	}
}
