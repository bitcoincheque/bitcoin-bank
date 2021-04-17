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

class Settings_Client_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_client_options';

	const SIGN_UP_CREDIT = 'sign_up_credit';
    const SIGN_UP_CREDIT_AMOUNT = 'sign_up_credit_amount';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::SIGN_UP_CREDIT => array(
            'data_type'     => 'Boolean_Type',
            'default_value' => '0',
            'label' => 'Sign-up credit',
            'legend' => 'Grant a credit to everybody signing up.'
		),
        self::SIGN_UP_CREDIT_AMOUNT => array(
            'data_type' => 'BCQ_BitcoinBank\Crypto_currency_type',
            'default_value' => 0,
            'decimals' => 8,
            'unit' => 'BTC',
            'label' => 'Credit amount'
        )
	);
}
