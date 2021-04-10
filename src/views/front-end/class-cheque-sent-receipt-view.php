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
use WP_PluginFramework\Views\Std_View;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\Img;

/**
 * Summary.
 *
 * Description.
 */
class Cheque_Sent_Receipt_View extends Std_View {

    protected $cheque_id = null;
    protected $access_code = null;
    protected $values2 = null;

    /** @var Status_Bar */
    public $status_bar;

    public function __construct( $id, $controller ) {
        parent::__construct($id, $controller);

        $this->status_bar = new Status_Bar();
        $this->register_component( 'status_bar', $this->status_bar );
    }

    public function set_values( $values ) {
        /*
        $this->cheque_id = $values['cheque_id'];
        $this->access_code = $values['access_code'];
        $this->data_objects = $values['data_objects'];
        $this->meta_data = $values['meta_data'];
        $this->status_bar->set_status_text($values['message'], Status_Bar::STATUS_SUCCESS);
        */
    }

    public function create_content( $parameters = null )
    {
        $img = Cheque_Handler::create_cheque_picture_content($parameters['cheque_id'], $parameters['access_code']);
        $this->add_content($img);

        $this->add_content($this->status_bar);

        $labels = array();
        foreach ($parameters['meta_data'] as $meta_item) {
            array_push($labels, $meta_item['label']);
        }

        $attribute = array('class' => 'wpf_list_view');
        $sort_list = new Sort_List(null, $attribute);
        $sort_list->add_row_header( $labels );
        $sort_list->add_rows($parameters['data_objects']);

        $this->add_content($sort_list);

        parent::create_content( $parameters );
    }

}
