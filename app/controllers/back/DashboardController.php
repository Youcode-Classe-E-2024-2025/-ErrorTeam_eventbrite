<?php

namespace App\Controllers\Back;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Event;

class DashboardController extends Controller
{
    public function index()
    {
        // Vérification des permissions d'accès
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            echo View::render('front/home.twig');
            return;
        }
    
        $userModel = new User();
        $reservationModel = new Reservation();
        $eventModel = new Event(); // Instanciation du modèle Event
    
        // Récupération des demandes d'organisateur
        $organizerRequests = $userModel->getOrganizerRequests();
    
        // Récupération des statistiques des réservations
        $totalReservations = $reservationModel->getTotalReservations();
        $totalParticipants = $reservationModel->getTotalParticipants();
        $totalRevenue = $reservationModel->getTotalRevenue();
        $latestReservations = $reservationModel->getLatestReservations(5);
    
        // Récupération du nombre total d'événements
        $totalEvents = $eventModel->getTotalEvents();
    
        // Affichage du tableau de bord avec les statistiques
        echo View::render('back/dashboard.twig', [
            'organizerRequests' => $organizerRequests,
            'totalReservations' => $totalReservations,
            'totalParticipants' => $totalParticipants,
            'totalRevenue' => $totalRevenue,
            'latestReservations' => $latestReservations,
            'totalEvents' => $totalEvents // Ajout du total des événements
        ]);
    }    

    public function confirmOrganizer($id)
{
    // Vérification de l'accès (admin)
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        echo View::render('front/home.twig');
        return;
    }

    $userModel = new User();

    // Récupération de l'utilisateur en fonction de l'ID
    $user = $userModel->getById($id);

    if ($user) {
        // Mise à jour du rôle de l'utilisateur en tant qu'organisateur
        $user->setRole('organizer');
        if ($userModel->update($user)) {
            // Rediriger ou afficher un message de succès
            $_SESSION['success_message'] = 'L\'utilisateur a été confirmé en tant qu\'organisateur.';
            header('Location: /admin/dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = 'Une erreur est survenue lors de la confirmation.';
            header('Location: /admin/dashboard');
            exit;
        }
    } else {
        // Si l'utilisateur n'est pas trouvé
        $_SESSION['error_message'] = 'Utilisateur non trouvé.';
        header('Location: /admin/dashboard');
        exit;
    }
}


}
