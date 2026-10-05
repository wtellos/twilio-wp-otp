<?php

namespace TwilioWpOtp;

if ( ! defined( 'ABSPATH' ) ) exit;

class RegistrationController {

    public function __construct() {
        add_action( 'register_form', [ $this, 'render_phone_field' ] );
        add_filter( 'registration_errors', [ $this, 'validate_phone_field' ], 10, 3 );
        add_action( 'user_register', [ $this, 'save_phone_field' ] );
    }

    public function validate_phone_field( $errors, $sanitized_user_login, $user_email ) {
        $phone = isset( $_POST['twp_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['twp_phone'] ) ) : '';

        if ( ! preg_match( '/^\+[1-9]\d{7,14}$/', $phone ) ) {
            $errors->add( 'twp_phone_error', '<strong>Error:</strong> Enter a valid phone number, like +35799123456.' );
        }

        return $errors;
    }    

    public function save_phone_field( $user_id ) {
        if ( isset( $_POST['twp_phone'] ) ) {
            $phone = sanitize_text_field( wp_unslash( $_POST['twp_phone'] ) );
            update_user_meta( $user_id, 'twp_phone', $phone );
        }
    }    

    public function render_phone_field() {
        ?>
        <p>
            <label for="twp_phone">Phone number (with country code)<br>
                <input type="tel" name="twp_phone" id="twp_phone" class="input" placeholder="+35799123456" required>
            </label>
        </p>
        <?php
    }
}
