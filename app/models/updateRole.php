<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UpdateRole
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Met à jour le rôle d'un utilisateur.
     *
     * @param string $userId
     * @param string $newRole
     * @return bool
     */
    public function updateUserRole(string $userId, string $newRole): bool
    {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':role', $newRole);
        $stmt->bindParam(':id', $userId);

        return $stmt->execute();
    }

    /**
     * Récupère un utilisateur par son ID.
     *
     * @param string $userId
     * @return array|null
     */
    public function getUserById(string $userId): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère tous les utilisateurs.
     *
     * @return array
     */
    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}