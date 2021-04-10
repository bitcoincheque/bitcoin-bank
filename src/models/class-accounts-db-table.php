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

use WP_PluginFramework\DataTypes\Data_Type;
use WP_PluginFramework\Models\Active_Record;
use WP_PluginFramework\Utils\Debug_Logger;


/**
 * Summary.
 *
 * Description.
 */
class Accounts_Db_Table extends Active_Record
{

    /* Database table name: */
    const TABLE_NAME = 'bcq_bitcoin_bank_accounts';

    /* List of table field names: */
    const CLIENT_ID = 'client_id';
    const BALANCE = 'balance';
    const CHART_ACCOUNT_TYPE_ID = 'chart_account_type_id';
    const LABEL = 'label';

    /* Metadata describing database fields and data properties: */
    static $meta_data = array(
        self::PRIMARY_KEY => array(
            'data_type' => 'BCQ_BitcoinBank\Account_Id_Type',
            'default_value' => null,
            'label' => 'Account No.'
        ),
        self::CLIENT_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Client_Id_Type',
            'default_value' => 0,
            'label' => 'Client ID'
        ),
        self::BALANCE => array(
            'data_type' => 'BCQ_BitcoinBank\Crypto_currency_type',
            'default_value' => 0,
            'label' => 'Amount'
        ),
        self::CHART_ACCOUNT_TYPE_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Account_Chart_Id_Description_Type',
            'default_value' => 0,
            'label' => 'Account Chart'
        ),
        self::LABEL => array(
            'data_type' => 'String_Type',
            'default_value' => null,
            'label' => 'Description'
        )
    );

    protected function create_data_object( $metadata, $key, $value = null, $record = null ) {
        $data_object = parent::create_data_object( $metadata, $key, $value );
        if($data_object instanceof Crypto_currency_type) {
            if($record !== null) {
                $account_chart_type = $record[self::CHART_ACCOUNT_TYPE_ID];
                $debit_credit_type = Accounting::get_debit_credit_type($account_chart_type);
                $data_object->set_property('debit_credit_type', $debit_credit_type);
            }
        }
        return $data_object;
    }

    public function create_account($client_id, $account_chart_id, $label)
    {
        $this->clear_all_data();
        $this->set_data(self::CLIENT_ID, $client_id);
        $this->set_data(self::BALANCE, 0);
        $this->set_data(self::CHART_ACCOUNT_TYPE_ID, $account_chart_id);
        $this->set_data(self::LABEL, $label);
        $this->save_data();
        return $this->get_data(Clients_Db_Table::PRIMARY_KEY);
    }

    public function get_account_record($account_id) {
        $account = false;
        if ($this->load_data_id($account_id))
        {
            $account_record = $this->get_data_record();
        }
        return $account_record;
    }

    public function get_account_field($account_id, $key) {
        $value = null;
        $account_record = $this->get_account_record($account_id);
        if ($account_record)
        {
            $value = $account_record[$key];
        }
        return $value;
    }

    public function get_account_owner($account_id) {
        $client_id = false;
        $account_record = $this->get_account_record($account_id);
        if ($account_record)
        {
            $client_id = $account_record[self::CLIENT_ID];
        }
        return $client_id;
    }

    public function get_accounts_of_chart_types($account_chart_type) {
        $account_list = array();
        if ($this->load_data(self::CHART_ACCOUNT_TYPE_ID, $account_chart_type) !== false)
        {
            $account_list = $this->get_copy_all_data();
        }
        return $account_list;
    }

}
