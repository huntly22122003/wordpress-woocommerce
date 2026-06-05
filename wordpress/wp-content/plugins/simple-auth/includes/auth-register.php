<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '/Models/UserModel.php';
require_once plugin_dir_path(__FILE__) . '../../simple-otp/includes/otp-generator.php';
require_once plugin_dir_path(__FILE__) . '../../simple-otp/includes/otp-mailer.php';
require_once plugin_dir_path(__FILE__) . '../../simple-otp/includes/otp-verify.php';

/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/
function sa_handle_register()
{
    if (!isset($_POST['sa_register_submit'])) {
        return;
    }

    $username = trim($_POST['username'] ?? '');
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        wp_die('Vui lòng nhập đầy đủ thông tin');
    }

    $userModel = new UserModel();

    // Check email tồn tại
    if ($userModel->findByEmail($email)) {
        wp_die('Email đã tồn tại');
    }


    // Gửi OTP
    if (!function_exists('sa_send_otp_email')) {
        wp_die('Simple OTP chưa được kích hoạt');
    }

    $sent = sa_send_otp_email($email);

    if (!$sent) {
        wp_die('Không thể gửi OTP');
    }

    // Lưu tạm thông tin vào session để xác nhận sau
    $_SESSION['sa_pending_user'] = [
        'username' => $username,
        'email'    => $email,
        'password' => $password,
    ];

    // Chuyển sang trang nhập OTP
    wp_redirect(home_url('/sa-otp'));
    exit;
}

add_action('init', 'sa_handle_register');
