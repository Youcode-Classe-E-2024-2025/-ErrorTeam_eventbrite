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
        $userId = $user->getId();
        $userModel = new User();
        $user = $userModel->getById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? $user->getUsername();
            $firstName = $_POST['first_name'] ?? $user->getFirstName();
            $lastName = $_POST['last_name'] ?? $user->getLastName();
            $phoneNumber = $_POST['phone_number'] ?? $user->getPhoneNumber();
            $bio = $_POST['bio'] ?? $user->getBio();

            if (strlen($username) > 255 || strlen($firstName) > 255 || strlen($lastName) > 255 || strlen($phoneNumber) > 20) {
                Session::set('error', 'Certains champs dépassent la longueur autorisée.');
                header('Location: /profile');
                exit;
            }

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar = $_FILES['avatar'];
                $fileExtension = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png'];

                if (in_array($fileExtension, $allowedExtensions) && $avatar['size'] < 1000000) {
                    $newAvatarName = Profile::generateUniqueFileName($fileExtension);
                    $avatarDestination = 'assets/img/' . $newAvatarName;
                    if (move_uploaded_file($avatar['tmp_name'], $avatarDestination)) {
                        $user->setAvatar('/' . $avatarDestination);
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

            $user->setUsername($username);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPhoneNumber($phoneNumber);
            $user->setBio($bio);

            $stmt = $this->db->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, phone_number = :phone_number, bio = :bio, avatar = :avatar WHERE id = :id");
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':first_name', $user->getFirstName());
            $stmt->bindValue(':last_name', $user->getLastName());
            $stmt->bindValue(':phone_number', $user->getPhoneNumber());
            $stmt->bindValue(':bio', $user->getBio());
            $stmt->bindValue(':avatar', $user->getAvatar());
            $stmt->bindValue(':id', $user->getId());

            if ($stmt->execute()) {
                Auth::setUser($user);
                Session::set('success', 'Profil mis à jour avec succès.');
                $_SESSION['first_name'] = $user->getFirstName();
                $_SESSION['last_name'] = $user->getLastName();
                $_SESSION['phone_number'] = $user->getPhoneNumber();
            } else {
                Session::set('error', 'Erreur lors de la mise à jour du profil.');
            }

            header('Location: /profile');
            exit;
        }

        header('Location: /profile');
        exit;
    }
}
