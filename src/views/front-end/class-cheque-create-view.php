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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Controllers\Form_Controller;
use WP_PluginFramework\HtmlComponents\Drop_Down_List;
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\Views\Form_View;
use WP_PluginFramework\Views\Std_View;

class Cheque_Create_View extends Std_View {

    /** @var Status_Bar */
    public $status_bar_header;
    /** @var P */
    public $header;
    /** @var Text_Line */
    public $email;
    /** @var Text_Line */
    public $amount;
    /** @var Text_Line */
    public $memo;
    /** @var Drop_Down_List */
    public $expire;
    /** @var Push_Button */
    public $button_create_cheque;
    /** @var Link */
    public $link2;
    /** @var Link */
    public $link1;
    /** @var Status_Bar */
    public $status_bar_footer;

    /**
     * Password_Recovery_View constructor.
     *
     * @param $id
     * @param $controller
     */
    public function __construct( $id, $controller ) {

        $this->email = new text_Line( esc_html__( 'E-mail address:', 'bitcoin-bank' ), '', 'email' );
        $this->amount = new text_Line( esc_html__( 'Amount:', 'bitcoin-bank' ), '', 'amount' );
        $this->memo = new text_Line( esc_html__( 'Memo:', 'bitcoin-bank' ), '', 'memo' );
        $expire_options = array(
            'one_hour' => '1 hour',
            'two_hour' => '2 hours',
            'tree_hour' => '3 hours',
            'six_hour' => '6 hours',
            'half_day' => '12 hours',
            'one_day' => '24 hours',
            'two_days' => '2 days',
            'tree_days' => '3 days',
            'five_days' => '5 days',
            'one_week' => '1 week',
            'two_weeks' => '2 weeks',
        );
        $this->expire = new Drop_Down_List($expire_options, 'one_day');
        $this->button_create_cheque = new Push_Button( esc_html__( 'Send cheque', 'bitcoin-bank' ) );
        $this->status_bar_footer = new Status_Bar();

        parent::__construct( $id, $controller );
    }

    public function create_content( $parameters = null )
    {
        if($parameters['status'])
        {
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

        $p_info = new P('Send cheque by e-mail. The recipient does not need bank account in advance, and will receive an e-mail with a picture of the cheque and link to sign up a bank client in order to cash the cheque.');
        $this->add_content($p_info);

        $this->add_form_input( 'email', $this->email, 'E-mail:', 'Only this recipient can cash the cheque.' );
        $this->add_form_input( 'amount', $this->amount, 'Amount:', 'Amount to send in bitcoins.' );
        $this->add_form_input( 'memo',  $this->memo, 'Memo:', 'Optional note written on the cheque.');
        $this->add_form_input( 'expire',  $this->expire, 'Expires after:');

        $this->add_button('button_create_cheque', $this->button_create_cheque);

		$this->add_post_form_content( $this->status_bar_footer);

        parent::create_content( $parameters );
    }
}
