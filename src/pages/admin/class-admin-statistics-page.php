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

use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\Pages\Admin_Page;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\Status_Bar;

class Admin_Statistics_Page extends Admin_Page {

    public function __construct() {
        $properties = array(
            /* translators: Name of the plugin, should only be translated if done consistently. */
            'headline' => esc_html__( 'Read More Login', 'bitcoin-bank' ) . ' - ' . get_admin_page_title()
        );
        parent::__construct( null, $properties );
    }

    public function create_content( $config = null ) {
        $this->add_content(new P( esc_html__( 'Statistics for user registrations.', 'bitcoin-bank' )));
        $this->add_content(new P( esc_html__( 'These numbers applies only for the registration forms inside articles.', 'bitcoin-bank' )));

        $stat_data = new Statistics_Db_Table();
        $count     = $stat_data->load_all_data();

        $attributes = array ('border' => '1');
        $table = new Table(null, $attributes);

        $tr = new Tr();
        $tr->add_content(new Th( esc_html__( 'Page ID', 'bitcoin-bank' )));
        $tr->add_content(new Th( esc_html__( 'Registering page', 'bitcoin-bank' )));
        $tr->add_content(new Th( esc_html__( 'Page view counter', 'bitcoin-bank' )));

        $th_attrib = array('colspan' => '2');
        $th = new Th(esc_html__( 'Registration started counter', 'bitcoin-bank' ), $th_attrib);
        $tr->add_content($th);
        $th = new Th(esc_html__( 'Sent e-mail counter', 'bitcoin-bank' ), $th_attrib);
        $tr->add_content($th);
        $th = new Th(esc_html__( 'Completed counter', 'bitcoin-bank' ), $th_attrib);
        $tr->add_content($th);

        $table->add_content($tr);

        $this->add_content($table);

        $page_views_totals_count = 0;
        $registered_totals_count = 0;
        $email_totals_sent       = 0;
        $completed_totals_count  = 0;

        for ( $index = 0; $index < $count; $index++ ) {
            $post_id          = $stat_data->get_data_index( $index, Statistics_Db_Table::POST_ID );
            $page_views_count = $stat_data->get_data_index( $index, Statistics_Db_Table::PAGE_VIEW );
            $registered_count = $stat_data->get_data_index( $index, Statistics_Db_Table::REGISTER );
            $email_sent       = $stat_data->get_data_index( $index, Statistics_Db_Table::VERIFY );
            $completed_count  = $stat_data->get_data_index( $index, Statistics_Db_Table::COMPLETED );

            $page_views_totals_count += $page_views_count;
            $registered_totals_count += $registered_count;
            $email_totals_sent       += $email_sent;
            $completed_totals_count  += $completed_count;

            if ( 0 === $page_views_count ) {
                $registered_percent = '0.0%';
                $email_percent      = '0.0%';
                $completed_percent  = '0.0%';
            } else {
                $registered_percent = number_format( 100.0 * $registered_count / $page_views_count, 1 ) . '%';
                $email_percent      = number_format( 100.0 * $email_sent / $page_views_count, 1 ) . '%';
                $completed_percent  = number_format( 100.0 * $completed_count / $page_views_count, 1 ) . '%';
            }

            $tr = new Tr();
            $tr->add_content(new Td( $post_id ) );
            $tr->add_content(new Td( get_the_title( $post_id ) ) );
            $tr->add_content(new Td( $page_views_count ) );
            $tr->add_content(new Td( $registered_count ) );
            $tr->add_content(new Td( $registered_percent ) );
            $tr->add_content(new Td( $email_sent ) );
            $tr->add_content(new Td( $email_percent ) );
            $tr->add_content(new Td( $completed_count ) );
            $tr->add_content(new Td( $completed_percent ) );
            $table->add_content($tr);
        }

        if ( 0 === $page_views_totals_count ) {
            $registered_total_percent = '0.0%';
            $email_total_percent      = '0.0%';
            $completed_total_percent  = '0.0%';
        } else {
            $registered_total_percent = number_format( 100.0 * $registered_totals_count / $page_views_totals_count, 1 ) . '%';
            $email_total_percent      = number_format( 100.0 * $email_totals_sent / $page_views_totals_count, 1 ) . '%';
            $completed_total_percent  = number_format( 100.0 * $completed_totals_count / $page_views_totals_count, 1 ) . '%';
        }

        $tr = new Tr();
        /* translators: This is sum of adding calculation. */
        $tr->add_content(new Td( esc_html__( 'Totals', 'bitcoin-bank' ) ) );
        $tr->add_content(new Td(  ) );
        $tr->add_content(new Td( $page_views_totals_count ) );
        $tr->add_content(new Td( $registered_totals_count ) );
        $tr->add_content(new Td( $registered_total_percent ) );
        $tr->add_content(new Td( $email_totals_sent ) );
        $tr->add_content(new Td( $email_total_percent ) );
        $tr->add_content(new Td( $completed_totals_count ) );
        $tr->add_content(new Td( $completed_total_percent ) );
        $table->add_content($tr);

        parent::create_content();
    }

}
