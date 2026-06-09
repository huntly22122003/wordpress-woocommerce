<?php
/*
Plugin Name: SA Home Shop
Version: 1.0
*/

defined('ABSPATH') || exit;

add_action('template_redirect', function () {

    if (is_front_page()) {

        echo sa_home_shop_full();
        exit;
    }
});

function sa_home_shop_full()
{
    ob_start();
    include plugin_dir_path(__FILE__) . 'welcome-page.php';
    return ob_get_clean();
}
