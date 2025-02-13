<?php

namespace App\Models;

use App\Core\Database;
use PDO;



class Category
{ 
    private $id;
    private $name;

    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getId(){
        return $this->id;
    }
    public function getName(){
        return $this->name;
    }
    public function setId($id){
        return $this->id = $id;
    }
    public function setName($name){
        return $this->name = $name;
    }
    public function getAll(){
        $stmt = $this->db->prepare("SELECT * FROM categories");
        $stmt->execute();
        $cgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $categories = [];
        foreach($cgs as $cg) {
            $category = new self();
            $category->setId($cg['id']);
            $category->setName($cg['name']);
            $categories[] = $category;
        }
        return $categories;
    }
}