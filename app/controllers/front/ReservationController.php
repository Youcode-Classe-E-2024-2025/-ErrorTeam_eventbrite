<?php

namespace App\Controllers\Front;

use App\Core\Controller;
use App\Core\View;
use App\Models\Reservation;
use App\Models\Event;  
use App\Core\Session;
use Stripe\Stripe; 
use Stripe\PaymentIntent; 
use Stripe\Exception\ApiErrorException;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ReservationController extends Controller
{
    public function createForm($event_id) 
    {
        if (!isset($_SESSION['user_id'])) {
            Session::set('error', 'Vous devez être connecté pour réserver un événement.');
            header('Location: /login');
            exit();
        }

        $eventModel = new Event();
        $event = $eventModel->getById($event_id);

        if (!$event) {
            echo "Événement non trouvé.";
            return;
        }

        echo View::render('front/reservations/create.twig', ['event' => $event]);
    }

    public function create($event_id) 
    {
        if (!isset($_SESSION['user_id'])) {
            Session::set('error', 'Vous devez être connecté pour réserver un événement.');
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $number_of_tickets = $_POST['number_of_tickets'];

            $eventModel = new Event();
            $event = $eventModel->getById($event_id);

            if (!$event) {
                echo "event non trouvé.";
                return;
            }

            if ($number_of_tickets > $event->getAvailableSeats()) {
                Session::set('error', 'Il n\'y a pas assez de places disponibles pour cet event.');
                header('Location: /events/' . $event_id);
                exit();
            }

            $total_price = $number_of_tickets * $event->getPrice();

            Stripe::setApiKey('sk_test_YOUR_STRIPE_SECRET_KEY');

            try {
                $intent = PaymentIntent::create([
                    'amount' => $total_price * 100,  
                    'currency' => 'eur',
                    'metadata' => [
                        'event_id' => $event_id,
                        'user_id' => $_SESSION['user_id'],
                        'number_of_tickets' => $number_of_tickets,
                    ],
                ]);

                $paymentId = $intent->id;
                $reservation = new Reservation();
                $reservation->setUserId($_SESSION['user_id']);
                $reservation->setEventId($event_id);
                $reservation->setNumberOfTickets($number_of_tickets);
                $reservation->setTotalPrice($total_price);
                $reservation->setStatus('pending');  
                $reservation->setPaymentId($paymentId);

                $renderer = new ImageRenderer(
                    new RendererStyle(400),
                );
                $writer = new Writer($renderer);
                $qrCode = $writer->writeFile('Reservation ID : '.$reservation->getId().'Event Title : '.$event->getTitle());

                $reservation->setQrCode($qrCode);

                $reservationModel = new Reservation();
                if ($reservationModel->create($reservation)) {
                    $event->setAvailableSeats($event->getAvailableSeats() - $number_of_tickets);
                    $eventModel->update($event);
                    Session::set('success', 'Réservation créée. Veuillez procéder au paiement.');
                    header('Location: /events/' . $event_id . '/reservations/payment?payment_intent=' . $paymentId); // Rediriger vers une nouvelle route pour le paiement
                    exit();
                } else {
                    echo "Erreur lors de la création de la réservation.";
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                error_log("Stripe error: " . $e->getMessage());
                Session::set('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');
                header('Location: /events/' . $event_id);
                exit();
            }
        }
    }
}