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
class Settings_Advanced_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_advanced_options';

	const UNINSTALL_KEEP_DB_DATA     = 'uninstall_keep_db_data';
	const ENABLE_DEBUG_LOG           = 'enable_debug_log';
	const ENABLE_EXTRA_DEBUG_LOGGING = 'enable_extra_debug_logging';
	const LICENSE_KEY                = 'license_key';
	const DOWNLOAD_RC_VERSIONS       = 'download_rc_versions';
	const DOWNLOAD_BETA_VERSIONS     = 'download_beta_versions';
	const DOWNLOAD_DEV_VERSIONS      = 'download_dev_versions';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::UNINSTALL_KEEP_DB_DATA     => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
            'legend'        => 'Yes'
		),
		self::ENABLE_DEBUG_LOG           => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
            'legend'        => 'Yes'
		),
		self::ENABLE_EXTRA_DEBUG_LOGGING => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '0',
            'legend'        => 'Yes'
		),
		self::LICENSE_KEY => array(
			'data_type'     => 'String_Type',
			'default_value' => ''
		),
        self::DOWNLOAD_RC_VERSIONS => array(
            'data_type'     =>  'Boolean_Type',
            'default_value' => '0',
            'legend'        => 'Yes'
        ),
        self::DOWNLOAD_BETA_VERSIONS => array(
            'data_type'     =>  'Boolean_Type',
            'default_value' => '0',
            'legend'        => 'Yes'
		),
        self::DOWNLOAD_DEV_VERSIONS => array(
            'data_type'     =>  'Boolean_Type',
            'default_value' => '0',
            'legend'        => 'Yes'
		)
	);
}
