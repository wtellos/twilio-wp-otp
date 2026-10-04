<?php

/**
 * Plugin Name: Twilio WP OTP
 * Description: Passwordless login with SMS one-time codes via Twilio Verify.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Text Domain: twilio-wp-otp
 */


if ( ! defined('ABSPATH') ) exit;

define( 'TWILIO_WP_OTP_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );

// Enqueue the CSS file
 wp_enqueue_style('twp-css', plugin_dir_url(__FILE__) . 'assets/css/twp.css', array(), '1.0', false);

// Enqueue the JS file
wp_enqueue_script( 'twp-js', plugin_dir_url(__FILE__) . 'assets/js/twp.js', array('jquery'), '1.0', false);


$autoload = TWILIO_WP_OTP_PLUGIN_ROOT . 'vendor/autoload.php';

if ( file_exists( $autoload ) ) {
    require_once $autoload;
}

include_once( plugin_dir_path( __FILE__ ) . 'includes/AuthController.php' );
