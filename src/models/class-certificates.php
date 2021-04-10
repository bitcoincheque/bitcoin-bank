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

use WP_PluginFramework\Models\Active_Record;

/**
 * Summary.
 *
 * Description.
 */
class Certificates extends Active_Record {

	const TABLE_NAME = 'bcq_bitcoin_bank_certificates';

    const CREATED_TIME = 'created_time';
    const EXPIRE_TIME = 'expire_time';
    const STATE = 'state';
    const ALGORITHM = 'algorithm';
    const BIT_LENGTH = 'bit_length';
    const PRIVATE_KEY = 'private_key';
    const PUBLIC_KEY = 'public_key';

    /* State values: */
    const STATE_INIT = 0;
    const STATE_OK = 10;
    const STATE_EXPIRED = 20;

    const MAX_KEY_LENGTH_ENCODED = 5000;

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
        self::CREATED_TIME => array(
            'data_type' => 'BCQ_BitcoinBank\Bank_Datetime_Type',
            'default_value' => null,
            'label' => 'Create time'
        ),
        self::EXPIRE_TIME => array(
            'data_type' => 'BCQ_BitcoinBank\Bank_Datetime_Type',
            'default_value' => null,
            'label' => 'Expire time'
        ),
        self::STATE => array(
            'data_type' => 'BCQ_BitcoinBank\Cheque_State_Type',
            'default_value' => self::STATE_INIT,
            'label' => 'State'
        ),
        self::ALGORITHM => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Algorithm'
        ),
        self::BIT_LENGTH => array(
            'data_type' => 'Integer_Type',
            'default_value' => '',
            'label' => 'Bit length'
        ),
        self::PRIVATE_KEY => array(
            'data_type' => 'String_Type',
            'data_size' => self::MAX_KEY_LENGTH_ENCODED,
            'default_value' => '',
            'label' => 'Private Key'
        ),
        self::PUBLIC_KEY => array(
            'data_type' => 'String_Type',
            'data_size' => self::MAX_KEY_LENGTH_ENCODED,
            'default_value' => '',
            'label' => 'Public Key'
        ),

	);
}
