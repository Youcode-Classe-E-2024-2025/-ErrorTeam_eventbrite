<?php

namespace Models;

use PDO;
use PDOException;

class OrganizerRequestModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAllRequests() {
        try {
            $sql = "SELECT u.username, u.email, r.created_at 
                    FROM organizer_requests r
                    JOIN users u ON r.user_id = u.id
                    ORDER BY r.created_at DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
