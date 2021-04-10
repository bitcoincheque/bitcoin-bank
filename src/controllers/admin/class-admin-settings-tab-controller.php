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

use WP_PluginFramework\Controllers\Admin_Controller;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Settings_Tab_Controller extends Admin_Controller {

	protected $tab_name;

	/**
	 * @param $values
	 */
	protected function init_view( $values ) {
		$this->view->set_tab_name( $this->tab_name );

		/* translators: Admin tab menu. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'forms',
				'text' => esc_html__( 'Forms', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=forms',
			)
		);
		/* translators: Admin tab menu. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'style',
				'text' => esc_html__( 'Style', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=style',
			)
		);
		/* translators: Admin tab menu. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'email',
				'text' => esc_html__( 'E-mail', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=email',
			)
		);
		/* translators: Admin tab menu. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'linking',
				'text' => esc_html__( 'Page linking', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=linking',
			)
		);
		/* translators: Admin tab menu. Abbreviation for Search Engine Optimization. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'access',
				'text' => esc_html__( 'Access', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=access',
			)
		);
		/* translators: Admin tab menu. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'security',
				'text' => esc_html__( 'Security', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=security',
			)
		);
		/* translators: Admin tab menu. Limited space, keep translation short. */
		$this->view->add_nav_tab(
			array(
				'name' => 'advanced',
				'text' => esc_html__( 'Advanced', 'bitcoin-bank' ),
				'link' => admin_url() . 'admin.php?page=bcq-admin-menu&tab=advanced',
			)
		);

		parent::init_view( $values );
	}
}
