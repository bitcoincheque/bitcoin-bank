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

use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\P;

class Cheque_Lookup_View extends Front_Page_View {

    /** @var Status_Bar */
    public $status_bar_header;
    /** @var Text_Line */
    public $cheque_id;
    /** @var Text_Line */
    public $access_code;
    /** @var Push_Button */
    public $button_lookup_cheque;
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
        $this->status_bar_header = new Status_Bar();
        $this->cheque_id = new text_Line( esc_html__( 'Cheque Serial Number (S/N):', 'bitcoin-bank' ), '', 'cheque_id' );
        $this->access_code = new text_Line( esc_html__( 'Access Code:', 'bitcoin-bank' ), '', 'access_code' );
        /* translators: Button label. */
        $this->button_lookup_cheque = new Push_Button( esc_html__( 'Look up', 'bitcoin-bank' ) );
        $this->status_bar_footer = new Status_Bar();
		parent::__construct( $id, $controller );
    }

    public function create_content( $parameters = null ) {
        $this->status_bar_header = new Status_Bar();
        $this->add_header( 'status_bar_header', $this->status_bar_header );

        $this->add_form_input( 'cheque_id', $this->cheque_id );
        $this->add_form_input( 'amount', $this->access_code );

        $this->add_button( 'button_lookup_cheque', $this->button_lookup_cheque );

        $this->add_footer( 'status_bar_footer', $this->status_bar_footer );

        parent::create_content( $parameters );
    }


}
