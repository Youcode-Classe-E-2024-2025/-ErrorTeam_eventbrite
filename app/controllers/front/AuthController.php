<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Security;
use App\Core\View;
use App\Core\Validator;
use App\Models\User;
use App\Core\Auth;
use App\Core\Session;
//use App\Services\EmailService; // Suppression de l'utilisation de la classe EmailService

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
            Session::set('error', 'CSRF token invalid. Veuillez réessayer.');
            header('Location: /signup');
            exit();
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
        $user->setRole('user'); // Définir un rôle par défaut

        if ($userModel->create($user)) {
            // Utilisation de la fonction mail() de PHP
            $to = $user->getEmail();
            $userName = $user->getUsername();
            $subject = "Bienvenue sur MonSiteEvenements!";
            $message = "Bonjour $userName,\r\n\r\nMerci de vous être inscrit sur MonSiteEvenements. Nous sommes ravis de vous accueillir!\r\n\r\nCordialement,\r\nL'équipe MonSiteEvenements";
            $headers = "From: belalalla810@example.com\r\n"; // Remplacez par une adresse email valide de votre domaine
            $headers .= "Reply-To: belalallala810@example.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Encodage UTF-8

            if (mail($to, $subject, $message, $headers)) {
                Session::set('success', 'Inscription réussie. Un email de bienvenue vous a été envoyé.');
            } else {
                Session::set('success', 'Inscription réussie, mais l\'envoi de l\'email a échoué.');
            }

            header('Location: /login');
            exit();
        } else {
            Session::set('error', 'Erreur lors de l\'enregistrement de l\'utilisateur. Veuillez réessayer plus tard.');
            header('Location: /signup');
            exit();
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
            Session::set('error', 'CSRF token invalid lors de la connexion.');
            header('Location: /login');
            exit();
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
            Auth::setUser($user); // Utiliser la méthode setUser de Auth
            Session::set('success', 'Connexion réussie.');
            header('Location: /');
            exit();
        } else {
            $errors['login'] = "Email ou mot de passe incorrect.";
            echo View::render('front/login.twig', [
                'errors' => $errors,
                'email' => $email
            ]);
            return;
        }
    }

    public function logout()
    {
        if (Auth::isAuthenticated()) { // Vérifier si l'utilisateur est connecté
            Auth::logout();
        }
        Session::destroy();
        header('Location: /login');
        exit();
    }
}