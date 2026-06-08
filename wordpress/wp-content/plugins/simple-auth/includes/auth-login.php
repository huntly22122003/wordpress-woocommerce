<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'Models/UserModel.php';

function sa_handle_login()
{
    if (!isset($_POST['sa_login_submit'])) {
        return;
    }

    error_log('=== LOGIN REQUEST ===');

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    error_log('Email: ' . $email);

    if (!$email || !$password) {
        error_log('LOGIN FAILED: Missing email or password');
        return 'Missing email or password';
    }

    $model = new UserModel();

    $user = $model->findByEmail($email);

    if (!$user) {
        error_log('LOGIN FAILED: User not found');
        return 'User not found';
    }

    error_log('User found ID: ' . $user['id']);

    if (!password_verify($password, $user['password'])) {
            error_log('INPUT PASSWORD: ' . $password);
    error_log('HASH IN DB: ' . $user['password']);
    error_log(
        password_verify($password, $user['password'])
            ? 'PASSWORD OK'
            : 'PASSWORD FAIL'
    );
        error_log(var_export(password_verify('123', $user['password']), true));
        error_log('LOGIN FAILED: Wrong password');
        return 'Wrong password';
    }

    if (!session_id()) {
        session_start();
    }

    $_SESSION['sa_user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email']
    ];

    error_log('LOGIN SUCCESS');
    error_log(print_r($_SESSION['sa_user'], true));
    if ($user['role'] === 'admin') {
    wp_redirect(home_url('/admin'));
    exit;
    }
    
    echo '<script>';
    echo 'console.log(' . json_encode($_SESSION['sa_user']) . ')';
    echo '</script>';
    //wp_redirect(home_url());
    //exit;
}