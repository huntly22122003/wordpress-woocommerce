<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../../DB/AuthDB.php';

class UserModel {

    private $db;

    public function __construct() {
        $this->db = AuthDB::conn();
    }

    public function create($username, $email, $password) {

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([$username, $email, $hash]);
    }

    public function findByEmail($email) {

        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE email = ?
        ");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}