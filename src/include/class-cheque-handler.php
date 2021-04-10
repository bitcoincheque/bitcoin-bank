<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Mailer;
use WP_PluginFramework\Utils\Security_Filter;

class Cheque_Handler {
    static public function schedule_event() {
        Debug_Logger::write_debug_note( 'Cheque_Handler hourly event.' );
        self::check_expired();
    }

    static public function query_cheques($query_parameters){
        $cheques = array();

        /* Need all fields to build the cheques */
        $query_parameters_cloned = clone $query_parameters;
        $query_parameters_cloned->select_all_fields();

        $cheque_data = new Cheque_Db_Table();
        $cheque_data->load_data_query_parameters($query_parameters_cloned);
        $records = $cheque_data->get_copy_all_data();

        $records_processed = array();
        foreach ($records as $record) {
            $cheque = new Cheque_File();
            $cheque->set_data_record($record);
            /* Need to build the cheque to create all data */
            $record_processed = $cheque->get_cheque_data($query_parameters->get_fields());
            $records_processed[] = $record_processed;
        }

        return $records_processed;
    }

    static public function get_cheque_fee() {
        return 1000;
    }

    static public function check_expired() {
        $cheque_data = new Cheque_Db_Table();
        $result = $cheque_data->load_data(Cheque_Db_Table::STATE, Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED);
        if( $result !== false )
        {
            $cheque_list = $cheque_data->get_copy_all_data();

            foreach ($cheque_list as $cheque)
            {
                $expire_time = $cheque[Cheque_Db_Table::EXPIRE_TIME];
                $expire_time_sec = strtotime( $expire_time );
                $now = time();
                if( $now > $expire_time_sec )
                {
                    $cheque_id = $cheque[Cheque_Db_Table::PRIMARY_KEY];
                    $amount = $cheque[Cheque_Db_Table::AMOUNT];
                    $debit_account_id = $cheque[Cheque_Db_Table::DEBIT_ACCOUNT_ID];
                    $credit_account_id = $cheque[Cheque_Db_Table::CREDIT_ACCOUNT_ID];

                    Debug_Logger::write_debug_note( 'Cheque has expired ', $cheque_id );

                    $result = $cheque_data->load_data_id( $cheque_id );
                    if($result === 1)
                    {
                        $cheque_data->set_data(Cheque_Db_Table::STATE, Cheque_Db_Table::STATE_REGISTRATION_EXPIRED);
                        $result = $cheque_data->save_data();
                        if( $result != false )
                        {
                            $transaction_id = Accounting::make_cheque_expire_transaction($credit_account_id, $debit_account_id, $amount, $cheque_id);
                            if( $transaction_id === false )
                            {
                                Debug_Logger::write_debug_error( 'Can not reverse transaction for expire cheque', $cheque_id );
                            }
                        }
                    }
                }
            }
        }
    }

    static public function create_cheque(
        $cheque,
        $time_stamp,
        $debit_account_id,
        $fee_obj,
        $sender_client_id,
        $receiver_client_id,
        $state=Cheque_Db_Table::STATE_REGISTRATION_INIT
    ) {
        $cheque_data = new Cheque_Db_Table();

        $fee = $fee_obj->get_value();
        $credit_account_id = Accounting::get_cheque_reserved_account_id();

        $cheque = $cheque_data->create_cheque(
            $cheque,
            $time_stamp,
            $debit_account_id,
            $credit_account_id,
            $fee,
            $sender_client_id,
            $receiver_client_id,
            $state
        );

        return $cheque;
    }




