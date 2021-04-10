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

use WP_PluginFramework\Controllers\List_Controller;
use WP_PluginFramework\Pages\Admin_Tab_Page;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\Utils\Security_Filter;

class Admin_Accounts_Tab_Page extends Admin_Tab_Page {

    public function __construct( $name=null, $content=null ) {
        $nav_tabs = array(
            'accounts' => array(
                'text' => esc_html__( 'Account List', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Account List', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-accounts&tab=accounts',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'account_chart' => array(
                'text' => esc_html__( 'Account Chart', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Account Chart', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-accounts&tab=account_chart',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'default_accounts' => array(
                'text' => esc_html__( 'Default Accounts', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Default Accounts', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-accounts&tab=default_accounts',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'system_accounts' => array(
                'text' => esc_html__( 'System Accounts', 'bitcoin-bank' ),
                'headline' => esc_html__( 'System Accounts', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-accounts&tab=system_accounts',
            )
        );

        parent::__construct( $nav_tabs, $name, $content );
    }

    public function create_content( $config = null ) {
        $tab_name = Security_Filter::safe_read_get_request( 'tab', Security_Filter::STRING_KEY_NAME );

        switch ( $tab_name ) {
            case 'account_chart':
                $controller = new List_Controller('BCQ_BitcoinBank\Account_Chart_Db_Table');
                break;

            case 'default_accounts':
                $controller = new List_Controller('BCQ_BitcoinBank\Account_Defaults_Db_Table');
                break;

            case 'system_accounts':
                $controller = new List_Controller('BCQ_BitcoinBank\System_Accounts_Db_Table');
                break;

            case 'accounts':
            default:
                $controller = new Admin_Account_List_Controller();
                $tab_name = 'accounts';
                break;
        }

        $this->set_tab_name($tab_name);
        $this->add_content($controller);

        parent::create_content();
    }


}
