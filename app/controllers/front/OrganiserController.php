<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;

use App\Core\Session;
use App\Models\Event;


class OrganiserController extends Controller
{
    public function events(){
        $_SESSION['user_id'] = '4a9ff32f-2dbf-4248-951d-d4258949ef72';
        $event = new Event();
        $events = $event->getByOrganiser($_SESSION['user_id']);
        $data = ['events'=>$events];

        echo View::render('front/myevents.twig', $data);
    }
    public function createEvent(){

    }
    public function deleteEvent(){

    }
    public function updateEvent(){

    }
}