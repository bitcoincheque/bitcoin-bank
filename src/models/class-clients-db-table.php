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

use phpDocumentor\Reflection\Types\Self_;
use WP_PluginFramework\Models\Active_Record;


/**
 * Summary.
 *
 * Description.
 */
class Clients_Db_Table extends Active_Record
{

    /* Database table name: */
    const TABLE_NAME = 'bcq_bitcoin_bank_clients_data';

    /* List of table field names: */
    const WP_USER_ID = 'wp_user_id';
    const REGISTER_DATETIME = 'register_datetime';
    const COUNTRY = 'country';
    const MONEY_ADDRESS = 'money_address';

    /* Metadata describing database fields and data properties: */
    static $meta_data = array(
        self::PRIMARY_KEY => array(
            'data_type' => 'BCQ_BitcoinBank\Client_Id_Type',
            'default_value' => null,
            'label' => 'Client ID'
        ),
        self::REGISTER_DATETIME => array(
            'data_type' => 'BCQ_BitcoinBank\Bank_Datetime_Type',
            'default_value' => null,
            'label' => 'Registration time'
        ),
        self::WP_USER_ID => array(
            'data_type' => 'BCQ_BitcoinBank\Wp_User_Id_Type',
            'default_value' => 0,
            'label' => 'Login username'
        ),
        self::COUNTRY => array(
            'data_type' => 'Unsigned_Integer_Type',
            'default_value' => '',
            'label' => 'Country'
        ),
        self::MONEY_ADDRESS => array(
            'data_type' => 'Money_Address_Type',
            'default_value' => null,
            'label' => 'Money Address'
        )
    );

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

    public function create_client($now, $wp_user_id, $money_address)
    {
        $this->clear_all_data();
        $this->set_data(self::REGISTER_DATETIME, $now);
        $this->set_data(self::WP_USER_ID, $wp_user_id);
        $this->set_data(self::MONEY_ADDRESS, $money_address);
        $this->save_data();
        return $this->get_data(Clients_Db_Table::PRIMARY_KEY);
    }

    public function get_wp_user_id($client_id) {
        $wp_user_id = false;
        if ($this->load_data_id($client_id))
        {
            $wp_user_id = $this->get_data(self::WP_USER_ID);
        }
        return $wp_user_id;
    }

}
