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

use WP_PluginFramework\HtmlComponents\Text_Line;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\Status_Bar;

class Transaction_List_View extends Front_Page_View {

	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Text_Line */
	public $transaction_list;
	/** @var Push_Button */
	public $std_submit;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * Profile_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
			parent::__construct( $id, $controller );

		$prop['readonly'] = true;
		$this->transaction_list = new Text_Line( esc_html__( 'Account', 'bitcoin-bank' ), '', 'user-name', $prop );
		$this->register_component( 'transaction_list',  $this->transaction_list);

		/* translators: Button label. */
		$this->std_submit = new Push_Button( esc_html__( 'Load Transaction', 'bitcoin-bank' ) );
		$this->register_component( 'std_submit', $this->std_submit );

		$this->status_bar_footer = new Status_Bar();
		$this->register_component( 'status_bar_footer', $this->status_bar_footer );
	}

	/**
	 * @param null $parameters
	 * @param null $wrapper
	 *
	 * @return void|\WP_PluginFramework\HtmlElements\Div|\WP_PluginFramework\Views\Form_View|\WP_PluginFramework\Views\Std_View|\WP_PluginFramework\Views\View|null
	 */
	public function create_content( $parameters = null, $wrapper = null ) {

		$this->status_bar_header = new Status_Bar();
		$this->add_header('status_bar_header', $this->status_bar_header);

		$this->add_form_input( 'transaction_list',  $this->transaction_list);

		$this->add_button( 'std_submit', $this->std_submit );

		$this->add_footer( 'status_bar_footer', $this->status_bar_footer );

		parent::create_content( $parameters );
	}
}
