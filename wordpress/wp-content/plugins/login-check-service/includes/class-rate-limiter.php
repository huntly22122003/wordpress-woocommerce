<?php
class LoginRateLimiter {

    private $db;
    private $max_attempts = 5;
    private $block_duration = 30; // giây

    public function __construct() {
        $this->db = LoginCheckDB::conn();
    }

    public function create_table() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
                email VARCHAR(100) NOT NULL PRIMARY KEY,
                failed_count INT DEFAULT 1,
                first_attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                blocked_until DATETIME DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log('LoginRateLimiter: Lỗi tạo bảng - ' . $e->getMessage());
        }
    }

    /**
     * Kiểm tra xem email có đang bị khóa không
     */
    public function check_blocked($user) {
        if (is_wp_error($user)) return $user;

        $email = isset($_POST['log']) ? sanitize_email($_POST['log']) : '';
        if (empty($email)) return $user;

        $stmt = $this->db->prepare("SELECT blocked_until FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['blocked_until']) {
            $blocked_until = new DateTime($row['blocked_until']);
            $now = new DateTime();
            if ($now < $blocked_until) {
                $remaining = $blocked_until->getTimestamp() - $now->getTimestamp();
                return new WP_Error(
                    'login_blocked',
                    sprintf(
                        'Tài khoản tạm thời bị khóa do nhập sai mật khẩu quá nhiều. Vui lòng thử lại sau %d giây.',
                        $remaining
                    )
                );
            } else {
                $this->db->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);
            }
        }

        return $user;
    }

    /**
     * Ghi log thất bại, tăng counter, khóa nếu vượt ngưỡng
     */
    public function log_failed_attempt($email) {
        if (empty($email)) return;

        $now = new DateTime();
        $now_str = $now->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("SELECT * FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $first_time = new DateTime($row['first_attempt_time']);
            $diff = $now->getTimestamp() - $first_time->getTimestamp();
            if ($diff > $this->block_duration) {
                $sql = "UPDATE login_attempts SET failed_count = 1, first_attempt_time = ? WHERE email = ?";
                $stmt2 = $this->db->prepare($sql);
                $stmt2->execute([$now_str, $email]);
                $new_count = 1;
            } else {
                $new_count = $row['failed_count'] + 1;
                $sql = "UPDATE login_attempts SET failed_count = ? WHERE email = ?";
                $stmt2 = $this->db->prepare($sql);
                $stmt2->execute([$new_count, $email]);
            }
        } else {
            $sql = "INSERT INTO login_attempts (email, failed_count, first_attempt_time) VALUES (?, 1, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $now_str]);
            $new_count = 1;
        }

        if ($new_count >= $this->max_attempts) {
            $blocked_until = $now->add(new DateInterval('PT' . $this->block_duration . 'S'));
            $blocked_str = $blocked_until->format('Y-m-d H:i:s');
            $sql = "UPDATE login_attempts SET blocked_until = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$blocked_str, $email]);
            error_log("LoginRateLimiter: Locked email $email until $blocked_str");
        }
    }

    /**
     * Reset số lần thử khi đăng nhập thành công
     */
    public function reset_attempts($email) {
        if (empty($email)) return;
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
    }
}