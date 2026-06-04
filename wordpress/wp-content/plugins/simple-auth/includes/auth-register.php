<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'Models/UserModel.php';

/**
 * HANDLE REGISTER
 */
function sa_handle_register() {

    if (!isset($_POST['sa_register_submit'])) {
        return;
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        return "Missing required fields";
    }

    $model = new UserModel();

    // check exists
    $existing = $model->findByEmail($email);

    if ($existing) {
        return "Email already exists";
    }

    $result = $model->create($username, $email, $password);

    if (!$result) {
        return "Register failed";
    }

    return "Register success";
}

/**
 * SHORTCODE / VIEW RENDER SUPPORT (optional)
 */
function sa_register_form() {
    ob_start();

    $msg = sa_handle_register();
    if ($msg) {
        echo "<p>$msg</p>";
    }

    return ob_get_clean();
}

add_shortcode('sa_register', 'sa_register_form');