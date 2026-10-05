<?php

namespace TwilioWpOtp;

use Twilio\Rest\Client;

class AuthController {
    private $twilio;
    private $serviceSid;

    public function __construct() {
        $this->twilio = new Client(
            $_ENV['TWILIO_API_KEY'],
            $_ENV['TWILIO_API_SECRET'],
            $_ENV['TWILIO_ACCOUNT_SID']
        );
        $this->serviceSid = $_ENV['TWILIO_VERIFY_SID'];
    }
}
