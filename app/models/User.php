<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;
use App\Core\Log; 

class User
{
    private $id;
    private $role_id;
    private $username;
    private $email;
    private $password;
    private $first_name;
    private $last_name;
    private $is_active;
    private $created_at;
    private $updated_at;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        // try {
            $stmt = $this->db->prepare("SELECT * FROM users");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        // } catch (PDOException $e) {
        //     Log::getLogger()->error('Erreur lors de la récupération de tous les utilisateurs : ' . $e->getMessage());
        //     return [];
        // }
    }

    public function getById($id)
    {
        // try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchObject(__CLASS__);
        // } catch (PDOException $e) {
        //     Log::getLogger()->error('Erreur lors de la récupération de l\'utilisateur par ID : ' . $e->getMessage(), ['user_id' => $id]);
        //     return null;
        // }
    }

    public function getUserByEmail($email)
    {
        // try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchObject(__CLASS__);
        // } catch (PDOException $e) {
        //     Log::getLogger()->error('Erreur lors de la récupération de l\'utilisateur par email : ' . $e->getMessage(), ['email' => $email]);
        //     return null;
        // }
    }

    public function create(User $user)
    {
        // try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password) 
                VALUES (:username, :email, :password)
            ");
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':email', $user->getEmail());
            $stmt->bindValue(':password', $user->getPassword());

            return $stmt->execute();
        // } catch (PDOException $e) {
        //     Log::getLogger()->error('Erreur lors de la création de l\'utilisateur : ' . $e->getMessage(), ['email' => $user->getEmail(), 'username' => $user->getUsername()]);
        //     return false;
        // }
    }

    public function getId() {
        return $this->id;
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

    public function setRoleId($roleId) {
        $this->role_id = $roleId;
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