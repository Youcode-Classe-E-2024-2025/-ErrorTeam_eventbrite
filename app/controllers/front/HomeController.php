<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;

class HomeController extends Controller
{
    public function index()
    {
        $user_id = Session::get('user_id'); 
        $role = Session::get('role');
        $data = [
            'title' => 'Accueil',
            'content' => 'Bienvenue sur notre site !',
            'user_id' => $user_id,  
            'role' => $role, 
        ];

        echo View::render('front/home.twig', $data);
    }
}