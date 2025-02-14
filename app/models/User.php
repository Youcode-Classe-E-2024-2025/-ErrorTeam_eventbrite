<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;
use App\Core\Log;
use Ramsey\Uuid\Uuid; 

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

    public function getOrganizerRequests()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'request_organizer'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    public function create(User $user)
    {
        try {
            $uuid = Uuid::uuid4()->toString(); 
            $stmt = $this->db->prepare("
                INSERT INTO users (id, username, email, password)
                VALUES (:id, :username, :email, :password)
            ");
            $stmt->bindValue(':id', $uuid); 
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':email', $user->getEmail());
            $stmt->bindValue(':password', $user->getPassword());

            $result = $stmt->execute();

            if ($result) {
                $user->setId($uuid); 
                return true;
            } else {
                return false; 
            }

        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false; 
        }

    }

    public function getId() {
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
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
        $this->role = $role;  
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