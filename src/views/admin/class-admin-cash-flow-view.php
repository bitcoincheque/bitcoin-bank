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
use WP_PluginFramework\HtmlComponents\Text_Box;
use WP_PluginFramework\HtmlComponents\Check_Box;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlElements\H;
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
class Admin_Cash_Flow_View extends Admin_Std_View {

	public function create_content( $parameters = null ) {

        $table_attr = array( 'class' => 'bcq_admin_table' );
        $table      = new Table( null, $table_attr );
        $tr         = new Tr();
        $tr->add_content( new Th( 'Account' ) );
        $tr->add_content( new Th( 'Value' ) );
        $table->add_content( $tr );

        foreach($parameters as $parameter)
        {
            $tr = new Tr();
            $tr->add_content(new Td($parameter['label']));
            $tr->add_content(new Td($parameter['grand_totals']));
            $table->add_content($tr);
        }

        $this->add_content($table);

        parent::create_content( $parameters );
	}
}
