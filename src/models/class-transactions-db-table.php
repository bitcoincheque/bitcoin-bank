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

defined('ABSPATH') || exit;

use WP_PluginFramework\Models\Active_Record;


/**
 * Summary.
 *
 * Description.
 */
class Transactions_Db_Table extends Active_Record
{

    /* Database table name: */
    const TABLE_NAME = 'bcq_bitcoin_bank_transactions';

    /* List of table field names: */
    const TIME_STAMP = 'time_stamp';
    const DEBIT_ACCOUNT_ID = 'debit_account_id';
    const CREDIT_ACCOUNT_ID = 'credit_account_id';
    const AMOUNT = 'amount';
    const TRANSACTION_TYPE = 'transaction_type';
    const REFERENCE_ID = 'reference_id';

    /* Transaction types: */
    const TRANSACTION_TYPE_NOT_SET = 0;
    const TRANSACTION_TYPE_CHEQUE_DRAW = 1;
    const TRANSACTION_TYPE_CHEQUE_EXPIRE = 2;
    const TRANSACTION_TYPE_CHEQUE_RECEIVE = 3;
    const TRANSACTION_TYPE_CHEQUE_FEE = 4;
    const TRANSACTION_TYPE_ACCOUNT_TRANSFER = 5;
    const TRANSACTION_TYPE_CHEQUE_REVERSED2 = 6;
    const TRANSACTION_TYPE_CHEQUE_REJECT = 7;

    /* Metadata describing database fields and data properties: */
    static $meta_data = array(
        self::TIME_STAMP => array(
            'data_type' => 'BCQ_BitcoinBank\Bank_Datetime_Type',
            'default_value' => null,
            'label' => 'Date & time'
        ),
        self::DEBIT_ACCOUNT_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Account_Id_Type',
            'default_value' => null,
            'label' => 'Debit Account'
        ),
        self::CREDIT_ACCOUNT_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Account_Id_Type',
            'default_value' => null,
            'label' => 'Credit Account'
        ),
        self::AMOUNT => array(
            'data_type' => 'BCQ_BitcoinBank\Crypto_currency_type',
            'default_value' => 0,
            'label' => 'Amount',
            'transaction_data' => true
        ),
		self::TRANSACTION_TYPE => array(
			'data_type' => 'BCQ_BitcoinBank\Transaction_Type',
			'default_value' => self::TRANSACTION_TYPE_NOT_SET,
            'label' => 'Description'
		),
        self::REFERENCE_ID => array(
            'data_type' => 'Id_Type',
            'default_value' => null,
            'label' => 'Reference'
        ),
    );
}
