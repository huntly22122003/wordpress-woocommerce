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
                action VARCHAR(20) NOT NULL,
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
            return;
        }
        $user = wp_get_current_user();
        if ($user->exists()) {
            $this->insert_log($user->user_email, 'logout');
        }
    }

    public function log_register($user_id) {
        $user = get_userdata($user_id);
        if ($user) {
            $this->insert_log($user->user_email, 'register');
        }
    }

    // ===== HÀM MỚI CHO RESET PASSWORD =====
    public function log_reset_password_custom($email) {
        if (!empty($email)) {
            $this->insert_log($email, 'reset_password');
            error_log("LoginLogger: reset_password logged for $email");
        }
    }

    private function insert_log($email, $action) {
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