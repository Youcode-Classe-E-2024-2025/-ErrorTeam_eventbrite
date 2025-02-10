<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Session;
use App\Core\View; 
use App\Core\Log; 

Session::start();

$router = require_once __DIR__ . '/../app/config/routes.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

$router->dispatch($method, $uri);