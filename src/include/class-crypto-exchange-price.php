<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Mailer;
use WP_PluginFramework\Utils\Security_Filter;

class Crypto_Exchange_Price {

    static private function get_blockchain_price_index () {
        $price_json = false;

        $url = 'https://blockchain.info/ticker';
        $response = wp_remote_get( $url);

        if($response['response']['code'] === 200)
        {
            $json_string = $response['body'];
            $price_json = json_decode($json_string, true);
            if(!is_array($price_json)) {
                $price_json = false;
            }
        }

        return $price_json;
    }

    static private function get_bitcoin_price_transient_name ( $currency ) {
        $transient_name = 'bitcoin_bank_bitcoin_price_index_for_' . $currency;
        return $transient_name;
    }

    static private function set_bitcoin_price_transient ( $currency_code, $price )
    {
        $transient_name = self::get_bitcoin_price_transient_name( $currency_code );

        $transient = array(
            'currency' => $currency_code,
            'price' => $price
        );

        $result = set_transient($transient_name, $transient, 3600);
        if(!$result) {
            Debug_Logger::write_debug_error('Error setting transient', $transient_name, $price);
        }
    }

    static private function get_bitcoin_price_transient ( $currency_code ) {
        $transient_name = self::get_bitcoin_price_transient_name( $currency_code );
        $transient = get_transient($transient_name);
        return $transient;
    }

    static public function get_bitcoin_price ($currency)
    {
        $currency_rate=false;

        $transient = self::get_bitcoin_price_transient($currency);
        if($transient !== false)
        {
            $currency_rate = $transient['price'];
        }
        else
        {
            $price_json = self::get_blockchain_price_index();
            if($price_json)
            {
                foreach($price_json as $currency_code => $price_data )
                {
                    if(is_string($currency_code))
                    {
                        $ticker_length = strlen($currency_code);
                        if(($ticker_length >= 3) and ($ticker_length <= 20))
                        {
                            $last_price = $price_data['last'];
                            if (is_numeric($last_price))
                            {
                                $last_price = floatval($last_price);
                                if ($currency == $currency_code)
                                {
                                    $currency_rate = $last_price;
                                }

                                self::set_bitcoin_price_transient($currency_code, $last_price);
                            } else {
                                self::set_bitcoin_price_transient($currency_code, false);
                            }
                        }
                    }
                }
            }

            if($currency_rate === false)
            {
                self::set_bitcoin_price_transient($currency, false);
            }
        }

        return $currency_rate;
    }


    static public function get_currency_exchange_rate( $from_currency, $to_currency )
    {
        if ($from_currency === 'BTC')
        {
            $rate = self::get_bitcoin_price($to_currency);
            return $rate;
        } else {
            return false;
        }
    }

}
