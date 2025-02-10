<?php

require_once __DIR__ . '/../vendor/autoload.php';


use App\Core\Session;
use App\Core\View; 
use App\Core\Log; 


Session::start();

// set_exception_handler(function ($e) {
//     Log::getLogger()->critical('Exception non gérée : ' . $e->getMessage(), ['exception' => $e]);
//     http_response_code(500);
//     echo View::render('front/error500.twig', ['message' => 'Une erreur interne s\'est produite. Veuillez réessayer plus tard.']);
// });


$router = require_once __DIR__ . '/../app/config/routes.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);