<?php

namespace BCQ_BitcoinBank;

use WP_PluginFramework\Utils\Debug_Logger;

defined( 'ABSPATH' ) || exit;

class Digital_Signature
{

    public function create_first_time() {
        $result = false;
        $certificate_data = new Certificates();
        $columns = array(
            Certificates::EXPIRE_TIME,
            Certificates::STATE
        );
        $number = $certificate_data->load_column($columns);
        if ($number === false) {
            return false;
        }

        if ($number > 0) {
            $result = true;
        }

        if (!$result) {
            $result = $this->create_new_certificate(OPENSSL_KEYTYPE_RSA, 2048);
        }

        return $result;
    }

    private function debug_log_ssl_error_messages($write_explanation_note = false, $type=null, $length=null) {
        while ($ssl_error_message = openssl_error_string()) {
            if ($write_explanation_note) {
                Debug_Logger::write_debug_note($write_explanation_note, $type, $length);
                $write_explanation_note = false;
            }
            Debug_Logger::write_debug_note('SSL error:', $ssl_error_message);
        }
    }

    public function create_new_certificate($type, $length) {
        $error = false;

        $private_key = '';
        $private_key_encoded = '';
        $public_key = '';
        $public_key_encoded = '';

        $this->debug_log_ssl_error_messages('Creating new certificates, and there was pre-existing errors in ssl module.');

        $config = array(
            "private_key_type" => $type,
            "private_key_bits" => $length,
            /*"config" => "C:/xampp74/apache/conf/openssl.cnf"*/
        );
        $cert_res = openssl_pkey_new($config);

        if ($cert_res === false) {
            $error = true;
            $this->debug_log_ssl_error_messages('Error creating certificate.', $type, $length);
        }
        else {
            $this->debug_log_ssl_error_messages('Certificate created, but SLL gave errors.', $type, $length);
        }

        if (!$error) {
            $ok = openssl_pkey_export($cert_res, $private_key);
            if ($ok !== true) {
                $error = true;
                $this->debug_log_ssl_error_messages('Error exporting private key.', $type, $length);
            }
            else {
                $this->debug_log_ssl_error_messages('Export private keys ok, but SSL module gave error.', $type, $length);
            }
        }

        if (!$error) {
            $public_key_data = openssl_pkey_get_details($cert_res);
            if (!is_array($public_key_data)) {
                $error = true;
                $this->debug_log_ssl_error_messages('Error exporting private key.', $type, $length);
            }
            else {
                $this->debug_log_ssl_error_messages('Export private keys ok, but SSL module gave error.', $type, $length);
                $public_key = $public_key_data["key"];
            }
        }

        if (!$error) {
            $private_key_encoded = base64_encode($private_key);
            $key_length = strlen($private_key_encoded);
            if($key_length > Certificates::MAX_KEY_LENGTH_ENCODED) {
                $error = true;
                Debug_Logger::write_debug_note('Error private key too big for database field.', $key_length, Certificates::MAX_KEY_LENGTH_ENCODED);
            }
        }

        if (!$error) {
            $public_key_encoded = base64_encode($public_key);
            $key_length = strlen($public_key_encoded);
            if($key_length > Certificates::MAX_KEY_LENGTH_ENCODED) {
                $error = true;
                Debug_Logger::write_debug_note('Error public key too big for database field.', $key_length, Certificates::MAX_KEY_LENGTH_ENCODED);
            }
        }

        if (!$error) {
            $create_time = time();
            $expire_time = null;
            $state = Certificates::STATE_OK;

            $certificate_data = new Certificates();
            $certificate_data->set_data(Certificates::CREATED_TIME, $create_time);
            $certificate_data->set_data(Certificates::EXPIRE_TIME, $expire_time);
            $certificate_data->set_data(Certificates::STATE, $state);
            $certificate_data->set_data(Certificates::PRIVATE_KEY, $private_key_encoded);
            $certificate_data->set_data(Certificates::PUBLIC_KEY, $public_key_encoded);
            $result = $certificate_data->save_data();

            if ($result !== true) {
                Debug_Logger::write_debug_note('Error updating certificate table.');
                $error = true;
            }
        }

        /* Overwrite private key memory */
        $n = strlen($private_key);
        for($i=0; $i<$n; $i++) {
            $private_key[$i] = '.';
        }

        $n = strlen($private_key_encoded);
        for($i=0; $i<$n; $i++) {
            $private_key_encoded[$i] = '.';
        }

        return (!$error);
    }
}
