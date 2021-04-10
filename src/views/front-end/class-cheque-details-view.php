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

use WP_PluginFramework\HtmlComponents\Html_Text;
use WP_PluginFramework\HtmlComponents\Sort_List;
use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Text_Box;
use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlElements\A;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\Views\Admin_Std_View;

/**
 * Summary.
 *
 * Description.
 */
class Cheque_Details_View extends Admin_Std_View {

    public function create_content( $parameters = null )
    {
        if($parameters['access_right'])
        {
            $png_url = site_url() . '/wp-admin/admin-ajax.php?action=bcf_bitcoinbank_get_cheque_png&cheque_id=' . $parameters['cheque_id'] . '&access_code=' . $parameters['access_code'];
            $img = new Img($png_url);
            $img->add_attribute('style', 'width:800px;height:300px;border: 1px solid gray;');
            $img->add_attribute('class', 'bitcoin-bank-cheque-placeholder');
            $this->add_content($img);

            $this->add_content(new P());
            $download_url = site_url() . '/wp-admin/admin-ajax.php?action=bcf_bitcoinbank_download_cheque_file&cheque_id=' . $parameters['cheque_id'] . '&access_code=' . $parameters['access_code'];
            $a = new A('Download', $download_url);
            $this->add_content($a);

            $this->add_content(new P());

            $attribute = array('class' => 'wpf_list_view');
            $sort_list = new Sort_List(null, $attribute);

            $sort_list->add_row_header(array(
                'Property',
                'Value'
            ));

            $sort_list->add_data_objects_rows($parameters['values']);

            $this->add_content($sort_list);
        }
        else
        {
            if($parameters['cheque_exist'])
            {
                $p = new P('You are not allowed to access this cheque s/n ' . $parameters['cheque_id']);
            }
            else
            {
                $p = new P('Error: Invalid cheque reference.');
            }
            $this->add_content($p);
        }

        parent::create_content( $parameters );
    }

}
