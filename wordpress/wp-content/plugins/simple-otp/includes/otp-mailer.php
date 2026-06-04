<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__)
    . 'Models/OtpModel.php';
function sa_send_otp_email($email)
{
    $email = sanitize_email(
        $email
    );

    if (!is_email($email)) {
        return false;
    }

    OtpModel::cleanupExpired();

    $otp = sa_generate_otp();

    $subject =
        'Email Verification OTP';

    $message = "
        <h2>Xác thực tài khoản</h2>

        <p>Mã OTP của bạn là:</p>

        <h1>{$otp}</h1>

        <p>Mã có hiệu lực trong 3 phút.</p>
    ";

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Simple OTP <sasukeholy@gmail.com>'
    ];

    $sent = wp_mail(
        $email,
        $subject,
        $message,
        $headers
    );

    if ($sent) {

        OtpModel::create(
            $email,
            $otp
        );

    }

    if (!$sent) {
    error_log('OTP MAIL FAILED');
    }

    return $sent;
}
add_action('wp_mail_failed', function ($error) {

    error_log(
        'WP MAIL ERROR: '
        . print_r(
            $error->get_error_messages(),
            true
        )
    );
});