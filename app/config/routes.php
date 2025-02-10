<?php

use App\Controllers\Front\HomeController;
use App\Controllers\Front\ArticleController;
use App\Controllers\Front\ContactController;
use App\Controllers\Front\AuthController;
use App\Controllers\Back\DashboardController; 
use App\Controllers\Back\UserController;

$router = new App\Core\Router();

$router->addRoute('GET', '/', HomeController::class, 'index');
$router->addRoute('GET', '/articles', ArticleController::class, 'index');
$router->addRoute('GET', '/articles/{id}', ArticleController::class, 'show');
$router->get('/contact', ContactController::class, 'index', 'contact.index');
$router->post('/contact', ContactController::class, 'submit', 'contact.submit');
$router->get('/signup', AuthController::class, 'signupForm', 'signup.form');
$router->post('/signup', AuthController::class, 'signup', 'signup.submit');
$router->get('/login', AuthController::class, 'loginForm', 'login.form');
$router->post('/login', AuthController::class, 'login', 'login.submit');
$router->get('/logout', AuthController::class, 'logout', 'logout');
$router->get('/admin/dashboard', DashboardController::class, 'index', 'admin.dashboard');
$router->get('/admin/users', UserController::class, 'index', 'admin.users');

return $router;