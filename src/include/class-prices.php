<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

class Prices {

    static public function calculate_cheque_fee($client_id, $account_id, $cheque) {
        $fee = new Crypto_currency_type(null, null );
        $fee->set_value(100); /* Satoshi */
        $fee->set_unit('BTC');
        return $fee;
    }
}
