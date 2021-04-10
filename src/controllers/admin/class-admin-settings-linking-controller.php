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
class Admin_Settings_Linking_Controller extends Admin_Controller {

	/**
	 * Construction.
	 */
	public function __construct() {
		parent::__construct( 'BCQ_BitcoinBank\Settings_Linking_Options', 'BCQ_BitcoinBank\Admin_Linking_View' );

		$this->tab_name = 'linking';
	}

	protected function load_model_values( $values = array() ) {
		$value                               = parent::load_model_values( $values = array() );
		$value_renamed['register_page_link'] = $value[ Settings_Linking_Options::REGISTER_PAGE_LINK ];
		$value_renamed['login_page_link']    = $value[ Settings_Linking_Options::LOGIN_PAGE_LINK ];
		$value_renamed['profile_page_link']  = $value[ Settings_Linking_Options::PROFILE_PAGE_LINK ];
		$value_renamed['password_page_link'] = $value[ Settings_Linking_Options::PASSWORD_PAGE_LINK ];
		$value_renamed['terms_page']         = $value[ Settings_Linking_Options::TERMS_PAGE ];
		$value_renamed['logout_page']        = $value[ Settings_Linking_Options::LOGOUT_PAGE_REDIRECT ];
        $value_renamed['login_redirect_page']        = $value[ Settings_Linking_Options::LOGIN_PAGE_REDIRECT ];
        $value_renamed['account_list_page']        = $value[ Settings_Linking_Options::ACCOUNT_LIST_PAGE ];
        $value_renamed['transaction_page']        = $value[ Settings_Linking_Options::TRANSACTION_PAGE ];
        $value_renamed['cheque_details_page']        = $value[ Settings_Linking_Options::CHEQUE_DETAILS_PAGE ];
        $value_renamed['send_cheque_page']        = $value[ Settings_Linking_Options::SEND_CHEQUE_PAGE ];
        $value_renamed['receive_cheque_page']        = $value[ Settings_Linking_Options::RECEIVE_CHEQUE_PAGE ];
		return $value_renamed;
	}

	public function std_submit_click() {
		$data_record = $this->view->get_values();

		$data_record_renamed[ Settings_Linking_Options::REGISTER_PAGE_LINK ]   = $data_record['register_page_link'];
		$data_record_renamed[ Settings_Linking_Options::LOGIN_PAGE_LINK ]      = $data_record['login_page_link'];
		$data_record_renamed[ Settings_Linking_Options::PROFILE_PAGE_LINK ]    = $data_record['profile_page_link'];
		$data_record_renamed[ Settings_Linking_Options::PASSWORD_PAGE_LINK ]   = $data_record['password_page_link'];
		$data_record_renamed[ Settings_Linking_Options::TERMS_PAGE ]           = $data_record['terms_page'];
		$data_record_renamed[ Settings_Linking_Options::LOGOUT_PAGE_REDIRECT ] = $data_record['logout_page'];
        $data_record_renamed[ Settings_Linking_Options::LOGIN_PAGE_REDIRECT ]  = $data_record['login_redirect_page'];

        $data_record_renamed[ Settings_Linking_Options::ACCOUNT_LIST_PAGE ]   = $data_record['account_list_page'];
        $data_record_renamed[ Settings_Linking_Options::TRANSACTION_PAGE ]      = $data_record['transaction_page'];
        $data_record_renamed[ Settings_Linking_Options::CHEQUE_DETAILS_PAGE ]    = $data_record['cheque_details_page'];
        $data_record_renamed[ Settings_Linking_Options::SEND_CHEQUE_PAGE ]   = $data_record['send_cheque_page'];
        $data_record_renamed[ Settings_Linking_Options::RECEIVE_CHEQUE_PAGE ]           = $data_record['receive_cheque_page'];

		if ( $this->model->validate_data_record( $data_record_renamed ) ) {
			$this->handle_validation_success( $data_record_renamed );
		} else {
			$this->handle_validation_errors( $data_record_renamed );
		}
	}
}
