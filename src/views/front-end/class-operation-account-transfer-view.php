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
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\Views\Admin_Std_View;

class Operation_Account_Transfer_View extends Admin_Std_View {

    /** @var Status_Bar */
    public $status_bar_header;
    /** @var P */
    public $header;
    /** @var Drop_Down_List */
    public $transfer_type;
    /** @var Push_Button */
    public $button_transfer_type_reload_cheque;
    /** @var Drop_Down_List */
    public $from_account;
    /** @var Drop_Down_List */
    public $to_account;
    /** @var Text_Line */
    public $amount;
    /** @var Push_Button */
    public $button_make_transfer;
    /** @var Link */
    public $link2;
    /** @var Link */
    public $link1;
    /** @var Status_Bar */
    public $status_bar_footer;

    public function __construct( $id, $controller )
    {
        $items = array(
            'expense' => 'Expense',
            'investment' => 'Investment',
        );
        $this->transfer_type = new Drop_Down_List($items, 'expense');
        $this->button_transfer_type_reload_cheque = new Push_Button( esc_html__( 'Reload', 'bitcoin-bank' ));
        $this->from_account = new Drop_Down_List();
        $this->to_account = new Drop_Down_List();
        $this->amount = new Text_Line();
        $this->button_make_transfer = new Push_Button( esc_html__( 'Transfer', 'bitcoin-bank' ));
        $this->button_make_transfer->set_primary();
        $this->status_bar_footer = new Status_Bar();

        parent::__construct( $id, $controller );
    }

    public function create_content( $parameters = null ) {
        $grid = new Grid(null, array('class' => 'form-table'));

        $grid->add_row();
        $grid->add_cell_header( new Label( 'Transfer type:', array( 'for' => 'from_account' )) );
        $grid->add_cell( array(
            $this->transfer_type,
            $this->button_transfer_type_reload_cheque
        ));

        $grid->add_row();
        $grid->add_cell_header( new Label( 'From account:', array( 'for' => 'from_account' )) );
        $grid->add_cell( $this->from_account );

        $grid->add_row();
        $grid->add_cell_header( new Label( 'To account:', array( 'for' => 'to_account' )) );
        $grid->add_cell( $this->to_account );

        $grid->add_row();
        $grid->add_cell_header( new Label( 'Amount:', array( 'for' => 'amount' )) );
        $grid->add_cell( $this->amount );

        $this->add_content($grid);

        $this->add_content($this->button_make_transfer);

        $this->add_content($this->status_bar_footer);

        parent::create_content( $parameters );
    }
}
