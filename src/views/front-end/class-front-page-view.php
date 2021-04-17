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

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Views\Std_View;
use WP_PluginFramework\HtmlComponents\Html_Base_Component;
use WP_PluginFramework\HtmlComponents\Html_Text;
use WP_PluginFramework\HtmlComponents\Check_Box;

class Front_Page_View extends Std_View {

	protected function create_button_styling(  ) {
		$style = '';
		$style_options = get_option( Settings_Style_Options::OPTION_NAME );
		if ( $style_options[ Settings_Style_Options::BUTTON_COLOR ] ) {
			$style = 'background-color:' . $style_options[ Settings_Style_Options::BUTTON_COLOR ] . ';';
		}
		if ( $style_options[ Settings_Style_Options::BUTTON_TEXT_COLOR ] ) {
			$style .= 'color:' . $style_options[ Settings_Style_Options::BUTTON_TEXT_COLOR ] . ';';
		}
		if ( $style_options[ Settings_Style_Options::BUTTON_BORDER_COLOR ] ) {
			$style .= 'border:1px solid ' . $style_options[ Settings_Style_Options::BUTTON_BORDER_COLOR ] . ';';
		}
		return $style;
	}
	/**
	 * Add class attributes to components.
	 *
	 * @param $id @var StatusBar
	 * @param $component Html_Base_Component
	 * @param null                          $properties
	 */
	public function register_component( $id, $component, $properties = null ) {
		if ( is_object( $component ) ) {
			$class                  = get_class( $component );
			$plugin_container       = Plugin_Container::instance();
			$wp_framework_namespace = $plugin_container->get_wp_framework_namespace();
			switch ( $class ) {
				case $wp_framework_namespace . '\HtmlComponents\Push_Button':
					$style = $this->create_button_styling();
					$component->set_property_key_values( 'attributes', 'style', $style );
					break;
			}
		}

		parent::register_component( $id, $component );
	}

	/**
	 * @return Check_Box
	 */
	protected function make_terms_checkbox() {
		$links_options = new Settings_Linking_Options();
		$terms_link    = $links_options->get_complete_link_url( Settings_Linking_Options::TERMS_PAGE );

		/* translators: "terms and condition" link label used by "Yes, I accept the %s" */
		$terms_name = esc_html__( 'terms and condition', 'bitcoin-bank' );
		$terms_href = '<a href="' . $terms_link . '" target="_blank">' . $terms_name . ' </a>';
		/* translators: %s: link label saying terms and condition. */
		$html_text = sprintf( esc_html__( 'Yes, I accept the %s.', 'bitcoin-bank' ), $terms_href );

		$html_content = new Html_Text( $html_text );
		$checkbox     = new Check_Box( $html_content, 0, 'accept_terms' );
		return $checkbox;
	}
}
