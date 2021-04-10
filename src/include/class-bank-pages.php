<?php
/** Bitcoin Bank plugin for WordPress.
 *
 *  Copyright (C) 2020 Arild Hegvik
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

use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\Pages\Page;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\Controllers\List_Controller;

class Bank_Pages {

    public static function set_cheque_coockie()
    {
        $receive_cheque_page = trim(Settings_Linking_Options::get_options(Settings_Linking_Options::RECEIVE_CHEQUE_PAGE));

        $request_page = trim($_SERVER['REQUEST_URI']);

        if(strpos ($request_page, $receive_cheque_page) !== false)
        {
            $cheque_id = Security_Filter::safe_read_get_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
            $access_code = Security_Filter::safe_read_get_request('access_code', Security_Filter::STRING_KEY_NAME);

            if($cheque_id and $access_code)
            {
                $cheque_data = new Cheque_Db_Table();
                if ($cheque_data->load_data_id($cheque_id) === 1)
                {
                    $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
                    if ($access_code === $my_access_code)
                    {
                        $state = $cheque_data->get_data(Cheque_Db_Table::STATE);
                        if ($state === Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED)
                        {
                            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;

                            setcookie('bitcoin_bank_cheque_id', strval($cheque_id), time() + 3600, '/', $domain);
                            setcookie('bitcoin_bank_access_code', strval($access_code), time() + 3600, '/', $domain);
                        }
                    }
                }
            }
        }
    }

    public static function client_home() {
        if ( is_user_logged_in() ) {
            $controller = new Client_Details_Controller();
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }

    public static function client_profile() {
        if ( is_user_logged_in() ) {
            $controller = new Client_Profile_Controller();
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }

    public static function account_list() {
        if ( is_user_logged_in() ) {
            $controller = new Account_List_Controller();
            return $controller->draw();
        } else {
            return '';
        }
    }

    public static function account_header() {
        if ( is_user_logged_in() ) {
            $account_id = Security_Filter::safe_read_get_request('account_id', Security_Filter::POSITIVE_INTEGER_ZERO);
            $controller = new Account_Header_Controller( $account_id );
            return $controller->draw();
        } else {
            return '';
        }
    }

    public static function account_deposit() {
        if ( is_user_logged_in() ) {
            $controller = new Client_Profile_Controller();
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }
    public static function account_withdraw() {
        if ( is_user_logged_in() ) {
            $controller = new Client_Profile_Controller();
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }
    public static function transactions_list() {
        if ( is_user_logged_in() ) {
            $account_id = Security_Filter::safe_read_get_request('account_id', Security_Filter::POSITIVE_INTEGER_ZERO);
            $controller = new Account_Transaction_List_Controller( $account_id );
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }
    public static function cheque_list() {
        if ( is_user_logged_in() ) {
            $controller = new Cheque_List_Controller();
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }
    public static function cheque_details() {
        if ( is_user_logged_in() ) {
            $cheque_id = Security_Filter::safe_read_get_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
            $controller = new Cheque_Details_Controller( $cheque_id );
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }
    public static function cheque_send() {
        if ( is_user_logged_in() ) {
            $cheque_id = Security_Filter::safe_read_get_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
            $page = new Page();
            if($cheque_id)
            {
                $controller1 = new Cheque_Details_Controller($cheque_id);
                $page->add_content($controller1);
                $page->add_content(new H(2, 'Previously sent cheques'));
                $controller2 = new Cheque_Sent_List_Controller();
                $page->add_content($controller2);
            }
            else
            {
                //$controller1 = new Account_Header_Controller();
                //$page->add_content($controller1);

                $controller2 = new Cheque_Create_Controller();
                $page->add_content($controller2);

                $page->add_content(new H(3, 'Previously sent cheques'));
                $controller3 = new Cheque_Sent_List_Controller();
                $page->add_content($controller3);
            }
            return $page->draw();
        } else {
            $controller = new Login_Controller();
        }
        return $controller->draw();
    }
    public static function cheque_receive() {
        $html = '';
        $cheque_id = Security_Filter::safe_read_get_request('cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO);
        $access_code = Security_Filter::safe_read_get_request('access_code', Security_Filter::STRING_KEY_NAME);

        if ( is_user_logged_in() ) {
            $page = new Page();
            if($cheque_id and $access_code) {
                $page->add_content(new H(3, 'Previously received cheques'));
                $controller1 = new Cheque_Receive_Controller($cheque_id, $access_code);
                $page->add_content($controller1);
            } else {
                $page->add_content(new H(3, 'Look up cheque'));
                $page->add_content(new P("Enter the cheque's serial number and access code. Only for cheques issued by this bank."));
                $controller1 = new Cheque_Lookup_Controller($cheque_id, $access_code);;
                $page->add_content($controller1);
            }
            $page->add_content(new H(3, 'Previously received cheques'));
            $controller2 = new Cheque_Received_List_Controller();
            $page->add_content($controller2);
            return $page->draw();
        } else {
            if($cheque_id and $access_code)
            {
                $html = Cheque_Handler::draw_cheque_picture($cheque_id, $access_code);
            }
            $controller = new Login_Controller();
        }

        return $html . $controller->draw();
    }

	/**
	 * @return string
	 */
	public static function membership_login() {
		$controller = new Login_Controller();

		return $controller->draw();

	}

	/**
	 * @return string
	 */
	public static function profile_page() {
		$html = '';
		if ( is_user_logged_in() ) {
			$controller = new Profile_Controller();
		} else {
			$html      .= '<p>' . esc_html__( 'You must log in to access your user profile.', 'bitcoin-bank' ) . '</p>';
			$controller = new Login_Controller();
		}

		$html .= $controller->draw();
		return $html;
	}

	/**
	 * @return string
	 */
	public static function password_page() {
		if ( is_user_logged_in() ) {
			$controller = new Password_Change_Controller();
		} else {
			$controller = new Password_Recovery_Controller();
		}

		return $controller->draw();
	}

    /**
     * @return string
     */
    public static function create_cheque() {
        if ( is_user_logged_in() ) {
            $controller = new Cheque_Write_Controller();
        } else {
            $controller = new Cheque_Write_Controller();
        }

        return $controller->draw();
    }

    public static function list_transactions() {
        if ( is_user_logged_in() ) {
            $controller = new List_Controller('BCQ_BitcoinBank\Transactions_Db_Table');
        } else {
            $controller = new List_Controller('BCQ_BitcoinBank\Transactions_Db_Table');
        }

        return $controller->draw();
    }

    /**
	 *
	 */
	public static function schedule_event() {
		Debug_Logger::write_debug_note( 'ScheduleEvent.' );

		$clear_timeout    = 30 * 24 * 60 * 60;  // Timeout for when the record will be removed from database
		$register_timeout = 2 * 24 * 60 * 60;  // Timeout for valid registration link
		$password_timeout = 60 * 60;  // Timeout for valid password link

		$reg_data = new Registration_Db_Table();
		$count    = $reg_data->load_all_data();

		$pending_approval = 0;

		while ( $count > 0 ) {
			$state         = $reg_data->get_data_index( 0, Registration_Db_Table::STATE );
			$timestamp_obj = $reg_data->get_data_object( Registration_Db_Table::TIMESTAMP );
			$timestamp     = $timestamp_obj->get_value_seconds();

			$now = time();

			$deleted = false;

			if ( ( $timestamp + $clear_timeout ) < $now ) {
				if ( Registration_Db_Table::STATE_APPROVAL_PENDING === $state ) {
					$reg_data->fetch_data();
				} else {
					$reg_data->delete();
				}
			} else {
				switch ( $state ) {
					case Registration_Db_Table::STATE_REGISTRATION_STARTED:
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
					case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
					case Registration_Db_Table::STATE_REGISTRATION_MORE_INFO:
						if ( ( $timestamp + $register_timeout ) < $now ) {
							$reg_data->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_REGISTRATION_EXPIRED );
							$reg_data->save_data();
							$id = $reg_data->get_data( Registration_Db_Table::PRIMARY_KEY );
							Debug_Logger::write_debug_note( 'Expired registration data record for id=', $id );
						}
						break;

					case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
					case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
						if ( ( $timestamp + $password_timeout ) < $now ) {
							$reg_data->set_data( Registration_Db_Table::STATE, Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED );
							$reg_data->save_data();
							$id = $reg_data->get_data( Registration_Db_Table::PRIMARY_KEY );
							Debug_Logger::write_debug_note( 'Expired password registration data record for id=', $id );
						}
						break;

					case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
					case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
					case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
					case Registration_Db_Table::STATE_REGISTRATION_EXPIRED:
					case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
					case Registration_Db_Table::STATE_APPROVAL_DECLINED:
						break;

					case Registration_Db_Table::STATE_APPROVAL_PENDING:
						$pending_approval++;
						break;

					default:
						$reg_id = $reg_data->get_data( Registration_Db_Table::PRIMARY_KEY );
						Debug_Logger::write_debug_error( 'Missing state handler for ' . $state . '. Delete reg_id ' . $reg_id );
						$reg_data->delete();
						$deleted = true;
						break;

				}

				if ( ! $deleted ) {
					$reg_data->fetch_data();
				}
			}
			$count--;
		}

		Registration_Db_Table::set_pending_approval_counter( $pending_approval );
	}

	/**
	 * @return bool|false|string
	 */
	public static function get_redirect_logout() {
		$linking_options = new Settings_Linking_Options();
		$url             = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGOUT_PAGE_REDIRECT, true );
		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|mixed|string
	 */
	public static function redirect_logout( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGOUT_PAGE_REDIRECT, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		} else {
			$url = $_SERVER['HTTP_REFERER'];
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	public static function redirect_register( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::REGISTER_PAGE_LINK, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	public static function redirect_login( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::LOGIN_PAGE_LINK, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	public static function redirect_lost_password( $url ) {
		$linking_options = new Settings_Linking_Options();
		$set_url         = $linking_options->get_complete_link_url( Settings_Linking_Options::PASSWORD_PAGE_LINK, true );

		if ( false !== $set_url ) {
			$url = $set_url;
		}

		return $url;
	}
}
