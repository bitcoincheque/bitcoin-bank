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

class Login_Redirect_Controller extends Login_Controller
{

    protected function draw_view( $parameters = null )
    {
        $redirect_url = null;

        $cheque_id = filter_input(INPUT_COOKIE, 'bitcoin_bank_cheque_id');
        $cheque_id = intval($cheque_id);
        if($cheque_id > 0)
        {
            $access_code = filter_input(INPUT_COOKIE, 'bitcoin_bank_access_code');
            if($access_code) {

                $cheque_data = new Cheque_Db_Table();
                if($cheque_data->load_data_id($cheque_id) === 1)
                {
                    $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
                    if($access_code === $my_access_code) {
                        $state = $cheque_data->get_data(Cheque_Db_Table::STATE);
                        if($state === Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED) {
                            $link_data = new Settings_Linking_Options();
                            $redirect_url = $link_data->get_complete_link_url(Settings_Linking_Options::RECEIVE_CHEQUE_PAGE);
                            $redirect_url .= '?cheque_id=' . strval($cheque_id) . '&access_code=' . $access_code;
                        }
                    }
                }
            }
        }

        if(!$redirect_url) {
            $link_data = new Settings_Linking_Options();
            $redirect_url = $link_data->get_complete_link_url(Settings_Linking_Options::LOGIN_PAGE_REDIRECT);
        }

        if($redirect_url)
        {
            $this->view->add_hidden_fields('login_redirect', $redirect_url);
        }

        return parent::draw_view();
    }
}
