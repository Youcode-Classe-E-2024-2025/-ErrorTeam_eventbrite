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
// use BaconQrCode\Renderer\ImageRenderer;
// use BaconQrCode\Renderer\RendererStyle\RendererStyle;
// use BaconQrCode\Writer;
use chillerlan\QRCode\QRCode; // Correction ici : Importer la classe QRCode
use chillerlan\QRCode\QROptions; 

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
                echo "Événement non trouvé.";
                return;
            }

            if ($number_of_tickets > $event->getAvailableSeats()) {
                Session::set('error', 'Il n\'y a pas assez de places disponibles pour cet événement.');
                header('Location: /events/' . $event_id);
                exit();
            }

            $total_price = $number_of_tickets * $event->getPrice();

            Stripe::setApiKey('sk_test_51QrgWJFaMh57r4UFxaapRc4fD0gF4qFrDSwOS9JTt6SVX3dhbcKjWyx6yijEpI1CmeL0swiq2SjIMbsX39mYqpVo00DirGnSHb');

            try {
                $intent = PaymentIntent::create([
                    'amount' => $total_price * 100,  // Montant en centimes
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

                // $renderer = new ImageRenderer(
                //     new RendererStyle(400),
                // );
                // $writer = new Writer($renderer);
                // $qrCode = $writer->writeFile('Reservation ID : '.$reservation->getId().'Event Title : '.$event->getTitle());

                // $reservation->setQrCode($qrCode);

                $reservationModel = new Reservation();
                $options = new QROptions([
                    'version'    => 5,
                    'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                    'eccLevel'   => QRCode::ECC_L,
                ]);

                $qrcode = (new QRCode($options))->render('Reservation ID : '.$reservation->getId().'Event Title : '.$event->getTitle());

                $reservation->setQrCode($qrcode);

                $reservationModel = new Reservation();
                if ($reservationModel->create($reservation)) {
                    $event->setAvailableSeats($event->getAvailableSeats() - $number_of_tickets);
                    $eventModel->update($event);

                    Session::set('success', 'Réservation créée. Veuillez procéder au paiement.');
                    header('Location: /events/' . $event_id . '/reservations/payment?payment_intent=' . $paymentId);
                    exit();
                } else {
                    echo "Erreur lors de la création de la réservation.";
                }
            } catch (ApiErrorException $e) {
                error_log("Stripe error: " . $e->getMessage());
                Session::set('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');
                header('Location: /events/' . $event_id);
                exit();
            }
        }
    }

    public function paymentForm($event_id)
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
        $paymentIntent = $_GET['payment_intent'] ?? null;

        if (!$paymentIntent) {
            Session::set('error', 'Intent de paiement manquant.');
            header('Location: /events/' . $event_id);
            exit();
        }
        echo View::render('front/reservations/payment.twig', ['event' => $event,'paymentIntent' => $paymentIntent]);
    }

    public function confirmPayment($event_id)
    {
        Stripe::setApiKey('sk_test_51QrgWJFaMh57r4UFxaapRc4fD0gF4qFrDSwOS9JTt6SVX3dhbcKjWyx6yijEpI1CmeL0swiq2SjIMbsX39mYqpVo00DirGnSHb');
        $paymentIntent = $_POST['payment_intent'] ?? null;

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


        try {
            $intent = \Stripe\PaymentIntent::retrieve($paymentIntent);
            $intent->confirm();
        } catch (Error\InvalidRequest $e) {
            Session::set('error', 'Intent de paiement invalid.');
            header('Location: /events/' . $event_id);
            exit();
        }

        if ($intent->status == "succeeded"){
            Session::set('success', 'paiement effectuer');
            header('Location: /events/' . $event_id);
            exit();
        } else {
            Session::set('success', 'paiement a echouer');
            header('Location: /events/' . $event_id);
            exit();
        }
    }
}