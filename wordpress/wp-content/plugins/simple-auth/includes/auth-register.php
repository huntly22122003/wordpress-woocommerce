<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '/Models/UserModel.php';
require_once plugin_dir_path(__FILE__)
    . '../../simple-otp/includes/otp-generator.php';
require_once plugin_dir_path(__FILE__)
    . '../../simple-otp/includes/otp-mailer.php';
require_once plugin_dir_path(__FILE__)
    . '../../simple-otp/includes/otp-verify.php';
/*
|--------------------------------------------------------------------------
| AJAX SEND OTP
|--------------------------------------------------------------------------
*/

add_action(
    'wp_ajax_nopriv_sa_send_otp',
    'sa_send_register_otp'
);

add_action(
    'wp_ajax_sa_send_otp',
    'sa_send_register_otp'
);

function sa_send_register_otp()
{
    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    if (!$email) {

        wp_send_json([
            'success' => false,
            'message' => 'Email không hợp lệ'
        ]);

    }

    $userModel = new UserModel();

    if (
        $userModel->findByEmail($email)
    ) {

        wp_send_json([
            'success' => false,
            'message' => 'Email đã tồn tại'
        ]);

    }

    if (
        !function_exists(
            'sa_send_otp_email'
        )
    ) {

        wp_send_json([
            'success' => false,
            'message' => 'Simple OTP chưa được kích hoạt'
        ]);

    }

    $sent =
        sa_send_otp_email(
            $email
        );

    wp_send_json([
        'success' => $sent,
        'message' => $sent
            ? 'OTP đã được gửi'
            : 'Gửi OTP thất bại'
    ]);
}

/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

function sa_handle_register()
{
    if (
        !isset(
            $_POST['sa_register_submit']
        )
    ) {
        return;
    }

    $full_name =
        trim(
            $_POST['full_name'] ?? ''
        );

    $gender =
        trim(
            $_POST['gender'] ?? ''
        );

    $age =
        intval(
            $_POST['age'] ?? 0
        );

    $email =
        sanitize_email(
            $_POST['email'] ?? ''
        );

    $phone =
        trim(
            $_POST['phone'] ?? ''
        );

    $password =
        $_POST['password'] ?? '';

    $otp =
        trim(
            $_POST['otp'] ?? ''
        );

    if (!$password) {
        wp_die('Mật khẩu không được để trống');
    }

    $hashedPassword = password_hash(
        $password,
        PASSWORD_BCRYPT
    );

    $userModel =
        new UserModel();

    /*
    |------------------------------------------------------
    | Check Email
    |------------------------------------------------------
    */

    if (
        $userModel->findByEmail(
            $email
        )
    ) {

        wp_die(
            'Email đã tồn tại'
        );

    }

    /*
    |------------------------------------------------------
    | Check Phone
    |------------------------------------------------------
    */

    if (
        $userModel->findByPhone(
            $phone
        )
    ) {

        wp_die(
            'Số điện thoại đã tồn tại'
        );

    }

    /*
    |------------------------------------------------------
    | Verify OTP
    |------------------------------------------------------
    */

    if (
        !function_exists(
            'sa_verify_otp'
        )
    ) {

        wp_die(
            'Simple OTP chưa được kích hoạt'
        );

    }

    if (
        !sa_verify_otp(
            $email,
            $otp
        )
    ) {

        wp_die(
            'OTP không hợp lệ hoặc đã hết hạn'
        );

    }

    /*
    |------------------------------------------------------
    | Create User
    |------------------------------------------------------
    */

    $created =
        $userModel->create(
            $full_name,
            $gender,
            $age,
            $email,
            $phone,
            $password
        );

    if (!$created) {

        wp_die(
            'Không thể tạo tài khoản'
        );

    }

    /*
    |------------------------------------------------------
    | Redirect Login
    |------------------------------------------------------
    */

    wp_redirect(
        home_url('/sa-login')
    );

    exit;
}

add_action(
    'init',
    'sa_handle_register'
);
