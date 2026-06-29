<?php
/**
 * Plugin Name: Login Check Service
 * Description: Microservice ghi log login/logout và register vào auth_db.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

if (!defined('SA_DB_HOST')) {
    define('SA_DB_HOST', 'localhost');
    define('SA_DB_NAME', 'auth_db');
    define('SA_DB_USER', 'root');
    define('SA_DB_PASS', '');
}

define('LCS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LCS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once LCS_PLUGIN_DIR . 'includes/class-db.php';
require_once LCS_PLUGIN_DIR . 'includes/class-login-logger.php';
require_once LCS_PLUGIN_DIR . 'includes/class-admin.php';

$logger = new LoginLogger();
register_activation_hook(__FILE__, array($logger, 'create_table'));

// Login
add_action('wp_login', array($logger, 'log_login'), 10, 2);

// Logout
add_action('wp_logout', array($logger, 'log_logout'), 999);
add_action('clear_auth_cookie', array($logger, 'log_logout'), 999);

// Register
add_action('user_register', array($logger, 'log_register'));

//Reset password
add_action('sa_reset_password_success', array($logger, 'log_reset_password_custom'));

// Admin
$admin = new LCS_Admin();
add_action('admin_init', array($admin, 'handle_view_log'));
add_action('admin_footer', array($admin, 'add_check_log_buttons'));
