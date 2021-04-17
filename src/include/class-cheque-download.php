<?php

namespace BCQ_BitcoinBank;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Mailer;
use WP_PluginFramework\Utils\Security_Filter;

class Cheque_Download {

    static protected function output_cheque_picture_png($cheque_data) {
        $cheque_no = $cheque_data->get_data(Cheque_Db_Table::PRIMARY_KEY);
        $sender_address = $cheque_data->get_data(Cheque_Db_Table::SENDER_ADDRESS);
        $receiver_address = $cheque_data->get_data(Cheque_Db_Table::RECEIVER_ADDRESS);
        $issue_datetime = $cheque_data->get_data(Cheque_Db_Table::ISSUE_TIMESTAMP);

        $expire_time = $cheque_data->get_data(Cheque_Db_Table::EXPIRE_TIME);
        $amount = $cheque_data->get_data_object(Cheque_Db_Table::AMOUNT);
        $amount_formatted = $amount->get_formatted_text();

        $memo = $cheque_data->get_data(Cheque_Db_Table::MEMO);
        $memo = wordwrap($memo, 40, PHP_EOL, true);
        $memo_lines = explode ( PHP_EOL, $memo );

        $access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
        $hash = $cheque_data->get_data(Cheque_Db_Table::HASH);

        header("Content-type: image/png");

        $filename = plugins_url() . '/bitcoin-bank/asset/img/bank_logo.png';

        $filename = plugins_url() . '/bitcoin-bank/asset/img/cheque_template2.png';

        $im = imagecreatefrompng($filename);

        $black = imagecolorallocate($im, 0, 0, 0);
        $red_ink = imagecolorallocate($im, 255, 0, 0);

        //imagestring($im, 10, 20, 20, get_bloginfo(), $black);
        //imagestring($im, 10, 20, 40, get_site_url(), $black);

        imagestring($im, 10, 20, 125, 'From   : ' . $sender_address, $black);
        imagestring($im, 10, 20, 145, 'To     : ' . $receiver_address, $black);

        $y=165;
        imagestring($im, 10, 20, $y, 'Memo   :', $black);
        foreach($memo_lines as $memo_line){
            imagestring($im, 10, 100, $y, $memo_line, $black);
            $y += 20;
        }

        imagestring($im, 10, 520, 125, $amount_formatted, $black);

        $issue_datetime = date( 'Y-m-d H:i', strtotime( $issue_datetime ));
        imagestring($im, 10, 520, 170, 'Issue  time: ' . $issue_datetime, $black);

        $expire_time = date( 'Y-m-d H:i', strtotime( $expire_time ));
        imagestring($im, 10, 520, 190, 'Expire time: ' . $expire_time, $black);

        imagestring($im, 10, 20, 275, 'Cheque S/N: ' . $cheque_no . '  Access Code: ' . $access_code . '  Hash:' . $hash, $black);

        imagestring($im, 14, 240, 230, 'NOT VALID - ONLY FOR DEMONSTRATION', $red_ink);

        imagepng($im);

        imagedestroy($im);
    }

    static public function output_cheque_picture_png_file()
    {
        $cheque_id   = Security_Filter::safe_read_get_request( 'cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO );
        if(!isset($cheque_id))
        {
            echo 'Missing cheque_id argument in url.';
            die();
        }

        $access_code = Security_Filter::safe_read_get_request( 'access_code', Security_Filter::ALPHA_NUM );
        if(!isset($access_code))
        {
            echo 'Missing access_code argument in url.';
            die();
        }

        $cheque_data = new Cheque_Db_Table();
        $results = $cheque_data->load_data_id($cheque_id);
        if($results === false)
        {
            echo 'Invalid cheque s/n.';
            die();
        }

        $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
        if( $access_code !== $my_access_code )
        {
            echo 'Invalid access code.';
            die();
        }

        header("Content-type: image/png");

        self::output_cheque_picture_png($cheque_data);

        die();
    }

    static public function download_cheque_picture_png_file()
    {
        $cheque_id   = Security_Filter::safe_read_get_request( 'cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO );
        if(!isset($cheque_id))
        {
            echo 'Missing cheque_id argument in url.';
            die();
        }

        $access_code = Security_Filter::safe_read_get_request( 'access_code', Security_Filter::ALPHA_NUM );
        if(!isset($access_code))
        {
            echo 'Missing access_code argument in url.';
            die();
        }

        $cheque_data = new Cheque_Db_Table();
        $results = $cheque_data->load_data_id($cheque_id);
        if($results === false)
        {
            echo 'Invalid cheque s/n.';
            die();
        }

        $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
        if( $access_code !== $my_access_code )
        {
            echo 'Invalid access code.';
            die();
        }

        $filename = 'cheque-' . strval($cheque_id) . '.png';
        header("Content-type: image/png");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        self::output_cheque_picture_png($cheque_data);

        die();
    }

    static public function download_cheque_file()
    {
        $cheque_id   = Security_Filter::safe_read_get_request( 'cheque_id', Security_Filter::POSITIVE_INTEGER_ZERO );
        if(!isset($cheque_id))
        {
            echo 'Missing cheque_id argument in url.';
            die();
        }

        $access_code = Security_Filter::safe_read_get_request( 'access_code', Security_Filter::ALPHA_NUM );
        if(!isset($access_code))
        {
            echo 'Missing access_code argument in url.';
            die();
        }

        $cheque_data = new Cheque_Db_Table();
        $results = $cheque_data->load_data_id($cheque_id);
        if($results === false)
        {
            echo 'Invalid cheque s/n.';
            die();
        }

        $my_access_code = $cheque_data->get_data(Cheque_Db_Table::ACCESS_CODE);
        if( $access_code !== $my_access_code )
        {
            echo 'Invalid access code.';
            die();
        }

        $record = $cheque_data->get_data_record();
        $cheque_file = new Cheque_File();
        $cheque_file->set_data_record($record);
        $text = $cheque_file->get_text();

        if($text !== false)
        {
            $filename = 'cheque-' . strval($cheque_id) . '.txt';
            header("Content-type: text/txt");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $text;
        }
        else
        {
            $arr = $cheque_file->get_missing_mandatory_fields();
            if(!empty($arr)) {
                echo 'Error generating cheque file. Missing field ' . print_r($arr, true);
            } else {
                echo 'Error generating cheque file.' . $arr;
            }
        }

        die();
    }

}
