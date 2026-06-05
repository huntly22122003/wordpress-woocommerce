<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '../../DB/AuthDB.php';

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = AuthDB::conn();
    }

    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    public function create(
        $username,
        $gender,
        $age,
        $email,
        $password
    ) {

        $hash = password_hash(
            $password,
            PASSWORD_BCRYPT
        );

        $stmt = $this->db->prepare("
            INSERT INTO users (
                username,
                gender,
                age,
                email,
                password
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $username,
            $gender,
            $age,
            $email,
            $hash
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Find By Email
    |--------------------------------------------------------------------------
    */

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([
            $email
        ]);

        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Find By Phone
    |--------------------------------------------------------------------------
    */

    public function findByPhone($phone)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE phone = ?
            LIMIT 1
        ");

        $stmt->execute([
            $phone
        ]);

        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function verifyLogin(
        $email,
        $password
    ) {

        $user =
            $this->findByEmail(
                $email
            );

        if (!$user) {
            return false;
        }

        if (
            !password_verify(
                $password,
                $user['password']
            )
        ) {
            return false;
        }

        return $user;
    }
}