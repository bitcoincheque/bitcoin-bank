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

use WP_PluginFramework\Models\Option_Model;

class Settings_Account_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_account_options';

	const SHOW_CREDIT_ACCOUNTS_NEGATIVE = 'show_credit_account_negative';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::SHOW_CREDIT_ACCOUNTS_NEGATIVE => array(
            'data_type'     => 'Boolean_Type',
            'default_value' => '0',
            'label' => 'Show credit as negative',
            'legend' => 'Show credit type accounts as negative values. (Only in the admin panel.)'
		)
	);
}
