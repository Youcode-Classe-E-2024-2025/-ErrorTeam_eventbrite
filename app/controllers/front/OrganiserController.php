<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;

use App\Core\Session;
use App\Models\Category;
use App\Models\Event;


class OrganiserController extends Controller
{
    public function events()
    {
        $_SESSION['user_id'] = '4a9ff32f-2dbf-4248-951d-d4258949ef72';
        $event = new Event();
        $events = $event->getByOrganiser($_SESSION['user_id']);
        $category = new Category();
        $categories = $category->getAll();
        $data = ['events' => $events, 'categories' => $categories];
        echo View::render('front/myevents.twig', $data);
    }
    public function createEvent()
    {
        foreach ($_POST as $key => $value) {
            $$key = $value;
        }
        $event = new Event();
        $event->setOrganizerId($_SESSION['user_id']);
        $event->setCategoryId($category);
        $event->setTitle($title);
        $event->setDescription($description);
        $event->setDateStart($date_start);
        $event->setDateEnd($date_end);
        $event->setLocation($location);
        $event->setPrice($price);
        $event->setCapacity($capacity);
        $event->save();
        header('Location: /myevents');
    }
    public function deleteEvent($id)
    {
        $event = new Event();
        $event->delete($id);
        header('Location: /myevents');
    }
    public function updateEvent()
    {

    }
}