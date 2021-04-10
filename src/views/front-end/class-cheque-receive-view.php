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

use WP_PluginFramework\HtmlComponents\Sort_List;
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\HtmlElements\P;

class Cheque_Receive_View extends Front_Page_View {

    /** @var Status_Bar */
    public $status_bar;
    /** @var Push_Button */
    public $button_claim_cheque;
    /** @var Push_Button */
    public $button_reject_cheque;
    /** @var Link */
    public $link2;
    /** @var Link */
    public $link1;
    /** @var Status_Bar */
    public $status_bar_footer;

    protected $cheque_id = null;
    protected $access_code = null;
    protected $data_objects = null;
    protected $meta_data = null;
    protected $cheque_valid = false;
    protected $cheque_state = null;

    public function __construct( $id, $controller ) {
        parent::__construct( $id, $controller );

        $this->status_bar = new Status_Bar();
        $this->register_component( 'status_bar', $this->status_bar );

        //$this->access_code = new text_Line( esc_html__( 'Access Code:', 'bitcoin-bank' ), '', 'access_code' );
        //$this->register_component( 'access_code', $this->access_code );

        /* translators: Button label. */
        $this->button_claim_cheque = new Push_Button( esc_html__( 'Accept cheque', 'bitcoin-bank' ) );
        $this->register_component( 'button_claim_cheque', $this->button_claim_cheque );

        $this->button_reject_cheque = new Push_Button( esc_html__( 'Reject cheque', 'bitcoin-bank' ) );
        $this->register_component( 'button_reject_cheque', $this->button_reject_cheque );

        $this->status_bar_footer = new Status_Bar();
        $this->register_component( 'status_bar_footer', $this->status_bar_footer );
    }

    public function set_valuesxxx( $values ) {
        if(isset($values['cheque_valid']))
        {
            $this->cheque_valid = $values['cheque_valid'];
            $this->cheque_state = $values['cheque_state'];
            $this->cheque_id = $values['cheque_id'];
            $this->access_code = $values['access_code'];
            $this->data_objects = $values['data_objects'];
            $this->meta_data = $values['meta_data'];
        }
    }

    public function create_content( $parameters = null ) {
        if ( $parameters['cheque_valid'] )
        {
            $png_url = site_url() . '/wp-admin/admin-ajax.php?action=bcf_bitcoinbank_get_cheque_png&cheque_id=' . $parameters['cheque_id'] . '&access_code=' . $parameters['access_code'];
            $img = new Img($png_url);
            $img->add_attribute('style', 'width:800px;height:300px;border: 1px solid gray;');
            $img->add_attribute('class', 'bitcoin-bank-cheque-placeholder');
            $this->add_content($img);

            $this->add_content($this->status_bar);

            if($parameters['show_cheque_data'])
            {
                $labels = array();
                foreach ($parameters['meta_data'] as $meta_item)
                {
                    array_push($labels, $meta_item['label']);
                }

                $attribute = array('class' => 'wpf_list_view');
                $sort_list = new Sort_List(null, $attribute);
                $sort_list->add_row_header($labels);
                $sort_list->add_rows($parameters['data_objects']);

                $this->add_content($sort_list);
            }

            switch ($parameters['cheque_state'])
            {
                case Cheque_Db_Table::STATE_REGISTRATION_INIT:
                    break;
                case Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED:
                    $this->add_content( $this->button_claim_cheque);
                    $this->add_content( '&nbsp;&nbsp;' );
                    $this->add_content( $this->button_reject_cheque);
                    break;
                case Cheque_Db_Table::STATE_REGISTRATION_CLAIMED:
                    break;
                case Cheque_Db_Table::STATE_REGISTRATION_EXPIRED:
                    break;
                default:
                    break;
            }

            $this->add_content($this->status_bar_footer);

            $this->add_content( new P());

        } else {
            $p = new P('Error. This cheque is invalid.');
            $this->add_content($p);
        }

        parent::create_content( $parameters );
    }

}
