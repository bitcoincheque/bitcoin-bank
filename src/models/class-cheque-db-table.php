<?php
    /** Bitcoin Bank plugin for WordPress.
     *  Puts a login/registration form in your posts and pages.
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
class Cheque_Db_Table extends Active_Record
{

    /* Database table name: */
    const TABLE_NAME = 'bcq_bitcoin_bank_cheque_data';

    /* List of table field names: */
    const ISSUE_TIMESTAMP = 'issue_time';
    const EXPIRE_TIME = 'expire_time';
    const STATE = 'state';
    const ISSUER_IDENTITY = 'issuer_identity';
    const SENDER_ADDRESS = 'sender_address';
    const RECEIVER_ADDRESS = 'receiver_address';
    const AMOUNT = 'amount';
    const FEE = 'fee';
    const CURRENCY_UNIT = 'currency_unit';
    const MEMO = 'memo';
    const ACCESS_CODE = 'access_code';
    const HASH = 'hash';
    const DEBIT_ACCOUNT_ID = 'debit_account_id';
    const CREDIT_ACCOUNT_ID = 'credit_account_id';
    const SENDER_CLIENT_ID = 'sender_client_id';
    const RECEIVER_CLIENT_ID = 'receiver_client_id';

    /* State values: */
    const STATE_REGISTRATION_INIT = 0;
    const STATE_REGISTRATION_UNCLAIMED = 1;
    const STATE_REGISTRATION_CLAIMED = 2;
    const STATE_REGISTRATION_EXPIRED = 3;
    const STATE_REGISTRATION_CASHED = 4;
    const STATE_REGISTRATION_REJECTED = 5;

    /* Metadata describing database fields and data properties: */
    static $meta_data = array(
        self::PRIMARY_KEY => array(
            'data_type' => 'BCQ_BitcoinBank\Cheque_Id_Type',
            'default_value' => null,
            'label' => 'Cheque S/N'
        ),
        self::ISSUE_TIMESTAMP => array(
            'data_type' => 'BCQ_BitcoinBank\Bank_Datetime_Type',
            'default_value' => null,
            'label' => 'Issue time'
        ),
        self::EXPIRE_TIME => array(
            'data_type' => 'BCQ_BitcoinBank\Bank_Datetime_Type',
            'default_value' => null,
            'label' => 'Expire time'
        ),
        self::STATE => array(
            'data_type' => 'BCQ_BitcoinBank\Cheque_State_Type',
            'default_value' => self::STATE_REGISTRATION_INIT,
            'label' => 'Status'
        ),
        self::ISSUER_IDENTITY => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Issuer identity'
        ),
        self::SENDER_ADDRESS => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Received from'
        ),
        self::RECEIVER_ADDRESS => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Sent to'
        ),
        self::AMOUNT => array(
            'data_type' => 'BCQ_BitcoinBank\Crypto_currency_type',
            'default_value' => 0,
            'label' => 'Amount'
        ),
        self::FEE => array(
            'data_type' => 'BCQ_BitcoinBank\Crypto_currency_type',
            'default_value' => 0,
            'label' => 'Fee'
        ),
        self::CURRENCY_UNIT => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Currency unit'
        ),
        self::MEMO => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Memo'
        ),
        self::ACCESS_CODE => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Access Code'
        ),
        self::HASH => array(
            'data_type' => 'String_Type',
            'default_value' => '',
            'label' => 'Hash'
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
        self::SENDER_CLIENT_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Client_Id_Type',
            'default_value' => null,
            'label' => 'Sender'
        ),
        self::RECEIVER_CLIENT_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Client_Id_Type',
            'default_value' => null,
            'label' => 'Receiver'
        )
    );

    public function create_access_code(){
        $r1 = rand(1, PHP_INT_MAX - 1);
        $r2 = rand(1, PHP_INT_MAX - 1);
        $r = $r1 / $r2;
        $str = strval($r);
        return str_replace('.', '', $str);
    }

    public function create_cheque(
        $cheque,
        $timestamp,
        $debit_account_id,
        $credit_account_id,
        $fee,
        $sender_client_id,
        $receiver_client_id,
        $state
    )
    {
        $sender_address = $cheque->get_data(Cheque_File::SENDER_ADDRESS);
        $receiver_address = $cheque->get_data(Cheque_File::RECEIVER_ADDRESS);
        $amount = $cheque->get_data(Cheque_File::AMOUNT);
        $currency_unit = $cheque->get_data(Cheque_File::CURRENCY_UNIT);
        $expire_time = $cheque->get_data(Cheque_File::EXPIRE_TIME);
        $memo = $cheque->get_data(Cheque_File::MEMO);
        $access_code = $this->create_access_code();

        $this->set_data(Cheque_Db_Table::STATE, $state);
        $this->set_data(Cheque_Db_Table::SENDER_ADDRESS, $sender_address);
        $this->set_data(Cheque_Db_Table::RECEIVER_ADDRESS, $receiver_address);
        $this->set_data(Cheque_Db_Table::ISSUE_TIMESTAMP, $timestamp);
        $this->set_data(Cheque_Db_Table::EXPIRE_TIME, $expire_time);
        $this->set_data(Cheque_Db_Table::AMOUNT, $amount);
        $this->set_data(Cheque_Db_Table::FEE, $fee);
        $this->set_data(Cheque_Db_Table::CURRENCY_UNIT, $currency_unit);
        $this->set_data(Cheque_Db_Table::MEMO, $memo);
        $this->set_data(Cheque_Db_Table::DEBIT_ACCOUNT_ID, $debit_account_id);
        $this->set_data(Cheque_Db_Table::CREDIT_ACCOUNT_ID, $credit_account_id);
        $this->set_data(Cheque_Db_Table::SENDER_CLIENT_ID, $sender_client_id);
        $this->set_data(Cheque_Db_Table::RECEIVER_CLIENT_ID, $receiver_client_id);
        $this->set_data(Cheque_Db_Table::ACCESS_CODE, $access_code);
        $result = $this->save_data();

        if( $result !== false ) {
            $cheque->set_data( Cheque_File::SERIAL_NUMBER, $this->get_data(Cheque_Db_Table::PRIMARY_KEY ));
            $cheque->set_data( Cheque_File::ISSUE_TIME, $this->get_data(Cheque_Db_Table::ISSUE_TIMESTAMP ));
            $cheque->set_data( Cheque_File::SENDER_ADDRESS, $this->get_data(Cheque_Db_Table::SENDER_ADDRESS ));
            $cheque->set_data( Cheque_File::ACCESS_CODE, $this->get_data(Cheque_Db_Table::ACCESS_CODE ));
        } else {
            $cheque = false;
        }
        return $cheque;
    }
}
