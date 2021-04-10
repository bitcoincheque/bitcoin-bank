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

use WP_PluginFramework\DataTypes\Unsigned_Integer_Type;

class Transaction_Type extends Unsigned_Integer_Type {

    public function get_formatted_text() {
        switch($this->value) {
            case Transactions_Db_Table::TRANSACTION_TYPE_NOT_SET:
                $text = 'NOT SET';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_DRAW:
                $text = 'CHEQUE DRAW';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_EXPIRE:
                $text = 'CHEQUE EXPIRE';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_RECEIVE:
                $text = 'CHEQUE RECEIVED';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REVERSED2:
                $text = 'CHEQUE REVERSED';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_REJECT:
                $text = 'CHEQUE REJECTED';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_CHEQUE_FEE:
                $text = 'CHEQUE FEE';
                break;
            case Transactions_Db_Table::TRANSACTION_TYPE_ACCOUNT_TRANSFER:
                $text = 'ACCOUNT TRANSFER';
                break;
            default:
                $text = 'ERROR. UNDEFINED.';
                break;
        }

        return $text;
    }
}
