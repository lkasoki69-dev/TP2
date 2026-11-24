<?php

namespace App\Models;

use App\Database;
use PDO;

class Account
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM account WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, email, created_at FROM account WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $email, string $password): int
    {
        $stmt = $this->db->prepare('INSERT INTO account (email, password) VALUES (:email, :password)');
        $stmt->execute([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return (int) $this->db->lastInsertId();
    }
}
