<?php
namespace App\Core;

class Security {

    public static function generateCsrfToken() {
        try {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
            }
            return $_SESSION['csrf_token'];
        } catch (\Exception $e) {
            error_log("Erreur lors de la génération du token CSRF : " . $e->getMessage());
            return null; 
        }
    }
    
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }

    public static function xssClean($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}