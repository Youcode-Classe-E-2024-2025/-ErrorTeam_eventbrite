<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Ramsey\Uuid\Uuid;

class Category
{
    private $id;
    private $name;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }


    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM categories");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchObject(__CLASS__);
    }

    public function getCategoryCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM categories");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
