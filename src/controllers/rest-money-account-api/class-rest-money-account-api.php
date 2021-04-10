<?php

namespace BCQ_BitcoinBank;

use WP_PluginFramework\Controllers\Rest_Controller;
use WP_PluginFramework\Utils\Query_Parameters;

defined( 'ABSPATH' ) || exit;

class Rest_Money_Account_Api extends Rest_Controller
{
    const NAME_SPACE = 'bitcoin-bank-money-account-api/v1';

    private $authorised_client_id = null;

    public function __construct($model_class_name=null) {
        $name_space = self::NAME_SPACE;
        parent::__construct( $name_space, $model_class_name);
    }

    public function endpoint_get_has_permission($request) {
        return $this->authenticate_client_id($request);
    }

    public function endpoint_get_single_has_permission($request) {
        return $this->authenticate_client_id($request);
    }

    public function endpoint_post_has_permission($request) {
        return $this->authenticate_client_id($request);
    }

    public function authenticate_client_id($request) {
        $authorised = false;
        if($this->check_basic_authentication_wp_user($request) !== false) {
            $wp_user_id = $this->get_authenticated_wp_user_id();
            $this->authorised_client_id = Accounting::get_client_id($wp_user_id);
            $authorised = true;
        }
        return $authorised;
    }

    public function get_authorised_client_id() {
        return $this->authorised_client_id;
    }





}
