<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '/Models/UserModel.php';
require_once plugin_dir_path(__FILE__) . '../../simple-otp/includes/otp-verify.php';

function sa_handle_otp_verify()
{
    if (!isset($_POST['sa_otp_submit'])) {
        return;
    }

    $otp = trim($_POST['otp'] ?? '');
    $pendingUser = $_SESSION['sa_pending_user'] ?? null;

    if (!$pendingUser) {
        wp_die('Không tìm thấy thông tin đăng ký');
    }

    if (!function_exists('sa_verify_otp')) {
        wp_die('Simple OTP chưa được kích hoạt');
    }

    if (!sa_verify_otp($pendingUser['email'], $otp)) {
        wp_die('OTP không hợp lệ hoặc đã hết hạn');
    }

    // Tạo user
    $userModel = new UserModel();
    $created = $userModel->create(
        $pendingUser['username'],
        NULL,
        NULL,
        NULL,
        $pendingUser['email'],
        $pendingUser['password'],
        $pendingUser['role']
    );

    unset($_SESSION['sa_pending_user']);

    if (!$created) {
        wp_die('Không thể tạo tài khoản');
    }

    wp_redirect(home_url('/sa-login'));
    exit;
}

add_action('init', 'sa_handle_otp_verify');
