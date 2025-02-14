<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;

use App\Core\Session;
use App\Core\Database; 


class HomeController extends Controller
{
    
    public function index()
    {
        // Générer un token CSRF et le stocker en session
        $csrf_token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $csrf_token);
    
        $user_id = Session::get('user_id'); 
        $role = Session::get('role');
    
        $data = [
            'title' => 'Accueil',
            'content' => 'Bienvenue sur notre site !',
            'user_id' => $user_id,  
            'role' => $role, 
            'csrf_token' => $csrf_token, // Ajouter le token CSRF dans les données envoyées à la vue
        ];
    
        echo View::render('front/home.twig', $data);
    }
    

   
    public function requestOrganizer()
    {
        error_log("requestOrganizer called!"); // Log que la méthode est appelée

        $user_id = Session::get('user_id');
        error_log("User ID: " . $user_id); // Log de l'ID de l'utilisateur

        if (!$user_id) {
            error_log("User not logged in!"); // Log si l'utilisateur n'est pas connecté
            http_response_code(401);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST request received!"); // Log qu'une requête POST est reçue

            // Récupère le token CSRF depuis l'en-tête
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            error_log("CSRF Token from header: " . $csrfToken); // Log du token CSRF

            if (!$csrfToken || $csrfToken !== Session::get('csrf_token')) {
                error_log("CSRF token mismatch!"); // Log si le token CSRF ne correspond pas
                http_response_code(400);
                echo json_encode(['error' => 'Invalid CSRF token.']);
                return;
            }

            $message = trim($_POST['message']);
            error_log("Message: " . $message); // Log du message

            if (empty($message)) {
                error_log("Message is empty!"); // Log si le message est vide
                http_response_code(400);
                echo json_encode(['error' => 'Message cannot be empty.']);
                return;
            }

            try {
                $stmt = Database::getInstance()->getConnection()->prepare(
                    "INSERT INTO organizer_requests (user_id, message, created_at) VALUES (:user_id, :message, NOW())"
                );
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':message', $message);
                $stmt->execute();

                error_log("Request sent successfully!"); // Log si la requête est envoyée avec succès
                http_response_code(200);
                echo json_encode(['success' => 'Request sent successfully']);
                return;

            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage()); // Log de l'erreur de la base de données
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
                return;
            }
        }
        error_log("Method not allowed!"); // Log si la méthode n'est pas autorisée
        http_response_code(405);
        echo json_encode(['error' => 'Methode not allowed']);
        return;
    }
}