    static function claim_cheque( $cheque_id, $access_code ) {
        $claim_approved = false;
        $client_id = Accounting::get_client_id();
        if($client_id)
        {
            $account_id = Accounting::get_client_default_account($client_id);
            if($account_id)
            {
                $cheque_data = new Cheque_Db_Table();
                $cheque_data->load_data_id($cheque_id);

                $state = $cheque_data->get_data(Cheque_Db_Table::STATE);
                if($state === Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED)
                {
                    $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
                    if ($access_code === $my_access_code)
                    {
                        $receiver_client_id = $cheque_data->get_data(Cheque_Db_Table::RECEIVER_CLIENT_ID);
                        if ($receiver_client_id == $client_id)
                        {
                            $claim_approved = true;
                        }

                        $receiver_email = $cheque_data->get_data(Cheque_Db_Table::RECEIVER_ADDRESS);
                        $wp_current_user = wp_get_current_user();
                        $user_email = $wp_current_user->user_email;
                        if ($receiver_email === $user_email)
                        {
                            $claim_approved = true;
                        }

                        if ($claim_approved)
                        {
                            $amount = $cheque_data->get_data(Cheque_Db_Table::AMOUNT);
                            $state = $cheque_data->get_data(Cheque_Db_Table::STATE);
                            $cheque_data->set_data(Cheque_Db_Table::STATE, Cheque_Db_Table::STATE_REGISTRATION_CLAIMED);
                            $cheque_data->set_data(Cheque_Db_Table::RECEIVER_CLIENT_ID, $client_id);
                            $cheque_data->save_data();
                            return Accounting::make_cheque_claim_transaction($account_id, $amount, $cheque_id);
                        }
                    }
                }
            }
        }
        return false;
    }

