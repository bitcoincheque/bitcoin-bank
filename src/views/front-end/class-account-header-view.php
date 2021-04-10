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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlComponents\Drop_Down_List;
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\Views\Form_View;
use WP_PluginFramework\HtmlElements\P;

/**
 * Summary.
 *
 * Description.
 */
class Account_Header_View extends Form_View {

    /** @var Drop_Down_List */
    public $currency_unit;
    /** @var Push_Button */
    public $button_update_currency_unit;

    public function __construct( $id, $controller )
    {
        $items = array(
            'btc' => 'BTC',
            'mbtc' => 'milli-BTC',
            'ubtc' => 'micro-BTC',
            'satoshi' => 'satoshi'
        );
        $currency_unit = Security_Filter::safe_read_get_request('currency_unit', Security_Filter::STRING_KEY_NAME);
        $this->currency_unit = new Drop_Down_List($items, $currency_unit);
        $this->button_update_currency_unit = new Push_Button('Update', Push_Button::METHOD_GET);

        parent::__construct( $id, $controller );
    }

    public function create_content( $parameters = null )
    {
        if($parameters['status'])
        {
            $p = new P();
            $p->add_content('Currency unit:');
            $p->add_content('&nbsp;&nbsp;');
            $p->add_content($this->currency_unit);
            $p->add_content('&nbsp;&nbsp;');
            $p->add_content($this->button_update_currency_unit);
            $this->add_content($p);

            $attribute = array('class' => 'wpf_list_view');
            $grid = new Grid(null, $attribute);

            $grid->add_row(array(
                'Account No.:',
                $parameters['values'][Accounts_Db_Table::PRIMARY_KEY]->get_formatted_text()
            ));

            $grid->add_row(array(
                'Description:',
                $parameters['values'][Accounts_Db_Table::LABEL]->get_formatted_text()
            ));

            $bitcoin_value = $parameters['values'][Accounts_Db_Table::BALANCE];
            $alt_value = clone $bitcoin_value;
            $alt_value->set_property('alternative_currency', true);
            $fiat_value = $alt_value->get_formatted_text();

            $balance_text = array(
                $bitcoin_value,
                ' = ' . $fiat_value
            );

            $grid->add_row(array(
                'Balance:',
                $balance_text
            ));

            $this->add_content($grid);
        } else {
            $p1 = new P('Error. Account info not available.');
            $this->add_content($p1);
        }

        parent::create_content( $parameters );
    }

}
