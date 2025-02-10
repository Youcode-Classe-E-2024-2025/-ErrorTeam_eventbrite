<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Article
{
    private $id;
    private $user_id;
    private $title;
    private $slug;
    private $content;
    private $is_published;
    private $created_at;
    private $updated_at;

    private $db; 

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM articles");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchObject(__CLASS__);
    }
    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getContent() {
        return $this->content;
    }

    public function getIsPublished() {
        return $this->is_published;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    
}