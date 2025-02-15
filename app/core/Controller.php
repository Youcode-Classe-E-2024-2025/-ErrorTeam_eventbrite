<?php

namespace App\Core;

use App\Core\Session;
use App\Core\Security;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Core\Database;


class Controller
{

    protected $twig;
    protected $db;

    public function __construct()
    {
        Session::start();

        // Initialiser Twig (si ce n'est pas déjà fait)
        $loader = new FilesystemLoader('../app/views'); // Chemin vers vos templates
        $this->twig = new Environment($loader, [
            'cache' => false, // Activer le cache en production
        ]);
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (\Exception $e) {
            // Gérer l'erreur de connexion à la base de données
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
        $this->twig->addGlobal('session', $_SESSION); // rendre la session disponible dans les templates
    }

    protected function validateCsrfToken($token): bool
    {
        return Security::validateCsrfToken($token);
    }

    protected function render(string $view, array $data = [])
    {
        try {
            echo $this->twig->render($view, $data);
        } catch (\Twig\Error\Error $e) {
            // Gérer les erreurs de template Twig
            echo "Erreur de template Twig: " . $e->getMessage();
        }
    }

    /**
     * Redirige vers une URL donnée.
     *
     * @param string $url L'URL vers laquelle rediriger.
     */
    public function redirect(string $url)
    {
        header('Location: ' . $url);
        exit();
    }
}