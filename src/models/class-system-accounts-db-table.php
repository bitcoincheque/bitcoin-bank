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

defined('ABSPATH') || exit;

use WP_PluginFramework\Models\Active_Record;


/**
 * Summary.
 *
 * Description.
 */
class System_Accounts_Db_Table extends Active_Record
{

    /* Database table name: */
    const TABLE_NAME = 'bcq_bitcoin_bank_system_accounts';

    /* List of table field names: */
    const SYSTEM_NAME = 'system_name';
    const LABEL = 'label';
    const ACCOUNT_ID = 'account_id';
    const DESCRIPTION = 'description';

    /* Metadata describing database fields and data properties: */
    static $meta_data = array(
        self::SYSTEM_NAME => array(
            'data_type' => 'String_Type',
            'default_value' => null,
            'label' => 'System Name'
        ),
        self::ACCOUNT_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Account_Id_Type',
            'default_value' => null,
            'label' => 'Account ID'
        ),
        self::LABEL => array(
            'data_type' => 'String_Type',
            'default_value' => null,
            'label' => 'Label'
        ),
        self::DESCRIPTION => array(
            'data_type' => 'String_Type',
            'default_value' => null,
            'label' => 'Description'
        ),
    );
}
