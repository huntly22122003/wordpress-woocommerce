<?php

if (!defined('ABSPATH')) {
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;

add_action('phpmailer_init', function ($phpmailer) {

    error_log('SMTP INIT CALLED');

    // FORCE SMTP
    $phpmailer->isSMTP();

    $phpmailer->Host = 'smtp.gmail.com';
    $phpmailer->SMTPAuth = true;
    $phpmailer->Port = 587;
    $phpmailer->SMTPSecure = 'tls';

    $phpmailer->Username = 'sasukeholy@gmail.com';
    $phpmailer->Password = PASSKEY;

    $phpmailer->setFrom(
        'sasukeholy@gmail.com',
        'Simple OTP'
    );

    // DEBUG SMTP (CHỈ GIỮ 1 CÁI)
    $phpmailer->SMTPDebug = 2;
    $phpmailer->Debugoutput = function ($str, $level) {
        error_log("SMTP[$level] $str");
    };
});