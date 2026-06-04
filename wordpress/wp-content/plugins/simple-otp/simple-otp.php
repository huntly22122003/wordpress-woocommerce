<?php
/*
Plugin Name: Simple OTP
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit;
}
function sa_send_otp_email($email)
{
    OtpModel::cleanupExpired();

    $otp =
        sa_generate_otp();

    $sent =
        wp_mail(...);

    if ($sent) {

        OtpModel::create(
            $email,
            $otp
        );

    }

    return $sent;
}

require_once plugin_dir_path(__FILE__) . 'Models/OtpModel.php';
require_once plugin_dir_path(__FILE__) . 'includes/otp-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/smtp-config.php';

require_once plugin_dir_path(__FILE__) . 'includes/otp-mailer.php';
require_once plugin_dir_path(__FILE__) . 'includes/otp-verify.php';