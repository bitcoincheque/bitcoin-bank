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
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Hr;

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
		$this->uninstall_keep_db_data = new Check_Box(esc_html__( 'Keep the plugin\'s settings and data after uninstall.', 'bitcoin-bank' ));
		$this->enable_debug_log = new Check_Box(esc_html__( 'Switch on logging of debug info.', 'bitcoin-bank' ));
		$this->enable_extra_debug_logging = new Check_Box(esc_html__( 'Switch on extra debug info.', 'bitcoin-bank' ));
		if ( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True)) {
			$this->license_key = new Text_Line();
		}
		if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True)) {
			$this->download_rc_versions = new Check_Box(esc_html__( 'Download and install release candidates.', 'bitcoin-bank' ));
			$this->download_beta_versions = new Check_Box(esc_html__( 'Download and install beta test versions.', 'bitcoin-bank' ));
		}
		if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True)) {
			$this->download_dev_versions = new Check_Box(esc_html__( 'Download and install latest development versions.', 'bitcoin-bank' ));
		}
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
		$this->add_content(new H(2, esc_html__( 'Uninstall options', 'bitcoin-bank' )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Preserve plugin data:', 'bitcoin-bank' ),
				array( 'for' => 'uninstall_keep_db_data' ))
		);
		$cell = array(
			$this->uninstall_keep_db_data,
			new P(esc_html__( 'If you want to uninstall this plugin and reinstall it later, this will save the settings, status and statistics data. Registered users will not be affected by this setting, that data is preserved by WordPress\'s own database tables.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		$this->add_content(new Hr());
		/* translators: Admin panel sub-headline */
		$this->add_content(new H(2, esc_html__( 'Debug logging', 'bitcoin-bank' )));
		$log_file_url = '\wp-content\read_more_login_plugin.log';
		$this->add_content(new P(sprintf( esc_html__( 'Enable logging of debug information from this plugin. In case you have problem with the plugin, this can give helpful information to solve it. Log will be saved to the file %s.', 'bitcoin-bank' ), $log_file_url )));

		$grid = new Grid(null, array('class' => 'form-table'));

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Enable debug log:', 'bitcoin-bank' ),
				array( 'for' => 'enable_debug_log' ))
		);
		$cell = array(
			$this->enable_debug_log,
			new P(esc_html__( 'This will switch on error and warning messages.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$grid->add_row();
		$grid->add_cell_header(
			new Label( esc_html__( 'Log extra debug info:', 'bitcoin-bank' ),
				array( 'for' => 'enable_extra_debug_logging' ))
		);
		$cell = array(
			$this->enable_extra_debug_logging,
			new P(esc_html__( 'Note! Do not the enable extra debug logging continuously as the log will grow very large over time.', 'bitcoin-bank' ))
		);
		$grid->add_cell( $cell );

		$this->add_content($grid);

		$p_attributes = array('class' => 'wpf-table-placeholder submit');
		$p = new P($this->std_submit, $p_attributes);
		$this->add_content($p);

		if ( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True)) {
			$this->add_content( new Hr() );
			/* translators: Admin panel sub-headline */
			$this->add_content( new H( 2, esc_html__( 'Download licensed editions', 'bitcoin-bank' ) ) );
			$this->add_content( new P( esc_html__( 'Download and install licensed premium editions. The download will be available in the list of installed plugins.', 'bitcoin-bank' ) ) );

			$grid = new Grid( null, array( 'class' => 'form-table' ) );

			$grid->add_row();
			$grid->add_cell_header(
				new Label( esc_html__( 'License key:', 'bitcoin-bank' ),
					array( 'for' => 'license_key' ) )
			);
			$cell = array(
				$this->license_key,
				new P( esc_html__( 'License key to be provided by plugin developer.', 'bitcoin-bank' ) )
			);
			$grid->add_cell( $cell );

			$this->add_content( $grid );

			$p_attributes = array( 'class' => 'wpf-table-placeholder submit' );
			$p = new P( $this->std_submit, $p_attributes );
			$this->add_content( $p );
		}

		if (( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True))
			or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True))) {
			$this->add_content( new Hr() );
			/* translators: Admin panel sub-headline */
			$this->add_content( new H( 2, esc_html__( 'Download pre-release versions', 'bitcoin-bank' ) ) );
			$this->add_content( new P( esc_html__( 'Download and update of beta and release candidate versions ahead of public release. These versions are not recommended for production servers, but for testing ahead of final release. The download will be available in the list of installed plugins.', 'bitcoin-bank' ) ) );

			$grid = new Grid( null, array( 'class' => 'form-table' ) );

			if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True) ) {
				$grid->add_row();
				$grid->add_cell_header(
					new Label( esc_html__( 'Release candidates:', 'bitcoin-bank' ),
						array( 'for' => 'download_rc_versions' ) )
				);
				$cell = array(
					$this->download_rc_versions
				);
				$grid->add_cell( $cell );

				$grid->add_row();
				$grid->add_cell_header(
					new Label( esc_html__( 'Beta versions:', 'bitcoin-bank' ),
						array( 'for' => 'download_beta_versions' ) )
				);
				$cell = array(
					$this->download_beta_versions
				);
				$grid->add_cell( $cell );
			}

			if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True) ) {
				$grid->add_row();
				$grid->add_cell_header(
					new Label( esc_html__( 'Development version:', 'bitcoin-bank' ),
						array( 'for' => 'download_dev_versions' ) )
				);
				$cell = array(
					$this->download_dev_versions
				);
				$grid->add_cell( $cell );
			}

			$this->add_content( $grid );

			$p_attributes = array( 'class' => 'wpf-table-placeholder submit' );
			$p = new P( $this->std_submit, $p_attributes );
			$this->add_content( $p );
		}

		parent::create_content( $parameters );
	}
}
