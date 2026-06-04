<?php
/*
Plugin Name: Welcome Page
*/

add_action('init', function () {
    add_rewrite_rule(
        '^welcomenewmember/?$',
        'index.php?welcome_page=1',
        'top'
    );
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'welcome_page';
    return $vars;
});

register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

add_action('template_redirect', function () {
    if (get_query_var('welcome_page')) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Welcome</title>
        </head>
        <body>
            <h1>Welcome Hùng!</h1>

            <a href="/">Home</a>
            <br>
            <a href="/shop">Shop</a>
        </body>
        </html>
        <?php
        exit;
    }
});