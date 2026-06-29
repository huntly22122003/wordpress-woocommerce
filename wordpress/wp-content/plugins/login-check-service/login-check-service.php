<?php
/**
 * Plugin Name: Login Check Service
 * Description: Ghi log login/logout vào auth_db và hiển thị log trên trang simple-auth.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sử dụng hằng số từ plugin AuthDB (nếu chưa defined thì tự định nghĩa mặc định)
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

// Hook login
add_action('wp_login', array($logger, 'log_login'), 10, 2);

// Hook logout (dùng cả hai để đảm bảo)
add_action('wp_logout', array($logger, 'log_logout'), 999);
add_action('clear_auth_cookie', array($logger, 'log_logout'), 999);

$admin = new LCS_Admin();
add_action('admin_init', array($admin, 'handle_view_log'));
add_action('admin_footer', array($admin, 'add_check_log_buttons'));