<?php
/**
 * Plugin Name: Side Banners for WooCommerce
 * Plugin URI: https://your-website.com
 * Description: Thêm banner quảng cáo 2 bên cho website WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: side-banners-woo
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa hằng số
define('SBW_VERSION', '1.0.0');
define('SBW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SBW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Kiểm tra WooCommerce trước khi khởi tạo
add_action('admin_notices', 'sbw_check_woocommerce');
function sbw_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>Side Banners for WooCommerce:</strong> Plugin này yêu cầu WooCommerce được cài đặt và kích hoạt.</p>
        </div>
        <?php
    }
}

// Tải các file cần thiết
require_once SBW_PLUGIN_DIR . 'includes/functions.php';
require_once SBW_PLUGIN_DIR . 'includes/class-admin.php';
require_once SBW_PLUGIN_DIR . 'includes/class-frontend.php';
require_once SBW_PLUGIN_DIR . 'includes/class-main.php';

// Khởi chạy plugin
function init_side_banners_woo() {
    return SideBannersWooCommerce_Main::get_instance();
}
add_action('plugins_loaded', 'init_side_banners_woo');