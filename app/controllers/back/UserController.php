<?php

namespace App\Controllers\Back;

use App\Core\Controller;
use App\Core\View;
use App\Models\User;
use App\Core\Session; 

class UserController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            echo "Accès non autorisé.";
            return;
        }

        $userModel = new User();
        $users = $userModel->getAll();

        echo View::render('back/users.twig', ['users' => $users]);
    }
}