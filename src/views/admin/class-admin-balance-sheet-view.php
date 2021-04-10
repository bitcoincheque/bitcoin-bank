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

use WP_PluginFramework\HtmlComponents\Grid;
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
class Admin_Balance_Sheet_View extends Admin_Std_View {

	public function create_content( $parameters = null ) {

        $attribute = array('class' => 'wpf_list_view');
        $grid = new Grid(null, $attribute);

        $grid->add_row_header($parameters['headers']);

        $grid->add_rows($parameters['asset_headers']);
        $grid->add_rows($parameters['asset_accounts']);
        $grid->add_rows($parameters['asset_totals']);

        $grid->add_row(array('','',''));

        $grid->add_rows($parameters['liabilities_headers']);
        $grid->add_rows($parameters['liabilities_accounts']);
        $grid->add_rows($parameters['liabilities_totals']);

        $grid->add_row(array('','',''));

        $grid->add_rows($parameters['equity_headers']);
        $grid->add_rows($parameters['equity_accounts']);
        $grid->add_rows($parameters['equity_totals']);

        $grid->add_row(array('','',''));
        $grid->add_rows($parameters['total_balance']);

        $this->add_content($grid);

        parent::create_content( $parameters );
	}
}
