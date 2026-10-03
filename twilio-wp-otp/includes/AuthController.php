<?php

namespace TwilioWpOtp;

if ( ! defined( 'ABSPATH' ) ) exit;


class AuthController {
    private $twilio;
    private $serviceSid;

    public function __construct() {
        $this->twilio = new Client(
            $_ENV['TWILIO_ACCOUNT_SID'],
            $_ENV['TWILIO_TOKEN']
        );
        $this->serviceSid = $_ENV['TWILIO_VERIFY_SID'];
    }

}
