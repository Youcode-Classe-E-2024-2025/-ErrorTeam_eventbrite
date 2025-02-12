<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class OrganizerRequest
{
    private $id;
    private $message;
    private $status;
    private $user_id;
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getPendingRequests()
    {
        $stmt = $this->db->prepare("
            SELECT r.id, r.message, r.status, u.username
            FROM organizer_requests r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = 'pending'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE organizer_requests 
            SET status = :status 
            WHERE id = :id
        ");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getId() {
        return $this->id;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getUserId() {
        return $this->user_id;
    }
}
