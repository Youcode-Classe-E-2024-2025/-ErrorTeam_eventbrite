<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;
use App\Core\Log; 

class User
{
    private $id;
    private $role;
    private $username;
    private $email;
    private $password;
    private $is_active;
    private $created_at;
    private $avatar;


    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        
            $stmt = $this->db->prepare("SELECT * FROM users");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
       
    }

    public function getById($id)
    {
       
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchObject(__CLASS__);
       
    }

    public function getUserByEmail($email)
    {
        
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchObject(__CLASS__);
      
    }

    public function create(User $user)
    {
        
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password) 
                VALUES (:username, :email, :password)
            ");
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':email', $user->getEmail());
            $stmt->bindValue(':password', $user->getPassword());

            return $stmt->execute();
      
    }

    public function getId() {
        return $this->id;
    }

    public function getAvatar(){
        return $this->avatar;
    }
    public function setAvatar($avatar){
        $this->avatar = $avatar;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setRole($role) {
        $this->role_id = $role;
    }

    public function getRole() {
        return $this->role;
    }

    public function setIsActive($isActive) {
        $this->is_active = $isActive;
    }

    public function getIsActive() {
        return $this->is_active;
    }
}