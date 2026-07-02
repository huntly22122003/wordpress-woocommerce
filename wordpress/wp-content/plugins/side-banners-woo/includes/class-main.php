<?php
/**
 * Lớp chính khởi tạo các thành phần
 */

if (!defined('ABSPATH')) {
    exit;
}

class SideBannersWooCommerce_Main {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Khởi tạo các module
        SBW_Admin::get_instance();
        SBW_Frontend::get_instance();

        // Hook cho text domain
        add_action('init', array($this, 'load_textdomain'));
    }

    public function load_textdomain() {
        load_plugin_textdomain('side-banners-woo', false, dirname(plugin_basename(SBW_PLUGIN_DIR)) . '/languages');
    }
}