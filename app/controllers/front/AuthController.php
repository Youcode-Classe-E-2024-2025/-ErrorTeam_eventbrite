<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Security;
use App\Core\View;
use App\Core\Validator;
use App\Models\User;
use App\Core\Log;
use HTMLPurifier;
use HTMLPurifier_Config;

class AuthController extends Controller
{
    public function signupForm()
    {
        $csrfToken = Security::generateCsrfToken();
        echo View::render('front/signup.twig', ['csrf_token' => $csrfToken]);
    }

    public function signup()
    {
        $userModel = new User();

        try {
            if (!Security::validateCsrfToken($_POST['csrf_token'])) {
                Log::getLogger()->warning('CSRF token invalid lors de l\'inscription.');
                http_response_code(400);
                echo View::render('front/error500.twig', ['message' => 'CSRF token invalid.']);
                return;
            }

            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            $errors = [];

            if (!Validator::string($username, 3, 50)) {
                $errors['username'] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères.";
            }

            if (!Validator::email($email)) {
                $errors['email'] = "L'adresse email n'est pas valide.";
            }

            if (!Validator::string($password, 8, 255)) {
                $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if ($password !== $confirm_password) {
                $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
            }

            if (!empty($errors)) {
                echo View::render('front/signup.twig', [
                    'errors' => $errors,
                    'username' => $username,
                    'email' => $email
                ]);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($hashedPassword);

            if ($userModel->create($user)) {
                Log::getLogger()->info('Nouvel utilisateur inscrit : ' . htmlspecialchars($email), ['username' => htmlspecialchars($username)]);
                header('Location: /login');
                exit();
            } else {
                Log::getLogger()->error('Erreur lors de l\'enregistrement de l\'utilisateur : ' . htmlspecialchars($email), ['username' => htmlspecialchars($username)]);
                http_response_code(500);
                echo View::render('front/error500.twig', ['message' => 'Erreur lors de l\'enregistrement de l\'utilisateur. Veuillez réessayer plus tard.']);
            }
        } catch (\Exception $e) {
            Log::getLogger()->critical('Exception lors de l\'enregistrement de l\'utilisateur : ' . $e->getMessage(), ['email' => htmlspecialchars($email), 'exception' => $e]);
            http_response_code(500);
            echo View::render('front/error500.twig', ['message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.']);
        }
    }

    public function loginForm()
    {
        $csrfToken = Security::generateCsrfToken();
        echo View::render('front/login.twig', ['csrf_token' => $csrfToken]);
    }

    public function login()
    {
        try {
            if (!Security::validateCsrfToken($_POST['csrf_token'])) {
                Log::getLogger()->warning('CSRF token invalid lors de la connexion.');
                http_response_code(400);
                echo View::render('front/error500.twig', ['message' => 'CSRF token invalid.']);
                return;
            }

            $email = $_POST['email'];
            $password = $_POST['password'];

            $errors = [];

            if (!Validator::email($email)) {
                $errors['email'] = "L'adresse email n'est pas valide.";
            }

            if (!Validator::string($password, 8, 255)) {
                $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if (!empty($errors)) {
                echo View::render('front/login.twig', [
                    'errors' => $errors,
                    'email' => $email
                ]);
                return;
            }

            $userModel = new User();
            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user->getPassword())) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['role_id'] = $user->getRoleId();

                Log::getLogger()->info('Utilisateur connecté : ' . htmlspecialchars($email), ['user_id' => $user->getId()]);

                header('Location: /');
                exit();
            } else {
                Log::getLogger()->warning('Échec de la connexion pour l\'utilisateur : ' . htmlspecialchars($email), ['ip' => $_SERVER['REMOTE_ADDR'], 'url' => $_SERVER['REQUEST_URI']]);
                $errors['login'] = "Email ou mot de passe incorrect.";
                echo View::render('front/login.twig', [
                    'errors' => $errors,
                    'email' => $email
                ]);
            }
        } catch (\Exception $e) {
            Log::getLogger()->critical('Exception lors de la connexion : ' . $e->getMessage(), ['email' => htmlspecialchars($email), 'exception' => $e]);
            http_response_code(500);
            echo View::render('front/error500.twig', ['message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.']);
        }
    }

    public function logout()
    {
        try {
            unset($_SESSION['user_id']);
            session_destroy();

            Log::getLogger()->info('Utilisateur déconnecté.');

            header('Location: /login');
            exit();
        } catch (\Exception $e) {
            Log::getLogger()->critical('Exception lors de la déconnexion : ' . $e->getMessage(), ['exception' => $e]);
            http_response_code(500);
            echo View::render('front/error500.twig', ['message' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.']);
        }
    }
}