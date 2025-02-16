<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;
use App\Models\Event;
use App\Models\Category;
use App\Core\Session;

class EventController extends Controller
{
    private const EVENTS_PER_PAGE = 8; 

    public function index()
    {
        $page = $_GET['page'] ?? 1; 
        $page = max(1, (int)$page); 

        $eventModel = new Event();
        $searchQuery = $_GET['search'] ?? null;
        $categoryId = $_GET['category'] ?? null;

        // Récupère le nombre total d'événements (pour la pagination) :
        $totalEvents = $this->getTotalEvents($searchQuery, $categoryId);

        $totalPages = ceil($totalEvents / self::EVENTS_PER_PAGE);

        $events = $this->getEventsForPage($page, $searchQuery, $categoryId);

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        if ($events === false || $categories === false) {
            echo "Erreur lors de la récupération des événements ou des catégories.";
            return;
        }

        $user_id = Session::get('user_id');
        $username = Session::get('username');
        $role = Session::get('role');

        // Si c'est une requête AJAX, renvoie uniquement le contenu des événements :
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo View::render('front/events/_event_list.twig', [
                'events' => $events,
                'user_id' => $user_id,
                'username' => $username,
                'role' => $role,
                'totalPages' => $totalPages,  
                'currentPage' => $page,      
            ]);
        } else {

            echo View::render('front/events/index.twig', [
                'events' => $events,
                'categories' => $categories,
                'searchQuery' => $searchQuery,
                'categoryId' => $categoryId,
                'user_id' => $user_id,
                'username' => $username,
                'role' => $role,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
        }
    }

     private function getTotalEvents(?string $searchQuery, ?string $categoryId): int
    {
        $eventModel = new Event();
        if ($searchQuery) {
            return $eventModel->getTotalSearchResults($searchQuery);
        } elseif ($categoryId) {
            return $eventModel->getTotalEventsByCategory($categoryId);
        } else {
            return $eventModel->getTotalEvents();
        }
    }

     private function getEventsForPage(int $page, ?string $searchQuery, ?string $categoryId)
    {
        $eventModel = new Event();
        $offset = ($page - 1) * self::EVENTS_PER_PAGE;
        if ($searchQuery) {
            return $eventModel->search($searchQuery, self::EVENTS_PER_PAGE, $offset);
        } elseif ($categoryId) {
            return $eventModel->getByCategory($categoryId, self::EVENTS_PER_PAGE, $offset);
        } else {
            return $eventModel->getAll(self::EVENTS_PER_PAGE, $offset);
        }
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