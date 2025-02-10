<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function checkPermission(string $permissionName): bool
    {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userModel = new User();
            $user = $userModel->getById($userId);
            if ($user) {
                return $user->hasPermission($permissionName);
            } else {
                return false; 
            }
        } else {
            return false; 
        }
        
    }
}