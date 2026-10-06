<?php

namespace TwilioWpOtp;

if ( ! defined( 'ABSPATH' ) ) exit;

use Twilio\Rest\Client;

class AuthController {
    private $twilio;
    private $serviceSid;

    public function __construct() {
        $this->twilio = new Client(
           // $_ENV['TWILIO_API_KEY'],
           // $_ENV['TWILIO_API_SECRET'],
            $_ENV['TWILIO_ACCOUNT_SID'],
            $_ENV['TWILIO_TOKEN']
        );
        $this->serviceSid = $_ENV['TWILIO_VERIFY_SID'];

            add_filter( 'authenticate', [ $this, 'intercept_login' ], 99, 3 );

            add_action( 'login_form_twp_verify', [ $this, 'render_verify_screen' ] );
    }

    public function render_verify_screen() {

        if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
            $this->handle_verify_submit();
        }
    
        $token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
        $pending = get_transient( 'twp_pending_' . $token );

        if ( ! $pending ) {
            wp_safe_redirect( wp_login_url() );
            exit;
        }

        login_header( 'Verify your code' );

        if ( isset( $_GET['error'] ) ) {
            echo '<div id="login_error" class="notice notice-error"><p>That code is not correct. Try again.</p></div>';
        }
                
        ?>
        <form method="post" action="<?php echo esc_url( add_query_arg( 'action', 'twp_verify', wp_login_url() ) ); ?>">
            <p>
                <label for="twp_code">Enter the code we texted you<br>
                    <input type="text" name="twp_code" id="twp_code" class="input" inputmode="numeric" autocomplete="one-time-code" maxlength="6" autofocus>
                </label>
            </p>
            <input type="hidden" name="twp_token" value="<?php echo esc_attr( $token ); ?>">
            <p class="submit"><input type="submit" class="button button-primary button-large" value="Verify"></p>
        </form>
        <?php
        login_footer();
        exit;
    }    

    private function handle_verify_submit() {
        $token   = isset( $_POST['twp_token'] ) ? sanitize_text_field( wp_unslash( $_POST['twp_token'] ) ) : '';
        $code    = isset( $_POST['twp_code'] ) ? sanitize_text_field( wp_unslash( $_POST['twp_code'] ) ) : '';
        $pending = get_transient( 'twp_pending_' . $token );

        if ( ! $pending ) {
            wp_safe_redirect( wp_login_url() );
            exit;
        }

        $phone = get_user_meta( $pending['user_id'], 'twp_phone', true );

        if ( ! $this->check_code( $phone, $code ) ) {
            wp_safe_redirect( add_query_arg( [
                'action' => 'twp_verify',
                'token'  => $token,
                'error'  => 1,
            ], wp_login_url() ) );
            exit;
        }

        delete_transient( 'twp_pending_' . $token );
        wp_set_auth_cookie( $pending['user_id'], $pending['remember'] );
        wp_safe_redirect( admin_url() );
        exit;
    }    

    public function intercept_login( $user, $username, $password ) {
        if ( ! $user instanceof \WP_User ) {
            return $user;
        }

        $phone = get_user_meta( $user->ID, 'twp_phone', true );
        if ( ! $phone ) {
            return $user;
        }

        $this->send_code( $phone );

        $token = wp_generate_password( 32, false );
        set_transient( 'twp_pending_' . $token, [
            'user_id'  => $user->ID,
            'remember' => ! empty( $_POST['rememberme'] ),
        ], 10 * MINUTE_IN_SECONDS );

        wp_safe_redirect( add_query_arg( [
            'action' => 'twp_verify',
            'token'  => $token,
        ], wp_login_url() ) );
        exit;
    }    

    public function send_code( string $phone ) {
        $verify       = $this->twilio->verify->v2;
        $service      = $verify->services( $this->serviceSid );
        $verification = $service->verifications->create( $phone, 'sms' );

        return $verification->status;
    } 
    
    public function check_code( string $phone, string $code ): bool {
        $verify  = $this->twilio->verify->v2;
        $service = $verify->services( $this->serviceSid );
        $check   = $service->verificationChecks->create( [
            'to'   => $phone,
            'code' => $code,
        ] );

        return $check->status === 'approved';
    }    

}
