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

use WP_PluginFramework\Models\Option_Model;

/**
 * Summary.
 *
 * Description.
 */
class Settings_Style_Options extends Option_Model {

	const OPTION_NAME = 'bcq_bitcoin_bank_style_options';

	const FADING_ENABLE          = 'fade_enable';
	const FADING_HEIGHT          = 'fading_height';
	const FADE_BACKGROUND_COLOR  = 'fade_background_color';
	const FRAME_BACKGROUND_COLOR = 'frame_background_color';
	const FRAME_BORDER_SIZE      = 'frame_border_size';
	const FRAME_BORDER_COLOR     = 'frame_border_color';
	const BUTTON_COLOR           = 'button_color';
	const BUTTON_TEXT_COLOR      = 'button_text_color';
	const BUTTON_BORDER_COLOR    = 'button_border_color';
	const FORM_LOADING           = 'form_loading';
	const TEXT_LOADING           = 'text_loading';
	const OVERRIDE_CSS           = 'override_css';
	const CSS_FILE               = 'css_file';

	/* The form loading constants is also name of JavaScript to be called for animation. */
	const FORM_LOADING_NO_ANIMATION = 'no_animations';
	const FORM_LOADING_FADE         = 'bcq_fade_in_out';
	const FORM_LOADING_V_ROLLING    = 'bcq_roll_down_up';

	/* The form loading constants is also name of JavaScript to be called for animation. */
	const TEXT_LOADING_NO_ANIMATION = 'no_animations';
	const TEXT_LOADING_FADE         = 'bcq_fade_in_out';
	const TEXT_LOADING_V_ROLLING    = 'bcq_roll_down_up';
	const TEXT_LOADING_RELOAD_PAGE = 'bcq_reload_page';

	const CSS_USE_PLUGIN_CSS = 'css_use_plugin_css';
	const CSS_USE_THEME_CSS  = 'css_use_theme_css';
	const CSS_USE_CUSTOM_CSS = 'css_use_custom_css';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::FADING_ENABLE          => array(
			'data_type'     => 'Boolean_Type',
			'default_value' => '1',
		),
		self::FADING_HEIGHT          => array(
			'data_type'     => 'Integer_Type',
			'default_value' => 10,
		),
		self::FADE_BACKGROUND_COLOR  => array(
			'data_type'     => 'String_Type',
			'default_value' => '#ffffff',
		),
		self::FRAME_BACKGROUND_COLOR => array(
			'data_type'     => 'String_Type',
			'default_value' => '#eeeeee',
		),
		self::FRAME_BORDER_SIZE      => array(
			'data_type'     => 'Integer_Type',
			'default_value' => 5,
		),
		self::FRAME_BORDER_COLOR     => array(
			'data_type'     => 'String_Type',
			'default_value' => '#cce6ff',
		),
		self::BUTTON_TEXT_COLOR      => array(
			'data_type'     => 'String_Type',
			'default_value' => '#ffffff',
		),
		self::BUTTON_BORDER_COLOR    => array(
			'data_type'     => 'String_Type',
			'default_value' => '#3079ed',
		),
		self::BUTTON_COLOR           => array(
			'data_type'     => 'String_Type',
			'default_value' => '#4D90FE',
		),
		self::FORM_LOADING           => array(
			'data_type'     => 'String_Type',
			'default_value' => self::FORM_LOADING_V_ROLLING,
		),
		self::TEXT_LOADING           => array(
			'data_type'     => 'String_Type',
			'default_value' => self::TEXT_LOADING_V_ROLLING,
		),
		self::OVERRIDE_CSS           => array(
			'data_type'     => 'String_Type',
			'default_value' => self::CSS_USE_PLUGIN_CSS,
		),
		self::CSS_FILE               => array(
			'data_type'     => 'String_Type',
			'default_value' => 'CSS defaults...',
		),
	);
}
