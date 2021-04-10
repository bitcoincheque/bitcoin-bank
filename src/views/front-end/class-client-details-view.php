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

use WP_PluginFramework\HtmlComponents\Sort_List;
use WP_PluginFramework\Views\Form_View;
use WP_PluginFramework\Views\Std_View;
use WP_PluginFramework\HtmlComponents\Grid;
use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Client_Details_View extends Form_View {

    public function create_content( $parameters = null )
    {
        $attribute = array('class' => 'wpf_list_view');
        $sort_list = new Sort_List(null, $attribute);

        if (is_admin())
        {
            $sort_list->add_row_header(array(
                'Property',
                'Value'
            ));
        }

        $sort_list->add_data_objects_rows($parameters['data_objects']);

        $this->add_content($sort_list);

        parent::create_content( $parameters );
    }

}
