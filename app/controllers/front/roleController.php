<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\Session;
use App\Models\UpdateRole;

class RoleController extends Controller
{
    /**
     * Affiche le formulaire de changement de rôle.
     */
    public function index()
    {
        if (Session::get('role') !== 'admin') {
            $this->redirect('/login');
        }

        $updateRoleModel = new UpdateRole();
        $users = $updateRoleModel->getAllUsers(); // À implémenter si nécessaire

        $this->render('front/role.twig', ['users' => $users]);
    }

    /**
     * Met à jour le rôle d'un utilisateur.
     */
    public function updateRole(string $userId)
    {
        if (Session::get('role') !== 'admin') {
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newRole = $_POST['role'] ?? null;

            if (!in_array($newRole, ['participant', 'organisateur'])) {
                Session::set('error', 'Rôle invalide.');
                $this->redirect('/role');
            }

            $updateRoleModel = new UpdateRole();
            $success = $updateRoleModel->updateUserRole($userId, $newRole);

            if ($success) {
                Session::set('success', 'Rôle mis à jour avec succès.');
            } else {
                Session::set('error', 'Erreur lors de la mise à jour du rôle.');
            }

            $this->redirect('/role');
        }
    }
}