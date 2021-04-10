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

class Compact_Widget {

	/**
	 * @return string
	 */
	public static function draw_compact_widget() {
		$show_name = '';

		if ( is_user_logged_in() ) {
			$wp_user_id = get_current_user_id();
			$user_info  = get_userdata( $wp_user_id );
			$username   = $user_info->user_login;
			$first_name = $user_info->first_name;
			$last_name  = $user_info->last_name;

			if ( ( '' === $first_name ) && ( '' === $last_name ) ) {
				$show_name = $username;
			} else {
				$show_name = $first_name . ' ' . $last_name;
			}

			$login_widget_style  = 'style="display:none;"';
			$logout_widget_style = ' ';
		} else {
			$login_widget_style  = '';
			$logout_widget_style = ' style="display:none;"';
		}

		$output = '<div class="bcq_compact_widget">';

		$links_options   = new Settings_Linking_Options();
		$profile_url     = $links_options->get_complete_link_url( Settings_Linking_Options::PROFILE_PAGE_LINK );
		$linking_options = new Settings_Linking_Options();
		$register_url    = $linking_options->get_register_url();
		$login_url       = $linking_options->get_login_url();

		/* translators: Link text for compact widget. Translation should be short. */
		$login_txt = esc_html__( 'Login', 'bitcoin-bank' );
		/* translators: Link text for compact widget. Translation should be short. */
		$register_txt = esc_html__( 'Register', 'bitcoin-bank' );
		/* translators: Link text for compact widget. Translation should be short. */
		$logout_txt = esc_html__( 'Logout', 'bitcoin-bank' );
		/* translators: Link text for compact widget. Translation should be short. */
		$profile_txt = esc_html__( 'Profile', 'bitcoin-bank' );

		$output .= '<div class="bcq_login_widget" ' . $logout_widget_style . '><span class="bcq_show_name">' . $show_name . '</span> <a class="bcq_logout_href" href="' . wp_logout_url() . ' ">' . $logout_txt . '</a> <a href="' . $profile_url . '">' . $profile_txt . '</a></div>';
		$output .= '<div class="bcq_logout_widget" ' . $login_widget_style . '><a href="' . $login_url . '">' . $login_txt . '</a> <a href="' . $register_url . '">' . $register_txt . '</a></div>';
		$output .= '</div>';

		return $output;
	}
}
