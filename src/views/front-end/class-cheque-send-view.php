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

use WP_PluginFramework\HtmlComponents\Drop_Down_List;
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;

class Cheque_Send_View extends Front_Page_View {

    /** @var Push_Button */
    public $button_send_cheque;
    /** @var Status_Bar */
    public $status_bar_footer;

    /**
     * Password_Recovery_View constructor.
     *
     * @param $id
     * @param $controller
     */
    public function __construct( $id, $controller ) {

        $this->button_send_cheque = new Push_Button( esc_html__( 'Send e-mail', 'bitcoin-bank' ) );
        $this->status_bar_footer = new Status_Bar();

        parent::__construct( $id, $controller );
    }

    public function create_content( $parameters = null ) {

        $png_url     = site_url() . '/wp-admin/admin-ajax.php?action=bcf_bitcoinbank_get_cheque_png&cheque_id=' . $this->cheque_id . '&access_code=' . $this->access_code;
        $img = new Img($png_url);
        $this->add_content($img);

        $this->add_content($this->button_send_cheque);

        $this->add_content($this->status_bar_footer);

        parent::create_content( $parameters );
    }
}
