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

use WP_PluginFramework\HtmlComponents\Html_Text;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Br;
use WP_PluginFramework\HtmlElements\Strong;

class Logout_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Push_Button */
	public $button_logout;
	/** @var Link */
	public $link;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Logout_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		/* translators: Button label. */
        $this->status_bar_header = new Status_Bar();
		$this->button_logout = new Push_Button( esc_html__( 'Log out', 'bitcoin-bank' ), Push_Button::METHOD_POST );
		$this->status_bar_footer = new Status_Bar();
        parent::__construct( $id, $controller );
    }

	public function create_content( $parameters = null ) {
		$this->set_method( self::SEND_METHOD_POST );

		$this->add_content( $this->status_bar_header );

		$message = new P( esc_html__( 'You are now logged in.', 'bitcoin-bank' ) );
		$message->set_id ('message' );
		$message->add_content( new Br() );

		$current_user = wp_get_current_user();

		$s                  = new Strong( $current_user->user_login );
		$formatted_username = $s->draw_html();
		/* translators: %s: username. */
		$msg  = sprintf( esc_html__( 'Username: %s', 'bitcoin-bank' ), $formatted_username );
		$html = new Html_Text( $msg );
		$message->add_content( $html );

		$this->add_content( $message );

		$this->add_content( $this->button_logout );

		$links_options = new Settings_Linking_Options();
		$url           = $links_options->get_complete_link_url( Settings_Linking_Options::PROFILE_PAGE_LINK );
		/* translators: Link label. */
		$this->link = new Link( $url, esc_html__( 'Update your profile', 'bitcoin-bank' ) );
		$this->add_content( $this->link );

		$this->add_content( $this->status_bar_footer );

        parent::create_content( $parameters );
    }
}
