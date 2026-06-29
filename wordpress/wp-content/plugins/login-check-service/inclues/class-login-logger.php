<?php
class LoginLogger {

    private $db;

    public function __construct() {
        $this->db = LoginCheckDB::conn();
    }

    public function create_table() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS login_check (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL,
                action VARCHAR(10) NOT NULL,
                login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log('LoginLogger: Lỗi tạo bảng - ' . $e->getMessage());
        }
    }

    public function log_login($user_login, $user) {
        $email = $user->user_email;
        set_transient('lcs_logout_email', $email, 300);
        $this->insert_log($email, 'login');
    }

    public function log_logout() {
        $email = get_transient('lcs_logout_email');
        if (!empty($email)) {
            delete_transient('lcs_logout_email');
            $this->insert_log($email, 'logout');
            error_log('LoginLogger: logout logged via transient for ' . $email);
            return;
        }

        $user = wp_get_current_user();
        if ($user->exists()) {
            $email = $user->user_email;
            $this->insert_log($email, 'logout');
            error_log('LoginLogger: logout logged via user for ' . $email);
            return;
        }

        error_log('LoginLogger: logout called but no email found');
    }

    public function insert_log($email, $action) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt = $this->db->prepare(
                "INSERT INTO login_check (email, action, ip_address, user_agent) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$email, $action, $ip, $agent]);
        } catch (PDOException $e) {
            error_log('LoginLogger: Lỗi insert log - ' . $e->getMessage());
        }
    }

    public function get_logs_by_email($email) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM login_check WHERE email = ? ORDER BY login_time DESC"
            );
            $stmt->execute([$email]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('LoginLogger: Lỗi lấy log - ' . $e->getMessage());
            return [];
        }
    }
}