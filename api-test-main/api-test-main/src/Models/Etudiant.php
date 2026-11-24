<?php

namespace App\Models;

use App\Database;
use PDO;

class Etudiant
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM tbl_etudiant ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tbl_etudiant WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO tbl_etudiant (nom, postnom, prenom, adresse, telephone)
                VALUES (:nom, :postnom, :prenom, :adresse, :telephone)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom' => $data['nom'],
            'postnom' => $data['postnom'],
            'prenom' => $data['prenom'],
            'adresse' => $data['adresse'],
            'telephone' => $data['telephone'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE tbl_etudiant
                SET nom = :nom, postnom = :postnom, prenom = :prenom,
                    adresse = :adresse, telephone = :telephone
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'postnom' => $data['postnom'],
            'prenom' => $data['prenom'],
            'adresse' => $data['adresse'],
            'telephone' => $data['telephone'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM tbl_etudiant WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
