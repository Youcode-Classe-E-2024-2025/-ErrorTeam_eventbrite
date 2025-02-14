<?php

namespace App\Controllers\Back;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session; 
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            echo View::render('front/home.twig');
            return;
        }
        $userModel = new User();
        $organizerRequests = $userModel->getOrganizerRequests();
        echo View::render('back/dashboard.twig', ['organizerRequests' => $organizerRequests]);
    }
}