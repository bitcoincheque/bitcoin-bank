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
use WP_PluginFramework\HtmlElements\Span;

class Cheque_State_Type extends Unsigned_Integer_Type {

    public function get_formatted_text() {
        switch($this->value) {
            case Cheque_Db_Table::STATE_REGISTRATION_INIT:
                $text = 'INIT';
                $span = new Span($text, array('class' => 'bitcoin-bank-cheque-not-set'));
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED:
                $text = 'PENDING';
                $span = new Span($text, array('class' => 'bitcoin-bank-cheque-unclaimed'));
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_CLAIMED:
                $text = 'RECEIVED';
                $span = new Span($text, array('class' => 'bitcoin-bank-cheque-claimed'));
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_CASHED:
                $text = 'CASHED';
                $span = new Span($text, array('class' => 'bitcoin-bank-cheque-cashed'));
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_EXPIRED:
                $text = 'EXPIRED';
                $span = new Span($text, array('class' => 'bitcoin-bank-cheque-expired'));
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_REJECTED:
                $text = 'DECLINED';
                $span = new Span($text, array('class' => 'bitcoin-bank-cheque-rejected'));
                break;
            default:
                $text = 'ERROR UNDEFINED.';
                $span = new Span($text);
                break;
        }

        return $span;
    }
}