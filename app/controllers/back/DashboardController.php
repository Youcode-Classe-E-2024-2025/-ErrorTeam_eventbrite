<?php

namespace App\Controllers\Back;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session; 

class DashboardController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            echo "Accès non autorisé.";
            return;
        }

        echo View::render('back/dashboard.twig');
    }
}