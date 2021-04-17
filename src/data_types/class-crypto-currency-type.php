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

use WP_PluginFramework\DataTypes\Currency_Type;
use WP_PluginFramework\HtmlElements\A;
use WP_PluginFramework\HtmlElements\Span;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Security_Filter;

class Crypto_Currency_Type extends Currency_Type {

    protected $debit_credit_type = false;
    protected $alternative_currency = false;

    static public function convert_str_to_value($text, $text_currency_unit=null)
    {
        $decimal_point = Settings_Currency_Options::get_options(Settings_Currency_Options::DECIMAL_POINT);
        switch($decimal_point) {
            case '.':
                $thousands_point = ',';
                break;
            case ',':
                $thousands_point = '.';
                break;
            default:
                $decimal_point = '.';
                $thousands_point = ',';
                break;
        }

        $max_decimal_numbers = 8;

        $text = trim($text);
        $text = str_replace($thousands_point, '', $text);

        if($decimal_point == ',')
        {
            $text = str_replace($decimal_point, '.', $text);
        }

        if(!is_numeric($text))
        {
            return false;
        }

        $decimal_point_count = substr_count($text, '.');

        if($decimal_point_count == 0)
        {
            $integer_part = $text;
            $fractional_part = '';
        }
        elseif($decimal_point_count == 1)
        {
            $decimal_pos = strpos ($text, '.');
            $integer_part = substr ($text, 0, $decimal_pos);
            $fractional_part = substr ($text, $decimal_pos+1);
        }
        else
        {
            return false;
        }

        $fractional_part_length = strlen($fractional_part);
        if($fractional_part_length > $max_decimal_numbers) {
            return false;
        }

        $fractional_part = str_pad($fractional_part,  $max_decimal_numbers, "0");

        $number_in_units = $integer_part . $fractional_part;

        $value = intval($number_in_units);
        return $value;
    }

    public function convert_value_to_str($value, $show_unit=false) {
        $decimal_point = Settings_Currency_Options::get_options(Settings_Currency_Options::DECIMAL_POINT);
        switch ($decimal_point) {
            case '.':
                $thousands_point = ',';
                break;
            case ',':
                $thousands_point = '.';
                break;
            default:
                $decimal_point = '.';
                $thousands_point = ',';
                break;
        }

        if ($this->alternative_currency) {
            $currency_unit = Settings_Currency_Options::get_options(Settings_Currency_Options::ALTERNATE_CURRENCY);
            $decimals = 2;
            $prefix = '';

            if ($this->alternative_currency) {
                $value /= 100000000;
                $currency_exchange_rate = Crypto_Exchange_Price::get_currency_exchange_rate('BTC', $currency_unit);
                if ($currency_exchange_rate == false) {
                    $currency_unit = 'USD';
                    $currency_exchange_rate = Crypto_Exchange_Price::get_currency_exchange_rate('BTC', $currency_unit);
                    if ($currency_exchange_rate == false) {
                        return 'No exchange rate';
                    }
                }
                $value *= $currency_exchange_rate;
                $value *= 100;
                $value = intval($value);
            }
        }
        else {
            $currency_unit = Settings_Currency_Options::get_options(Settings_Currency_Options::CURRENCY_UNIT);
            $currency_unit_position = Security_Filter::safe_read_get_request('currency_unit', Security_Filter::STRING_KEY_NAME);
            switch ($currency_unit_position) {
                case 'btc':
                    $decimals = 8;
                    $prefix = '';
                    break;

                case 'mbtc':
                    $decimals = 5;
                    $prefix = ' milli';
                    break;

                case 'ubtc':
                    $decimals = 2;
                    $prefix = ' micro';
                    break;

                case 'satoshi':
                    $decimals = 0;
                    $prefix = ' satoshi';
                    break;

                default:
                    $decimals = 8;
                    $prefix = '';
                    break;
            }
        }

        $currency_string = strval($value);

        $negative = false;
        if ($currency_string[0] === '-') {
            $negative = true;
            $currency_string = substr($currency_string, 1);
        }

        $length = strlen($currency_string);
        if ($length > $decimals) {
            $integer_part = substr($currency_string, 0, $length - $decimals);
            $fractional_part = substr($currency_string, -$decimals, $decimals);
        }
        else {
            $integer_part = '0';
            $fractional_part = str_repeat('0', $decimals - $length) . $currency_string;
        }

        $integer_part = strrev($integer_part);
        $integer_part_split = chunk_split($integer_part, 3, $thousands_point);
        $integer_part_formatted = strrev($integer_part_split);
        if ($integer_part_formatted[0] == $thousands_point) {
            $integer_part_formatted = substr($integer_part_formatted, 1);
        }

        $fractional_part_formatted = chunk_split($fractional_part, 3, $thousands_point);
        $n = strlen($fractional_part_formatted);
        if ($fractional_part_formatted[$n - 1] == $thousands_point) {
            $fractional_part_formatted = substr($fractional_part_formatted, 0, $n - 1);
        }

        if(is_admin()) {
            $credit_as_negative = Settings_Account_Options::get_options(Settings_Account_Options::SHOW_CREDIT_ACCOUNTS_NEGATIVE);
            if(!$credit_as_negative) {
                if (!isset($this->transaction_data)) {
                    if ($this->debit_credit_type === Account_Chart_Db_Table::CREDIT_ACCOUNT) {
                        $negative = !$negative;
                    }
                }
            }
        } else {
            if(!isset($this->transaction_data)) {
                $negative = !$negative;
            }
        }

        $negative_sign = '';
        if ($negative) {
            if ($this->value !== 0) {
                $negative_sign = '-';
            }
        }
        else {
            $negative_sign = '';
        }

        if ($currency_unit) {
            $currency_unit .= ' ';
        }

        if ($decimals == 0) {
            $decimal_point = '';
        }

        $text = '';
        if ($show_unit) {
            $text .= $currency_unit;
        }

        $text .= $negative_sign . $integer_part_formatted . $decimal_point . $fractional_part_formatted . $prefix;

        return $text;
    }

    public function get_string() {
        $value = $this->value;
        return $this->convert_value_to_str($value, false);
    }

    public function get_formatted_text()
    {
        $value = $this->get_value();
        return $this->convert_value_to_str($value, true);
    }

    public function set_value( $value ) {
        switch ( gettype( $value ) ) {
            case 'string':
                $this->value = $this->convert_str_to_value( $value );
                break;

            case 'integer':
                $this->value = $value;
                break;

            default:
                Debug_Logger::write_debug_error( 'Unsupported data type ' . gettype( $value ) );
                break;
        }
    }

    public function create_content()
    {
        $content = $this->get_formatted_text();
        if($this->alternative_currency) {
            $span = new Span($content, array('class' => 'bitcoin_bank_alternative_currency'));
        } else {
            $span = new Span($content, array('class' => 'bitcoin_bank_bitcoin_currency'));
        }
        $this->set_content( $span );
    }
}
