<?php
function sa_logout()
{
    if (!session_id()) {
        session_start();
    }

    // Xoá toàn bộ session user
    unset($_SESSION['sa_user']);
    unset($_SESSION['sa_error']);

    // hoặc destroy sạch luôn session
    session_destroy();

    // (optional) xoá cookie session PHP
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    wp_redirect(home_url('/sa-login'));
    exit;
}
?>