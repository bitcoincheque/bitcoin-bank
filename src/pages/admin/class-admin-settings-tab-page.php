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

use WP_PluginFramework\Controllers\Std_Controller;
use WP_PluginFramework\Controllers\Std_Option_Controller;
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

class Admin_Settings_Tab_Page extends Admin_Tab_Page {

    public function __construct( $name=null, $content=null ) {
        $nav_tabs = array(
            'identity' => array(
                'name' => 'identity',
                'text' => esc_html__( 'Identity', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Identity settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=identity',
            ),
            'currency' => array(
                'name' => 'identity',
                'text' => esc_html__( 'Currency', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Currency settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=currency',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'email' => array(
                'name' => 'email',
                'text' => esc_html__( 'E-mail', 'bitcoin-bank' ),
                'headline' => esc_html__( 'E-mail settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=email',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'linking' => array(
                'name' => 'linking',
                'text' => esc_html__( 'Page linking', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Page linking settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=linking',
            ),
            /* translators: Admin tab menu. Abbreviation for Search Engine Optimization. Limited space, keep translation short. */
            'access' => array(
                'name' => 'access',
                'text' => esc_html__( 'Access', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Access settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=access',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'security' => array(
                'name' => 'security',
                'text' => esc_html__( 'Security', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Security settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=security',
            ),
            /* translators: Admin tab menu. Limited space, keep translation short. */
            'advanced' => array(
                'name' => 'advanced',
                'text' => esc_html__( 'Advanced', 'bitcoin-bank' ),
                'headline' => esc_html__( 'Advanced settings', 'bitcoin-bank' ),
                'link' => admin_url() . 'admin.php?page=bcq-admin-settings&tab=advanced',
            )
        );

        parent::__construct( $nav_tabs, $name, $content );
    }

    public function create_content( $config = null ) {
        $tab_name = Security_Filter::safe_read_get_request( 'tab', Security_Filter::STRING_KEY_NAME );

        switch ( $tab_name ) {
            case 'identity':
                $controller = new Admin_Settings_Bank_Identity_Controller();
                break;

            case 'currency':
                $controller = new Admin_Settings_Currency_Controller();
                break;

            case 'linking':
                $controller = new Admin_Settings_Linking_Controller();
                break;

            case 'email':
                $controller = new Admin_Settings_Email_Controller();
                break;

            case 'access':
                $controller = new Admin_Settings_Access_Controller();
                break;

            case 'security':
                $controller = new Admin_Settings_Security_Controller();
                break;

            case 'advanced':
                $controller = new Admin_Settings_Advanced_Controller();
                break;

            default:
                $controller = new Admin_Settings_Bank_Identity_Controller();
                $tab_name = 'identity';
                break;
        }

        $this->set_tab_name($tab_name);
        $this->add_content($controller);

        switch($this->my_tab_name) {
            case 'forms':
            case 'style':
            case 'email':
                /* Add some space before the table */
                $this->add_content( new P() );
                $table = $this->get_variable_descriptions( true );
                $this->add_content( $table );
                break;

            default:
                breaK;
        }

        parent::create_content();
    }

    static public function get_variable_descriptions( $is_form ) {
        $table_attr = array( 'class' => 'bcq_admin_table' );
        $table      = new Table( null, $table_attr );
        $tr         = new Tr();
        $tr->add_content( new Th( 'Variables' ) );
        $tr->add_content( new Th( 'Description' ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%username%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in the user\'s username. This variable is not available before the username has been given during the registration.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%first_name%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in user\'s first name. If not given it will write blank.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%last_name%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in user\'s last name. If not given it will write blank.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%name%' ) );
        $tr->add_content( new Td( esc_html__( 'Depending of what is available the variable will put in the user\'s first name and/or the last name. If no name has been given, variable will write the user\'s username.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%email%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in the user\'s e-mail address.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%title%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in the tittle of page the user are registering on.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%site_name%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in the name of the site.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        $tr = new Tr();
        $tr->add_content( new Td( '%site_url%' ) );
        $tr->add_content( new Td( esc_html__( 'Variable will put in the link to the site.', 'bitcoin-bank' ) ) );
        $table->add_content( $tr );

        if ( ! $is_form ) {
            $tr = new Tr();
            $tr->add_content( new Td( '%link%' ) );
            $tr->add_content( new Td( esc_html__( 'Depending on type of e-mail this variable will put in link to read the remaining article or to complete the registration or the password change page.', 'bitcoin-bank' ) ) );
            $table->add_content( $tr );
        }

        return $table;
    }
}
