<?php
/**
 * Plugin Name: Login Check Service
 * Description: Microservice ghi log login/logout, register, reset password, login failed và rate limiting.
 * Version: 1.2
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
require_once LCS_PLUGIN_DIR . 'includes/class-rate-limiter.php';
require_once LCS_PLUGIN_DIR . 'includes/class-admin.php';

$logger = new LoginLogger();
$rate_limiter = new LoginRateLimiter();

register_activation_hook(__FILE__, array($logger, 'create_table'));
register_activation_hook(__FILE__, array($rate_limiter, 'create_table'));

// Login thành công (WordPress hook)
add_action('wp_login', array($logger, 'log_login'), 10, 2);
// Hook tùy chỉnh từ auth-login.php để reset counter
add_action('sa_login_success', array($rate_limiter, 'reset_attempts'), 10, 1);

// Login thất bại (hook từ auth-login.php)
add_action('sa_login_failed', array($rate_limiter, 'log_failed_attempt'), 10, 1);
add_action('sa_login_failed', array($logger, 'log_login_failed'), 10, 1);

// Chặn đăng nhập nếu bị khóa (filter authenticate)
add_filter('authenticate', array($rate_limiter, 'check_blocked'), 10, 1);

// Logout
add_action('wp_logout', array($logger, 'log_logout'), 999);
add_action('clear_auth_cookie', array($logger, 'log_logout'), 999);

// Register
add_action('user_register', array($logger, 'log_register'));

// Reset password (hook từ auth-otp-forgot)
add_action('sa_reset_password_success', array($logger, 'log_reset_password_custom'));

// Admin
$admin = new LCS_Admin();
add_action('admin_init', array($admin, 'handle_view_log'));
add_action('admin_footer', array($admin, 'add_check_log_buttons'));