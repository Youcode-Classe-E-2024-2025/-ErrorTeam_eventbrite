<?php

namespace App\Controllers\Front;

use App\core\Controller;
use App\models\User;
use App\models\UpdateProfile; // Import du nouveau modèle
use App\core\Auth;
use App\core\profile;
use App\core\Session;
use Ramsey\Uuid\Uuid;

class ProfileController extends Controller
{
    public function index()
    {
        if (!Auth::isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::getUser();
        $this->render('front/profile.twig', ['user' => $user]);
    }

    public function show()
    {
        if (!Auth::isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::getUser();
        $this->render('front/update.twig', ['user' => $user]);
    }

    public function update()
    {
        if (!Auth::isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::getUser();
        $userId = $_SESSION['user_id'];
        $userModel = new User();
        $user = $userModel->getById($userId);


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateProfileModel = new UpdateProfile();
            $avatarPath = null;

            // Gestion de l'avatar (avant la mise à jour du profil)
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar = $_FILES['avatar'];
                $fileExtension = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png'];

                if (in_array($fileExtension, $allowedExtensions) && $avatar['size'] < 1000000) {
                    $newAvatarName = Profile::generateUniqueFileName($fileExtension);
                    $avatarDestination = 'assets/img/' . $newAvatarName;

                    if (move_uploaded_file($avatar['tmp_name'], $avatarDestination)) {
                        $avatarPath = '/' . $avatarDestination;
                    } else {
                        Session::set('error', 'Erreur lors du déplacement de l\'avatar.');
                        header('Location: /profile');
                        exit;
                    }
                } else {
                    Session::set('error', 'Seuls les fichiers JPG, JPEG et PNG de moins de 1MB sont autorisés.');
                    header('Location: /profile');
                    exit;
                }
            }


            $data = $_POST; // Récupérer toutes les données POST
            $success = $updateProfileModel->updateProfile($user, $data, $avatarPath); // Passer l'avatarPath

            if ($success) {
                Auth::setUser($user); // Mettre à jour l'utilisateur authentifié
                Session::set('success', 'Profil mis à jour avec succès.');

            }

            header('Location: /profile');
            exit;
        }

        header('Location: /profile');
        exit;
    }
}