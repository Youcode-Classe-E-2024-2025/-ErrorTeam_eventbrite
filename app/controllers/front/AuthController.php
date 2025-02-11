<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Security;
use App\Core\View;
use App\Core\Validator;
use App\Models\User;
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
        if (!Security::validateCsrfToken($_POST['csrf_token'])) {
            echo 'CSRF token invalid. AuthController methode signup ';
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
            header('Location: /login');
            exit();
        } else {
            echo 'Erreur lors de l\'enregistrement de l\'utilisateur. Veuillez réessayer plus tard.';
        }
    }

    public function loginForm()
    {
        $csrfToken = Security::generateCsrfToken();
        echo View::render('front/login.twig', ['csrf_token' => $csrfToken]);
    }

    public function login()
    {

        if (!Security::validateCsrfToken($_POST['csrf_token'])) {
            echo 'CSRF token invalid lors de la connexion.';
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
            $_SESSION['role'] = $user->getRole();
            header('Location: /');
            exit();
        } else {
            $errors['login'] = "Email ou mot de passe incorrect.";
            echo View::render('front/login.twig', [
                'errors' => $errors,
                'email' => $email
            ]);
        }

    }

    public function logout()
    {

        unset($_SESSION['user_id']);
        unset($_SESSION['role']); // Supprimer également le rôle
        //session_destroy(); // Pas nécessaire de détruire toute la session
        header('Location: /login');
        exit();

    }
}