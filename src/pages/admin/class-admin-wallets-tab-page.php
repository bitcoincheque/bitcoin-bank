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

class Admin_Wallets_Tab_Page extends Admin_Tab_Page
{

    public function __construct($name=null, $content = null)
    {
        $nav_tabs = array(
            'hot_wallets' => array(
                'text' => esc_html__('Hot Wallets', 'bitcoin-bank'),
                'headline' => esc_html__('Hot Wallets', 'bitcoin-bank'),
                'link' => admin_url() . 'admin.php?page=bcq-admin-operations&tab=hot_wallets',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'cold_wallets' => array(
                'text' => esc_html__('Cold Wallets', 'bitcoin-bank'),
                'headline' => esc_html__('Cold Wallets', 'bitcoin-bank'),
                'link' => admin_url() . 'admin.php?page=bcq-admin-operations&tab=cold_wallets',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'client_wallets' => array(
                'text' => esc_html__('Client Wallets', 'bitcoin-bank'),
                'headline' => esc_html__('Client Wallets', 'bitcoin-bank'),
                'link' => admin_url() . 'admin.php?page=bcq-admin-operations&tab=client_wallets',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'add_coins' => array(
                'text' => esc_html__('Add Coins', 'bitcoin-bank'),
                'headline' => esc_html__('Add Coins', 'bitcoin-bank'),
                'link' => admin_url() . 'admin.php?page=bcq-admin-operations&tab=add_coins',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'withdraw_coins' => array(
                'text' => esc_html__('Withdraw Coins', 'bitcoin-bank'),
                'headline' => esc_html__('Withdraw Coins', 'bitcoin-bank'),
                'link' => admin_url() . 'admin.php?page=bcq-admin-operations&tab=withdraw_coins',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'transfer_coins' => array(
                'text' => esc_html__('Transfer Coins', 'bitcoin-bank'),
                'headline' => esc_html__('Transfer Coins', 'bitcoin-bank'),
                'link' => admin_url() . 'admin.php?page=bcq-admin-operations&tab=transfer_coins',
            )
        );

        parent::__construct($nav_tabs, $name, $content);
    }

    public function create_content( $config = null ) {
        $tab_name = Security_Filter::safe_read_get_request( 'tab', Security_Filter::STRING_KEY_NAME );

        switch ( $tab_name ) {
            case 'hot_wallets':
                $controller = new Operation_Cheque_Payment_Controller();
                break;

            case 'cold_wallets':
                $controller = new Operation_Payment_Request_Controller();
                break;

            case 'client_wallets':
                $controller = new Operation_Add_Cash_Controller();
                break;

            case 'withdraw_cash':
                $controller = new Operation_Withdraw_Cash_Controller();
                break;

            case 'transfer_cash':
                $controller = new Operation_Transfer_Cash_Controller();
                break;

            case 'account_transfer':
            default:
                $controller = new Operation_Account_Transfer_Controller();
                $tab_name = 'hot_wallets';
                break;
        }

        $this->set_tab_name($tab_name);
        $this->add_content($controller);

        parent::create_content();
    }
}
