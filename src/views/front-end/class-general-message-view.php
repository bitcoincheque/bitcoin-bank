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

use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\H;


class General_Message_View extends Front_Page_View {

	/** @var H */
	public $header;
	/** @var Status_Bar */
	public $status_bar_header;
	/** @var Status_Bar */
	public $status_bar_footer;

	/**
	 * General_Message_View constructor.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$this->header = new H( 3 );
		$this->status_bar_header = new Status_Bar();
		$this->status_bar_footer = new Status_Bar();

		$this->register_component( 'header', $this->header );
		$this->register_component( 'status_bar_header', $this->status_bar_header );
		$this->register_component( 'status_bar_footer', $this->status_bar_footer );

	}

	public function create_content( $parameters = null ) {
		$this->add_header( 'header', $this->header );
		$this->add_header( 'status_bar_header', $this->status_bar_header );
		$this->add_footer( 'status_bar_footer', $this->status_bar_footer );
		parent::create_content( $parameters );
	}
}
