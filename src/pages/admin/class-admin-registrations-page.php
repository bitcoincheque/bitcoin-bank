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

use WP_PluginFramework\HtmlElements\Input_Hidden;
use WP_PluginFramework\Pages\Admin_Page;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\HtmlElements\Button;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlComponents\Status_Bar;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Registrations_Page {


    static $user_handling_result     = null;
    static $user_handling_result_msg = null;

    public static function show_registrations_page() {
        $status_bar_html = '';
        if ( isset( self::$user_handling_result ) ) {
            $status_bar = new Status_Bar( Status_Bar::TYPE_REMOVABLE_BLOCK );
            $status_bar->set_id( '0' );
            if ( self::$user_handling_result ) {
                $status_bar->set_status_text( self::$user_handling_result_msg, Status_Bar::STATUS_SUCCESS );
            } else {
                $status_bar->set_status_text( self::$user_handling_result_msg, Status_Bar::STATUS_ERROR );
            }
            $status_bar->create_content();
            $status_bar_html = $status_bar->draw_html();
        }

        echo '<div class="wrap">';
        /* translators: Name of the plugin, should only be translated if done consistently. */
        echo '<h1 class="wp-heading-inline">' . esc_html__( 'Bitcoin Bank', 'bitcoin-bank' ) . ' - ' . get_admin_page_title() . '</h1>';
        echo $status_bar_html;
        echo '<hr class="wp-header-end">';
        echo '<p>' . esc_html__( 'Show status for user registrations and password recovery.', 'bitcoin-bank' ) . '</p>';

        $reg_data = new Registration_Db_Table();
        $count    = $reg_data->load_all_data();

        $all_count              = 0;
        $registering_count      = 0;
        $pending_approval_count = 0;
        $approved_count         = 0;
        $declined_count         = 0;
        $expired_count          = 0;
        $password_count         = 0;
        for ( $index = 0; $index < $count; $index++ ) {
            $state      = $reg_data->get_data_index( $index, Registration_Db_Table::STATE );
            $wp_user_id = $reg_data->get_data_index( $index, Registration_Db_Table::WP_USER_ID );

            if ( self::show_registration_item( $state, 'all', $wp_user_id ) ) {
                $all_count += 1;
            }

            if ( self::show_registration_item( $state, 'registering', $wp_user_id ) ) {
                $registering_count += 1;
            }

            if ( self::show_registration_item( $state, 'pending_approval', $wp_user_id ) ) {
                $pending_approval_count += 1;
            }

            if ( self::show_registration_item( $state, 'approved', $wp_user_id ) ) {
                $approved_count += 1;
            }

            if ( self::show_registration_item( $state, 'declined', $wp_user_id ) ) {
                $declined_count += 1;
            }

            if ( self::show_registration_item( $state, 'expired', $wp_user_id ) ) {
                $expired_count += 1;
            }

            if ( self::show_registration_item( $state, 'password', $wp_user_id ) ) {
                $password_count += 1;
            }
        }

        $current_register_status = Security_Filter::safe_read_get_request( 'register-status', Security_Filter::STRING_KEY_NAME );
        if ( ! isset( $current_register_status ) ) {
            if ( 0 === $pending_approval_count ) {
                $current_register_status = 'all';
            } else {
                $current_register_status = 'pending_approval';
            }
        }

        echo '<p>';
        echo self::create_table_link( 'All', $all_count, 'all', $current_register_status ) . ' | ';
        echo self::create_table_link( 'Registering', $registering_count, 'registering', $current_register_status ) . ' | ';
        echo self::create_table_link( 'Pending approval', $pending_approval_count, 'pending_approval', $current_register_status ) . ' | ';
        echo self::create_table_link( 'Approved', $approved_count, 'approved', $current_register_status ) . ' | ';
        echo self::create_table_link( 'Declined', $declined_count, 'declined', $current_register_status ) . ' | ';
        echo self::create_table_link( 'Expired', $expired_count, 'expired', $current_register_status ) . ' | ';
        echo self::create_table_link( 'Password recovery', $password_count, 'password_recovery', $current_register_status );
        echo '</p>';

        if ( 'password_recovery' !== $current_register_status ) {
            $local_timestamp_offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
            $local_time_format      = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

            echo '<table class="wp-list-table widefat fixed striped paged">';
            echo '<thead>';
            echo '<tr>';
            /* translators: Database reference number for registrations. */
            echo '<th scope="col" id="timestamp" class="manage-column column-primary">' . esc_html__( 'Time-stamp', 'bitcoin-bank' ) . '</th>';
            echo '<th scope="col" id="status" class="manage-column">' . esc_html__( 'Status', 'bitcoin-bank' ) . '</th>';
            echo '<th scope="col" id="username" class="manage-column">' . esc_html__( 'Username', 'bitcoin-bank' ) . '</th>';
            echo '<th scope="col" id="email" class="manage-column">' . esc_html__( 'E-mail', 'bitcoin-bank' ) . '</th>';
            $form_options = get_option( Settings_Form_Options::OPTION_NAME );
            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
                echo '<th scope="col" id="firstname" class="manage-column">' . esc_html__( 'First name', 'bitcoin-bank' ) . '</th>';
            }
            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
                echo '<th scope="col" id="lastname" class="manage-column">' . esc_html__( 'Last name', 'bitcoin-bank' ) . '</th>';
            }
            echo '<th scope="col" id="pagetitle" class="manage-column">' . esc_html__( 'Page title', 'bitcoin-bank' ) . '</th>';
            echo '<th  scope="col" id="actions" class="manage-column" colspan="2">' . esc_html__( 'Actions', 'bitcoin-bank' ) . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody id="the-list">';

            $any = false;

            for ( $index = 0; $index < $count; $index++ ) {
                $reg_id     = $reg_data->get_data_index( $index, Registration_Db_Table::PRIMARY_KEY );
                $state      = $reg_data->get_data_index( $index, Registration_Db_Table::STATE );
                $secret     = $reg_data->get_data_index( $index, Registration_Db_Table::SECRET );
                $username   = $reg_data->get_data_index( $index, Registration_Db_Table::USERNAME );
                $wp_user_id = $reg_data->get_data_index( $index, Registration_Db_Table::WP_USER_ID );

                if ( self::show_registration_item( $state, $current_register_status, $wp_user_id ) ) {
                    $post_id = $reg_data->get_data_index( $index, Registration_Db_Table::POST_ID );
                    if ( current_user_can( 'edit_pages' ) ) {
                        $link = admin_url() . 'post.php?post=' . $post_id . '&action=edit';
                    } else {
                        $link = get_permalink( $post_id );
                    }
                    $registering_page_link = '<a href="' . $link . '">' . get_the_title( $post_id ) . '</a>';

                    $action_approve  = '';
                    $actions_decline = '';
                    switch ( $state ) {
                        case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
                            if ( current_user_can( 'list_users' ) ) {
                                if ( get_userdata( $wp_user_id ) !== false ) {
                                    $edit_user_link = admin_url() . 'user-edit.php?user_id=' . $wp_user_id;
                                    $username       = '<a href="' . $edit_user_link . '">' . $username . '</a>';
                                }
                            }
                            break;

                        case Registration_Db_Table::STATE_APPROVAL_PENDING:
                        case Registration_Db_Table::STATE_APPROVAL_DECLINED:
                            $button_attr['class'] = 'button button-primary';
                            $button_attr['type']  = 'submit';
                            $button_attr['value'] = 'Submit';
                            $refresh_button       = new Button( esc_html__( 'Approve', 'bitcoin-bank' ), $button_attr );

                            $actions_form_attr['method'] = 'get';
                            $actions_form                = new Form( $refresh_button, $actions_form_attr );

                            $hidden_attr['name']  = 'register-status';
                            $hidden_attr['value'] = $current_register_status;
                            $hidden               = new Input_Hidden( $hidden_attr );
                            $actions_form->add_content( $hidden );

                            $hidden_attr['name']  = 'page';
                            $hidden_attr['value'] = 'bcq-admin-registration-status';
                            $hidden               = new Input_Hidden( $hidden_attr );
                            $actions_form->add_content( $hidden );

                            $hidden_attr['name']  = 'event';
                            $hidden_attr['value'] = 'approve';
                            $hidden               = new Input_Hidden( $hidden_attr );
                            $actions_form->add_content( $hidden );

                            $hidden_attr['name']  = 'rid';
                            $hidden_attr['value'] = $reg_id;
                            $hidden               = new Input_Hidden( $hidden_attr );
                            $actions_form->add_content( $hidden );

                            $hidden_attr['name']  = 'secret';
                            $hidden_attr['value'] = $secret;
                            $hidden               = new Input_Hidden( $hidden_attr );
                            $actions_form->add_content( $hidden );

                            $actions_form->create_content();
                            $action_approve = $actions_form->draw_html();

                            if ( Registration_Db_Table::STATE_APPROVAL_DECLINED !== $state ) {
                                $button_attr['class'] = 'button';
                                $button_attr['type']  = 'submit';
                                $button_attr['value'] = 'Submit';
                                $refresh_button       = new Button( esc_html__( 'Declined', 'bitcoin-bank' ), $button_attr );

                                $actions_form_attr['method'] = 'get';
                                $actions_form                = new Form( $refresh_button, $actions_form_attr );

                                $hidden_attr['name']  = 'register-status';
                                $hidden_attr['value'] = $current_register_status;
                                $hidden               = new Input_Hidden( $hidden_attr );
                                $actions_form->add_content( $hidden );

                                $hidden_attr['name']  = 'page';
                                $hidden_attr['value'] = 'bcq-admin-registration-status';
                                $hidden               = new Input_Hidden( $hidden_attr );
                                $actions_form->add_content( $hidden );

                                $hidden_attr['name']  = 'event';
                                $hidden_attr['value'] = 'decline';
                                $hidden               = new Input_Hidden( $hidden_attr );
                                $actions_form->add_content( $hidden );

                                $hidden_attr['name']  = 'rid';
                                $hidden_attr['value'] = $reg_id;
                                $hidden               = new Input_Hidden( $hidden_attr );
                                $actions_form->add_content( $hidden );

                                $hidden_attr['name']  = 'secret';
                                $hidden_attr['value'] = $secret;
                                $hidden               = new Input_Hidden( $hidden_attr );
                                $actions_form->add_content( $hidden );

                                $actions_form->create_content();

                                $actions_decline = $actions_form->draw_html();
                            }
                            break;
                    }

                    switch ( $state ) {
                        case Registration_Db_Table::STATE_REGISTRATION_STARTED:
                        case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
                        case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
                        case Registration_Db_Table::STATE_REGISTRATION_MORE_INFO:
                        case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
                        case Registration_Db_Table::STATE_REGISTRATION_EXPIRED:
                        case Registration_Db_Table::STATE_APPROVAL_PENDING:
                        case Registration_Db_Table::STATE_APPROVAL_DECLINED:
                            $any = true;
                            echo '<tr>';
                            $time            = $reg_data->get_data_index( $index, Registration_Db_Table::TIMESTAMP );
                            $local_timestamp = get_date_from_gmt( $time, 'U' ) + $local_timestamp_offset;
                            $localized_time  = date_i18n( $local_time_format, $local_timestamp );
                            echo '<td>' . $localized_time . '</td>';
                            echo '<td>' . self::get_reg_status_name( $state ) . '</td>';
                            echo '<td>' . $username . '</td>';
                            $email = $reg_data->get_data_index( $index, Registration_Db_Table::EMAIL );
                            echo '<td><a href="mailto:' . $email . '">' . $email . '</a></td>';
                            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
                                $first_name = $reg_data->get_data_index( $index, Registration_Db_Table::FIRST_NAME );
                                echo '<td>' . $first_name . '</td>';
                            }
                            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
                                $last_name = $reg_data->get_data_index( $index, Registration_Db_Table::LAST_NAME );
                                echo '<td>' . $last_name . '</td>';
                            }
                            echo '<td>' . $registering_page_link . '</td>';
                            echo '<td>' . $action_approve . '</td>';
                            echo '<td class="column-primary">' . $actions_decline . '</td>';
                            echo '</tr>';
                            break;
                    }
                }
            }

            if ( ! $any ) {
                echo '<tr>';
                echo '<td class="column-primary">' . esc_html__( 'No listings', 'bitcoin-bank' ) . '</td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
                    echo '<td></td>';
                }
                if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
                    echo '<td></td>';
                }
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';

            Registration_Db_Table::set_pending_approval_counter( $pending_approval_count );

            echo '<p>' . esc_html__( 'Registration links sent to confirm e-mail addresses gets expired after 48 hours.', 'bitcoin-bank' ) . '</p>';
            if ( current_user_can( 'list_users' ) ) {
                $user_page = '<a href="' . admin_url() . 'users.php">Users</a>';
                $note      = '';
            } else {
                $user_page = 'Users';
                $note      = esc_html__( '(You are not allowed to access Users listing.)' );
            }
            /* translators: %1$s: Username 1$s: A warning note telling user can not read other user's data. */
            echo '<p>' . sprintf( esc_html__( 'Approved registrations are copied to %1$s. %2$s', 'bitcoin-bank' ), $user_page, $note ) . '</p>';
        } else {
            $form_options = get_option( Settings_Form_Options::OPTION_NAME );

            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            /* translators: Database reference number for registrations. */
            echo '<th class="column-primary">' . esc_html__( 'Time-stamp', 'bitcoin-bank' ) . '</th>';
            echo '<th>' . esc_html__( 'Status', 'bitcoin-bank' ) . '</th>';
            echo '<th>' . esc_html__( 'Username', 'bitcoin-bank' ) . '</th>';
            echo '<th>' . esc_html__( 'E-mail', 'bitcoin-bank' ) . '</th>';
            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
                echo '<th scope="col" id="firstname" class="manage-column">' . esc_html__( 'First name', 'bitcoin-bank' ) . '</th>';
            }
            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
                echo '<th scope="col" id="lastname" class="manage-column">' . esc_html__( 'Last name', 'bitcoin-bank' ) . '</th>';
            }
            echo '<th>' . esc_html__( 'Password page', 'bitcoin-bank' ) . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $any = false;
            for ( $index = 0; $index < $count; $index++ ) {
                $wp_user_id = $reg_data->get_data_index( $index, Registration_Db_Table::WP_USER_ID );
                $wp_user    = get_userdata( $wp_user_id );
                if ( false !== $wp_user ) {
                    $state = $reg_data->get_data_index( $index, Registration_Db_Table::STATE );

                    $username_link = $reg_data->get_data_index( $index, Registration_Db_Table::USERNAME );
                    if ( current_user_can( 'list_users' ) ) {
                        $wp_user_id = $reg_data->get_data_index( $index, Registration_Db_Table::WP_USER_ID );
                        if ( get_userdata( $wp_user_id ) !== false ) {
                            $edit_user_link = admin_url() . 'user-edit.php?user_id=' . $wp_user_id;
                            $username_link  = '<a href="' . $edit_user_link . '">' . $username_link . '</a>';
                        }
                    }

                    $post_id = $reg_data->get_data_index( $index, Registration_Db_Table::POST_ID );
                    if ( current_user_can( 'edit_pages' ) ) {
                        $link = admin_url() . 'post.php?post=' . $post_id . '&action=edit';
                    } else {
                        $link = get_permalink( $post_id );
                    }
                    $password_page_link = '<a href="' . $link . '">' . get_the_title( $post_id ) . '</a>';

                    switch ( $state ) {
                        case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
                        case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                        case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
                        case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
                            $any = true;
                            echo '<tr>';
                            echo '<td>' . $reg_data->get_data_index( $index, Registration_Db_Table::TIMESTAMP ) . '</td>';
                            echo '<td>' . self::get_reg_status_name( $state ) . '</td>';
                            echo '<td>' . $username_link . '</td>';
                            $email = $reg_data->get_data_index( $index, Registration_Db_Table::EMAIL );
                            echo '<td><a href="mailto:' . $email . '">' . $email . '</a></td>';
                            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
                                echo '<td>' . $wp_user->first_name . '</td>';
                            }
                            if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
                                echo '<td>' . $wp_user->last_name . '</td>';
                            }
                            echo '<td class="column-primary">' . $password_page_link . '</td>';
                            echo '</tr>';
                            break;
                    }
                }
            }

            if ( ! $any ) {
                echo '<tr>';
                echo '<td class="column-primary">' . esc_html__( 'No listings', 'bitcoin-bank' ) . '</td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_FIRST_NAME ] ) {
                    echo '<td></td>';
                }
                if ( $form_options[ Settings_Form_Options::REGISTER_COLLECT_LAST_NAME ] ) {
                    echo '<td></td>';
                }
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';

            echo '<p>' . esc_html__( 'Password reset links gets expired after 1 hour.', 'bitcoin-bank' ) . '</p>';
        }

        echo '<p>' . esc_html__( 'Listings in the tables will be cleared after 30 days.', 'bitcoin-bank' ) . '</p>';

        echo '</div>';
    }

    /**
     * @param $reg_status
     *
     * @return string
     */
    public static function get_reg_status_name( $reg_status ) {
        switch ( $reg_status ) {
            case Registration_Db_Table::STATE_REGISTRATION_STARTED:
                $name = esc_html__( 'Registering started', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
                $name = esc_html__( 'Verification e-mail sent', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
                $name = esc_html__( 'Verification e-mail confirmed', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_REGISTRATION_MORE_INFO:
                $name = esc_html__( 'Request user data', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
                $name = esc_html__( 'Completed', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
                $name = esc_html__( 'E-mail link sent', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                $name = esc_html__( 'E-mail link clicked', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
                $name = esc_html__( 'Password changed', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
                $name = esc_html__( 'Expired', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_REGISTRATION_EXPIRED:
                $name = esc_html__( 'Expired', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_APPROVAL_PENDING:
                $name = esc_html__( 'Pending approval', 'bitcoin-bank' );
                break;

            case Registration_Db_Table::STATE_APPROVAL_DECLINED:
                $name = esc_html__( 'Declined', 'bitcoin-bank' );
                break;

            default:
                $name = strval( $reg_status );
        }

        return $name;
    }



    /**
     * @param $label
     * @param $count
     * @param $register_status
     * @param $current_select
     *
     * @return string
     */
    private static function create_table_link( $label, $count, $register_status, $current_select ) {
        if ( $current_select === $register_status ) {
            $html  = '<a href="' . admin_url() . 'admin.php?page=bcq-admin-registration-status&register-status=' . $register_status . '">';
            $html .= '<strong>' . $label . '</strong>';
            $html .= '</a> (' . $count . ')';
        } else {
            $html  = '<a href="' . admin_url() . 'admin.php?page=bcq-admin-registration-status&register-status=' . $register_status . '">';
            $html .= $label;
            $html .= '</a> (' . $count . ')';
        }
        return $html;
    }

    /**
     * @param $state
     * @param $current_status
     * @param $wp_user_id
     *
     * @return bool
     */
    private static function show_registration_item( $state, $current_status, $wp_user_id ) {
        $wp_user = get_userdata( $wp_user_id );

        switch ( $state ) {
            case Registration_Db_Table::STATE_REGISTRATION_STARTED:
                if ( 'all' === $current_status ) {
                    return true;
                }
                if ( 'registering' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_REGISTRATION_EMAIL_SENT:
                if ( 'all' === $current_status ) {
                    return true;
                }
                if ( 'registering' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_REGISTRATION_EMAIL_CONFIRMED:
                if ( 'all' === $current_status ) {
                    return true;
                }
                if ( 'registering' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_REGISTRATION_MORE_INFO:
                if ( 'all' === $current_status ) {
                    return true;
                }
                if ( 'registering' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_REGISTRATION_USER_CREATED:
                if ( ( 'all' === $current_status ) && ( false !== $wp_user ) ) {
                    return true;
                }
                if ( ( 'approved' === $current_status ) && ( false !== $wp_user ) ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_SENT:
                if ( ( 'password' === $current_status ) && ( false !== $wp_user ) ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                if ( ( 'password' === $current_status ) && ( false !== $wp_user ) ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_RESET_PASSWORD_DONE:
                if ( ( 'password' === $current_status ) && ( false !== $wp_user ) ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_RESET_PASSWORD_EXPIRED:
                if ( ( 'password' === $current_status ) && ( false !== $wp_user ) ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_REGISTRATION_EXPIRED:
                if ( 'expired' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_REGISTRATION_NOT_SET:
                if ( 'all' === $current_status ) {
                    return true;
                }
                if ( 'registering' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_APPROVAL_PENDING:
                if ( 'all' === $current_status ) {
                    return true;
                }
                if ( 'pending_approval' === $current_status ) {
                    return true;
                }
                break;
            case Registration_Db_Table::STATE_APPROVAL_DECLINED:
                if ( 'declined' === $current_status ) {
                    return true;
                }
                break;
        }
        return false;
    }

}
