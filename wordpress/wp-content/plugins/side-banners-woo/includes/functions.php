<?php
/**
 * Các hàm tiện ích dùng chung
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Kiểm tra xem có nên hiển thị banner hay không
 */
function sbw_should_display_banners() {
    // Kiểm tra WooCommerce
    if (!class_exists('WooCommerce')) {
        return false;
    }

    // Kiểm tra mobile
    if (!get_option('sbw_show_on_mobile', 0) && wp_is_mobile()) {
        return false;
    }

    // Kiểm tra chỉ hiển thị trên shop
    if (get_option('sbw_show_only_shop', 0)) {
        return is_shop() || is_product_category() || is_product_tag() || is_singular('product');
    }

    return true;
}

/**
 * Lấy giá trị cookie
 */
function sbw_get_cookie($name) {
    if (isset($_COOKIE[$name])) {
        return sanitize_text_field($_COOKIE[$name]);
    }
    return null;
}