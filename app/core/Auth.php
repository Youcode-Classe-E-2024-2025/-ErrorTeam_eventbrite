<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function getUser(): ?User
    {
        if (self::isAuthenticated()) {
            $userModel = new User();
            $userId = $_SESSION['user_id'];
            return $userModel->getById($userId);
        }
        return null; // Correction : retourne null au lieu de false
    }

    public static function setUser(User $user): void
    {
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['role'] = $user->getRole();
    }


    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['role']);
    }

    public static function hasRole(string $role): bool
    {
        if (self::isAuthenticated() && isset($_SESSION['role'])) {
            return $_SESSION['role'] === $role;
        }
        return false;
    }
}