    static function reject_cheque( $cheque_id, $access_code )
    {
        $client_id = Accounting::get_client_id();
        if($client_id)
        {
            $cheque_data = new Cheque_Db_Table();
            $result = $cheque_data->load_data_id($cheque_id);
            if ($result === 1)
            {
                $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
                $amount = $cheque_data->get_data(Cheque_Db_Table::AMOUNT);
                $debit_account_id = $cheque_data->get_data(Cheque_Db_Table::DEBIT_ACCOUNT_ID);
                $credit_account_id = $cheque_data->get_data(Cheque_Db_Table::CREDIT_ACCOUNT_ID);
                $state = $cheque_data->get_data(Cheque_Db_Table::STATE);

                if ($access_code === $my_access_code)
                {
                    if ($state == Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED)
                    {
                        $cheque_data->set_data(Cheque_Db_Table::STATE, Cheque_Db_Table::STATE_REGISTRATION_REJECTED);
                        $cheque_data->set_data(Cheque_Db_Table::RECEIVER_CLIENT_ID, $client_id);
                        $result = $cheque_data->save_data();

                        if ($result != false)
                        {
                            $transaction_id = Accounting::make_cheque_reject_transaction($credit_account_id, $debit_account_id, $amount, $cheque_id);
                            if ($transaction_id === false)
                            {
                                Debug_Logger::write_debug_error('Can not reverse transaction for expire cheque', $cheque_id);
                            }
                            else
                            {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    static public function change_state_machine($cheque_id, $new_state) {
        $result = false;
        $cheque_data = new Cheque_Db_Table();
        $cheque_data->load_data_id($cheque_id);
        $state = $cheque_data->get_data(Cheque_Db_Table::STATE);
        switch ($state) {
            case Cheque_Db_Table::STATE_REGISTRATION_INIT:
                switch ($new_state) {
                    case Cheque_Db_Table::STATE_REGISTRATION_INIT:
                    case Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED:
                    case Cheque_Db_Table::STATE_REGISTRATION_CLAIMED:
                    case Cheque_Db_Table::STATE_REGISTRATION_EXPIRED:
                    case Cheque_Db_Table::STATE_REGISTRATION_CASHED:
                    case Cheque_Db_Table::STATE_REGISTRATION_REJECTED:
                        $result = true;
                        break;
                }
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED:
                switch ($new_state) {
                    case Cheque_Db_Table::STATE_REGISTRATION_CLAIMED:
                    case Cheque_Db_Table::STATE_REGISTRATION_EXPIRED:
                    case Cheque_Db_Table::STATE_REGISTRATION_CASHED:
                    case Cheque_Db_Table::STATE_REGISTRATION_REJECTED:
                        $result = true;
                        break;
                }
                break;
            case Cheque_Db_Table::STATE_REGISTRATION_CLAIMED:
                switch ($new_state) {
                    case Cheque_Db_Table::STATE_REGISTRATION_CASHED:
                        $result = true;
                        break;
                }
                break;
        }

        if ($result !== false) {
            $cheque_data->set_data(Cheque_Db_Table::STATE, $new_state);
            $cheque_data->save_data();
        }else {
            Debug_Logger::write_debug_error('Atempted change cheque state to ilegal state', $state, $new_state);
        }

        return $result;
    }

    static public function change_state_to_issued($cheque_id) {
        return self::change_state_machine($cheque_id, Cheque_Db_Table::STATE_REGISTRATION_UNCLAIMED);
    }

    static public function send_email_cheque( $email, $cheque_id, $access_code, $receiver_name, $message ) {
        Debug_Logger::write_debug_note( Debug_Logger::obfuscate( $email ) );

        $png_url     = site_url() . '/wp-admin/admin-ajax.php?action=bcf_bitcoinbank_get_cheque_png&cheque_id=' . $cheque_id . '&access_code=' . $access_code;

        $links_options = new Settings_Linking_Options();
        $claim_url = $links_options->get_complete_link_url( Settings_Linking_Options::RECEIVE_CHEQUE_PAGE );
        $collect_url = $claim_url . '?cheque_id=' . $cheque_id . '&access_code=' . $access_code;

        $body = '';
        /*
        $body = '<p></p><b>Hello';
        if($receiver_name)
        {
            $body .= ' ' . $receiver_name;
        }
        $body .= ',</b></p>';
        */

        if($message)
        {
            $body .= '<p>You have received a Bitcoin Cheque</p>';
            $body .= '<p>Message from sender: ' . $message . '</p>';
            $body .= '<p>To collect the money click on the cheque picture or copy the link below into your web browser.</p>';
        }
        else
        {
            $body .= '<p>You have received a Bitcoin Cheque. To collect the money click on the cheque picture or copy the link below into your web browser.</p>';
        }

        $body .= '<p><a href="' . $collect_url . '"><img src="' . $png_url . '" height="300" width="800" alt="Loading cheque image..."/></a></p>';

        $body .= '<p><a href="' . $collect_url . '">' . $collect_url . '</a></p>';

        $body .= '<p>Or you can click on this link and enter the Cheque No. and Access Code:</p>';
        $body .= '<p><a href="' . $claim_url . '">' . $claim_url . '</a></p>';
        $body .= 'Cheque No.: ' . $cheque_id;
        $body .= '<br>Access Code.: ' . $access_code;

        $body .= '<p>This Bitcoin Cheque has been issued by <a href="https://www.bitcoindemobank.com/"><strong>Bitcoin Bank</strong></a></p>';

        $body .= '<p><b>What is Bitcoin?</b><br>Bitcoin is a consensus network that enables a new payment system and a completely digital money. It is the first decentralized peer-to-peer payment network that is powered by its users with no central authority or middlemen. From a user perspective, Bitcoin is pretty much like cash for the Internet.</p>';
        $body .= '<p><b>What is Bitcoin Cheques?</b><br>A Bitcoin Cheque is a new method for sending Bitcoins. The Bitcoin Cheque is a promiss that the issuing bank will pay a certain amount to a receiver. You can read more about Bitcoin Cheque here at <a href="http://www.bitcoincheque.org">www.bitcoincheque.org</a></p>';

        $option = new Settings_Email_Options();
        $option->load_data();
        $replay_addr = $option->get_data(Settings_Email_Options::BITCOIN_CHEQUE_REPLY_ADDRESS);

        $subject = "Bitcoin Cheque";

        //return true;
        return self::send_email( $email, $replay_addr, $subject, $body );
    }

    static private function send_email( $to, $from, $subject, $body ) {
        $email = array(
            'to'      => $to,
            'from'    => $from,
            'subject' => $subject,
            'body'    => $body,
        );

        $email = apply_filters( 'bitcoin_bank_email', $email );

        if ( gettype( $email ) === 'array' ) {
            $mailer = new Mailer( $email['to'] );
            $mailer->set_from_address( $email['from'] );
            $mailer->aet_subject( $email['subject'] );
            $mailer->set_body( $email['body'] );
            return $mailer->send();
        }

        return ( true === $email );
    }

    static function create_cheque_picture_content($cheque_id, $access_code) {
        $png_url     = site_url() . '/wp-admin/admin-ajax.php?action=bcf_bitcoinbank_get_cheque_png&cheque_id=' . strval($cheque_id) . '&access_code=' . strval($access_code);
        $img = new Img($png_url);
        $img->add_attribute('style', 'width:800px;height:300px;border: 1px solid gray;');
        $img->add_attribute('class', 'bitcoin-bank-cheque-placeholder');
        return $img;
    }

    static function draw_cheque_picture($cheque_id, $access_code) {
        $img = self::create_cheque_picture_content($cheque_id, $access_code);
        $html = $img->draw();
        return $html;
    }
}
