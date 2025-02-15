<?php

use App\Controllers\Front\HomeController;
use App\Controllers\Front\ArticleController;
use App\Controllers\Front\ContactController;
use App\Controllers\Front\AuthController;
use App\Controllers\Back\DashboardController; 
use App\Controllers\Back\UserController;
use App\Controllers\Front\EventController;  
use App\Controllers\Front\ReservationController;
use App\Controllers\Front\OrganiserController;
use App\Controllers\Front\ReservationController;
use App\Core\Router;
use App\Controllers\Front\RoleController;
use App\Controllers\Back\ProfileController;

$router = new Router();

// Routes Front
$router->addRoute('GET', '/', HomeController::class, 'index');
$router->post('/request', HomeController::class, 'requestOrganizer');
$router->get('/signup', AuthController::class, 'signupForm', 'signup.form');
$router->post('/signup', AuthController::class, 'signup', 'signup.submit');
$router->get('/login', AuthController::class, 'loginForm', 'login.form');
$router->post('/login', AuthController::class, 'login', 'login.submit');
$router->get('/logout', AuthController::class, 'logout', 'logout');
$router->get('/profile', App\Controllers\Front\ProfileController::class, 'index', 'profile.index');
$router->post('/profile/update', App\Controllers\Front\ProfileController::class, 'update', 'profile.update');
$router->get('/events', EventController::class, 'index', 'events.index');
$router->get('/myevents', OrganiserController::class, 'events', 'events.index');
$router->get('/myevents', OrganiserController::class, 'events', 'events.index');
$router->get('/events/{id}', EventController::class, 'show', 'events.show');
$router->get('/delete-event/{id}', OrganiserController::class, 'deleteEvent', 'events.show');
$router->post('/create-event', OrganiserController::class, 'createEvent', 'events.show');
$router->get('/events/{event_id}/reservations/create', ReservationController::class, 'createForm', 'reservations.create.form');
$router->post('/events/{event_id}/reservations/create', ReservationController::class, 'create', 'reservations.create');
$router->get('/events/{event_id}/reservations/payment', ReservationController::class, 'paymentForm', 'reservations.payment.form');
$router->get('/role', RoleController::class, 'index', 'role.index');
$router->post('/role/{userId}/update', RoleController::class, 'updateRole', 'role.update');

// Routes Back (admin)
$router->get('/admin/dashboard', DashboardController::class, 'index', 'admin.dashboard');
$router->get('/admin/users', UserController::class, 'index', 'admin.users');
$router->get('/admin/users/{id}', UserController::class, 'show', 'admin.users.show');

// Route pour récupérer les demandes d'organisateur
$router->get('/admin/organizer-requests', DashboardController::class, 'organizerRequests', 'admin.organizer.requests');

// Route Profile
$router->get('/profile', App\Controllers\Front\ProfileController::class, 'index', 'profile.index');
$router->post('/profile/update', App\Controllers\Front\ProfileController::class, 'update', 'profile.update');


$router->get('/events/{event_id}/reservations/payment', ReservationController::class, 'paymentForm', 'reservations.payment.form');
$router->get('/profile/update', App\Controllers\Front\ProfileController::class, 'show', 'profile.show');

// Ajout des routes pour gérer les réservations, si nécessaire
$router->get('/events/{event_id}/reservations', ReservationController::class, 'index', 'reservations.index');

// Route pour confirmer un organisateur
$router->post('/admin/confirm-organizer/{id}', DashboardController::class, 'confirmOrganizer', 'admin.confirm.organizer');

// Route pour afficher les derniers événements (si nécessaire)
$router->get('/admin/latest-events', DashboardController::class, 'latestEvents', 'admin.latest.events');

return $router;
