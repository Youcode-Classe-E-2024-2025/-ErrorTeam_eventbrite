<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;
use App\Models\User;

use App\Core\Log;

class HomeController extends Controller
{
    public function index()
    {
        // try {
            $userModel = new User();
            $users = $userModel->getAll();

            $data = [
                'title' => 'Bienvenue sur la page d\'accueil',
                'content' => 'Ceci est le contenu de la page d\'accueil.',
                'users' => $users,
            
                'user_id' => $_SESSION['user_id'] ?? null,
                'role_id' => $_SESSION['role_id'] ?? null
            ];

            echo View::render('front/home.twig', $data);
        // } catch (\Exception $e) {
        //     Log::getLogger()->critical('Exception lors de l\'affichage de la page d\'accueil : ' . $e->getMessage(), ['exception' => $e]);
        //     http_response_code(500);
        //     echo View::render('front/error500.twig', ['message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.']);
        // }
    }
}