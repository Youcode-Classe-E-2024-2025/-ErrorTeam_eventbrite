<?php

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Models\User;
use App\Models\Event;

class ProfileController extends BaseController
{
    public function showProfile()
    {
        // Récupérer l'utilisateur connecté
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /login');
            exit();
        }

        // Récupérer les informations de l'utilisateur
        $user = User::find($userId);

        // Récupérer les événements créés par l'utilisateur
        $createdEvents = Event::where('creator_id', $userId)->get();

        // Récupérer les événements auxquels l'utilisateur a participé
        $participatedEvents = Event::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        // Afficher la vue du profil
        $this->render('front/profile/show', [
            'user' => $user,
            'createdEvents' => $createdEvents,
            'participatedEvents' => $participatedEvents,
        ]);
    }
}