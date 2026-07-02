<?php
/**
 * Xử lý admin: menu, settings, trang cài đặt
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBW_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Side Banners', 'side-banners-woo'),
            __('Side Banners', 'side-banners-woo'),
            'manage_options',
            'side-banners-woo',
            array($this, 'render_admin_page'),
            'dashicons-format-image',
            30
        );

        add_submenu_page(
            'side-banners-woo',
            __('Cài đặt', 'side-banners-woo'),
            __('Cài đặt', 'side-banners-woo'),
            'manage_options',
            'side-banners-woo-settings',
            array($this, 'render_admin_page')
        );
    }

    public function register_settings() {
        // Banner trái
        register_setting('sbw_settings', 'sbw_left_banner_enabled');
        register_setting('sbw_settings', 'sbw_left_banner_image');
        register_setting('sbw_settings', 'sbw_left_banner_link');
        register_setting('sbw_settings', 'sbw_left_banner_width');
        register_setting('sbw_settings', 'sbw_left_banner_height');

        // Banner phải
        register_setting('sbw_settings', 'sbw_right_banner_enabled');
        register_setting('sbw_settings', 'sbw_right_banner_image');
        register_setting('sbw_settings', 'sbw_right_banner_link');
        register_setting('sbw_settings', 'sbw_right_banner_width');
        register_setting('sbw_settings', 'sbw_right_banner_height');

        // Cài đặt chung
        register_setting('sbw_settings', 'sbw_show_on_mobile');
        register_setting('sbw_settings', 'sbw_show_only_shop');
        register_setting('sbw_settings', 'sbw_banner_position');
        register_setting('sbw_settings', 'sbw_close_button');
    }

    /**
     * Tải script media uploader cho admin
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'side-banners-woo') === false) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }

    /**
     * Hiển thị trang cài đặt (gọi view)
     */
    public function render_admin_page() {
        // Kiểm tra quyền
        if (!current_user_can('manage_options')) {
            wp_die(__('Bạn không có quyền truy cập trang này.', 'side-banners-woo'));
        }

        // Include view
        include SBW_PLUGIN_DIR . 'views/admin-page.php';
    }
}