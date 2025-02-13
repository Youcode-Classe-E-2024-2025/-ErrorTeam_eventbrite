<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Models\User;
use App\Core\Auth;
use App\Core\Profile as ProfileHelper; // To avoid naming conflict with this class
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
        $this->render('front/edit_profile.twig', ['user' => $user, 'session' => $_SESSION]); // Pass the session
    }

    public function update()
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::getUser(); // Get the logged-in user object
        $userId = $user->getId();
        $userModel = new User();
        $user = $userModel->getById($userId); // Récupérer l'utilisateur de la base de données pour avoir les infos à jour

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Validation (Important!)
            if (!isset($_POST['_token']) || !hash_equals($_SESSION['csrf_token']['edit_profile'], $_POST['_token'])) {
                Session::set('error', 'CSRF token invalid.');
                header('Location: /profile');
                exit;
            }

            $firstName = $_POST['first_name'] ?? $user->getFirstName();
            $lastName = $_POST['last_name'] ?? $user->getLastName();
            $phoneNumber = $_POST['telephone'] ?? $user->getPhoneNumber();
            $username = $_POST['username'] ?? $user->getUsername();

            // Avatar upload handling
            $avatarPath = $user->getAvatar(); // Keep the existing avatar path by default

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar = $_FILES['avatar'];

                $fileExtension = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    if ($avatar['size'] < 1000000) { // 1MB limit
                        $newAvatarName = ProfileHelper::generateUniqueFileName($fileExtension); // Use the helper class
                        $avatarDestination = 'assets/img/' . $newAvatarName;

                        if (move_uploaded_file($avatar['tmp_name'], $avatarDestination)) {
                            $avatarPath = '/' . $avatarDestination; // Update the avatar path

                            // Delete the old avatar if it exists and is not a default avatar.
                            if ($user->getAvatar() && strpos($user->getAvatar(), 'default_avatar.') === false) {
                                if (file_exists(ltrim($user->getAvatar(), '/'))) { //Remove the leading slash to be a valid path
                                    unlink(ltrim($user->getAvatar(), '/'));
                                }
                            }
                        } else {
                            Session::set('error', 'Failed to move uploaded file.');
                            header('Location: /profile');
                            exit;
                        }
                    } else {
                        Session::set('error', 'Image size is too large (max 1MB).');
                        header('Location: /profile');
                        exit;
                    }
                } else {
                    Session::set('error', 'Only JPG, JPEG, and PNG files are allowed.');
                    header('Location: /profile');
                    exit;
                }
            }

            // Update user object
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPhoneNumber($phoneNumber);
            $user->setUsername($username);
            $user->setAvatar($avatarPath);  // Set the potentially new avatar path

            // Database update (Use prepared statements to prevent SQL injection)
            $stmt = $this->db->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, phone_number = :phone_number, username = :username, avatar = :avatar WHERE id = :id");
            $stmt->bindValue(':first_name', $user->getFirstName());
            $stmt->bindValue(':last_name', $user->getLastName());
            $stmt->bindValue(':phone_number', $user->getPhoneNumber());
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':avatar', $user->getAvatar());
            $stmt->bindValue(':id', $user->getId());


            if ($stmt->execute()) {
                // Update session user
                Auth::setUser($user);  // Update the session with the modified User object
                Session::set('success', 'Profile updated successfully.');
            } else {
                Session::set('error', 'Failed to update profile in the database.');
            }

            header('Location: /profile'); // Redirect after the update
            exit;
        }

        // If it's not a POST request, redirect to the profile page
        header('Location: /profile');
        exit;
    }
}