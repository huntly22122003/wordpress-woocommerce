<?php
/*
Plugin Name: Simple Auth Clean
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

/*thêm backend thì thêm vào*/
require_once plugin_dir_path(__FILE__) . 'includes/auth-register.php';
require_once plugin_dir_path(__FILE__) . 'includes/auth-login.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
/**
 * REWRITE ROUTES
 */
/**  thêm pages html**/
add_action('init', function () {

    add_rewrite_rule('^sa-login/?$', 'index.php?sa_page=login', 'top');
    add_rewrite_rule('^sa-register/?$', 'index.php?sa_page=register', 'top');
});

/**
 * QUERY VAR
 */
add_filter('query_vars', function ($vars) {
    $vars[] = 'sa_page';
    return $vars;
});

/**
 * LOAD VIEW
 */
function sa_template_loader() {

    $page = get_query_var('sa_page');

    if ($page === 'login') {
        include plugin_dir_path(__FILE__) . 'views/login.php';
        exit;
    }

    if ($page === 'register') {
        include plugin_dir_path(__FILE__) . 'views/register.php';
        exit;
    }
}

add_action('template_redirect', 'sa_template_loader');