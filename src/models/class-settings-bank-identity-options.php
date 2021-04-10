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

class Settings_Bank_Identity_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_bank_identity';

	const BANK_NAME = 'bank_name';
    const MONEY_ACCOUNT_API_URL = 'money_account_api_url';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::BANK_NAME => array(
			'data_type'     => 'String_Type',
			'default_value' => '0',
            'label' => 'Bank name'
		),
        self::MONEY_ACCOUNT_API_URL => array(
            'data_type'     => 'String_Type',
            'default_value' => '0',
            'label' => 'Money Account API domain'
        ),
	);
}
