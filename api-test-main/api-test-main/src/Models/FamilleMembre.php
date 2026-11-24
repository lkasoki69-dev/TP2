<?php

namespace App\Models;

use App\Database;
use PDO;

class FamilleMembre
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM famille_membre ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM famille_membre WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO famille_membre (nom, postnom, prenom, lieu_naissance, date_naissance, image, parente)
                VALUES (:nom, :postnom, :prenom, :lieu_naissance, :date_naissance, :image, :parente)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom' => $data['nom'],
            'postnom' => $data['postnom'],
            'prenom' => $data['prenom'],
            'lieu_naissance' => $data['lieu_naissance'],
            'date_naissance' => $data['date_naissance'],
            'image' => $data['image'] ?? null,
            'parente' => $data['parente'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE famille_membre
                SET nom = :nom, postnom = :postnom, prenom = :prenom,
                    lieu_naissance = :lieu_naissance, date_naissance = :date_naissance,
                    image = :image, parente = :parente
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'postnom' => $data['postnom'],
            'prenom' => $data['prenom'],
            'lieu_naissance' => $data['lieu_naissance'],
            'date_naissance' => $data['date_naissance'],
            'image' => $data['image'] ?? null,
            'parente' => $data['parente'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM famille_membre WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
