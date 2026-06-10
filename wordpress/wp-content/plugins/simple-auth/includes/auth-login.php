<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'Models/UserModel.php';

function sa_handle_login()
{
    if (!isset($_POST['sa_login_submit'])) {
        return;
    }

    if (!session_id()) {
        session_start();
    }
    error_log('=== LOGIN REQUEST ===');

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    error_log('Email: ' . $email);  

    if (!$email || !$password) {
        $_SESSION['sa_error'] = 'Missing email or password';
        error_log('Session error: ' . print_r($_SESSION['sa_error'], true));
        wp_redirect(home_url('/sa-login'));
        exit;
    }

    $model = new UserModel();

    $user = $model->findByEmail($email);

    if (!$user) {
        $_SESSION['sa_error'] = 'User not found';
        wp_redirect(home_url('/sa-login'));
        exit;
    }

    error_log('User found ID: ' . $user['id']);

    if (!password_verify($password, $user['password'])) {
        $_SESSION['sa_error'] = 'Wrong password';
        wp_redirect(home_url('/sa-login'));
        exit;
    }

    $_SESSION['sa_user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'wp_user_id' => $user['wp_user_id'],
    ];
    $wp_user_id = (int) ($_SESSION['sa_user']['wp_user_id'] ?? 0);

    error_log('LOGIN SUCCESS');
    error_log(print_r($_SESSION['sa_user'], true));
    if ($user['role'] === 'admin') {
    wp_redirect(home_url('/wp-admin'));
    exit;
    }
    else if ($user['role'] === 'user') {
    wp_redirect(home_url('/simple-order'));
    exit;
    }
    else {
        $_SESSION['sa_error'] = 'Invalid user role';
        wp_redirect(home_url('/login'));
        exit;
    }
    //wp_redirect(home_url());
    //exit;
}