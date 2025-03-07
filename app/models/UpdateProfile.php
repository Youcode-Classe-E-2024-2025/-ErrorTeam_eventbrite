<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Session;
use PDO;

class UpdateProfile
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function updateProfile($user, $data, $avatar = null)
    {
        
        $username = trim($data['username'] ?? $user->getUsername());
        $firstName = trim($data['first_name'] ?? $user->getFirstName());
        $lastName = trim($data['last_name'] ?? $user->getLastName());
        $phoneNumber = trim($data['phone_number'] ?? $user->getPhoneNumber());
        $bio = trim($data['bio'] ?? $user->getBio());

        
        if (strlen($username) > 255 || strlen($firstName) > 255 || strlen($lastName) > 255 || strlen($phoneNumber) > 20) {
            Session::set('error', 'Certains champs dépassent la longueur autorisée.');
            return false;
        }

        
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setPhoneNumber($phoneNumber);
        $user->setBio($bio);

        
        if ($avatar) {
            $user->setAvatar($avatar);
        }

        
        $pdo = $this->db->getConnection();

        
        $stmt = $pdo->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, phone_number = :phone_number, bio = :bio, avatar = :avatar WHERE id = :id");
        $stmt->bindValue(':username', $user->getUsername());
        $stmt->bindValue(':first_name', $user->getFirstName());
        $stmt->bindValue(':last_name', $user->getLastName());
        $stmt->bindValue(':phone_number', $user->getPhoneNumber());
        $stmt->bindValue(':bio', $user->getBio());
        $stmt->bindValue(':avatar', $user->getAvatar());
        $stmt->bindValue(':id', $user->getId());

        
        if ($stmt->execute()) {
           
            $_SESSION['first_name'] = $user->getFirstName();
            $_SESSION['last_name'] = $user->getLastName();
            $_SESSION['phone_number'] = $user->getPhoneNumber();
            return true;
           
        } else {
            Session::set('error', 'Erreur lors de la mise à jour du profil.');
            return false;
        }
    }
}