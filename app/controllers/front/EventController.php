<?php

namespace App\Controllers\Front;  

use App\Core\Controller;
use App\Core\View;
use App\Models\Event;
use App\Core\Session;
use App\Models\Category;

class EventController extends Controller
{
    public function index()
    {
        $eventModel = new Event();
        $searchQuery = $_GET['search'] ?? null;
        $categoryId = $_GET['category'] ?? null;

        if ($searchQuery) {
            $events = $eventModel->search($searchQuery);
        } elseif ($categoryId) {
            $events = $eventModel->getByCategory($categoryId);
        } else {
            $events = $eventModel->getAll();
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        if ($events === false || $categories === false) {
            echo "Erreur lors de la récupération des événements ou des catégories.";
            return;
        }

        // echo View::render('front/events/index.twig', ['events' => $events, 'categories' => $categories, 'searchQuery' => $searchQuery, 'categoryId' => $categoryId]);
        $user_id = Session::get('user_id');
        $username = Session::get('username');
        $role = Session::get('role');

        echo View::render('front/events/index.twig', [
            'events' => $events,
            'categories' => $categories,
            'searchQuery' => $searchQuery,
            'categoryId' => $categoryId,
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
        ]);
    }

    public function show($id) 
    {
        $eventModel = new Event();
        $event = $eventModel->getById($id);
        if (!$event) {
            echo "Erreur show eventcontroller";
            return;
        }
        echo View::render('front/events/show.twig', ['event' => $event]);
    }

}