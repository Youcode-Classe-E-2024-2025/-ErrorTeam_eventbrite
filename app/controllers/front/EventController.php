<?php

namespace App\Controllers\Front;  

use App\Core\Controller;
use App\Core\View;
use App\Models\Event;
use App\Core\Session;

class EventController extends Controller
{
    public function index()
    {
        $eventModel = new Event();
        $events = $eventModel->getAll();
        if ($events === false) {
            echo "Erreur index eventcontroller";
            return; 
        }
        echo View::render('front/events/index.twig', ['events' => $events]);
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