<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Models\User;
use App\Core\Auth;
use App\Core\Profile;
use App\Core\Session;
use Ramsey\Uuid\Uuid;

class ProfileController extends Controller
{
    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::isAuthenticated()) {
            // Rediriger vers la page de connexion si non connecté
            header('Location: /login');
            exit;
        }

        // Récupérer l'utilisateur connecté
        $user = Auth::getUser();

        // Afficher la vue du profil
        $this->render('front/profile.twig', ['user' => $user]);
    }

    public function update()
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::getUser();
        $userId = $user->getId();
        $userModel = new User();
        $user = $userModel->getById($userId); // Récupérer l'utilisateur de la base de données.

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? $user->getUsername(); // Garder l'ancien si pas de nouveau nom.

            // Gestion de l'avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar = $_FILES['avatar'];
                $avatarName = $avatar['name'];
                $avatarTmpName = $avatar['tmp_name'];
                $avatarSize = $avatar['size'];
                $avatarError = $avatar['error'];

                $fileExtension = strtolower(pathinfo($avatarName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    if ($avatarSize < 1000000) { // Limite de 1MB
                        $newAvatarName = Profile::generateUniqueFileName($fileExtension);
                        $avatarDestination = 'assets/img/' . $newAvatarName; // Dossier de destination des avatars

                        move_uploaded_file($avatarTmpName, $avatarDestination);

                        $user->setAvatar('/' . $avatarDestination); // Enregistrer le chemin relatif dans l'objet user
                    } else {
                        Session::set('error', 'La taille de l\'image est trop grande.');
                        header('Location: /profile'); // Redirection avec message d'erreur
                        exit;
                    }
                } else {
                    Session::set('error', 'Seuls les fichiers JPG, JPEG et PNG sont autorisés.');
                    header('Location: /profile'); // Redirection avec message d'erreur
                    exit;
                }
            }

            $user->setUsername($username);


            // Mise à jour dans la base de données
            $stmt = $this->db->prepare("UPDATE users SET username = :username, avatar = :avatar WHERE id = :id");
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':avatar', $user->getAvatar());
            $stmt->bindValue(':id', $user->getId());

            if ($stmt->execute()) {
                 // Mettre à jour les informations de l'utilisateur dans la session
                Auth::setUser($user);
                Session::set('success', 'Profil mis à jour avec succès.');
            } else {
                Session::set('error', 'Erreur lors de la mise à jour du profil.');
            }

            header('Location: /profile'); // Redirection après la mise à jour
            exit;
        }

        // Si ce n'est pas une requête POST, rediriger vers la page de profil
        header('Location: /profile');
        exit;
    }
}