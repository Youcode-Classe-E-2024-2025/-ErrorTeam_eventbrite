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
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Ramsey\Uuid\Uuid;
use Dompdf\Options;
use App\Services\EmailService;

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

        if ($event->getAvailableSeats() <= 0) {
            Session::set('error', 'Cet événement est complet. Aucune réservation n\'est possible.');
            header('Location: /events/' . $event_id);
            exit;
        }

        $dateStart = new DateTime($event->getDateStart());
        $now = new DateTime(); 

        if ($dateStart < $now) {
            Session::set('error', 'Cet événement a déjà eu lieu.');
            header('Location: /events/' . $event_id);
            exit();
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
            Session::set('total_price', $total_price); 

            $total_price = $number_of_tickets * $event->getPrice();

            Stripe::setApiKey('sk_test_51QrgWJFaMh57r4UFxaapRc4fD0gF4qFrDSwOS9JTt6SVX3dhbcKjWyx6yijEpI1CmeL0swiq2SjIMbsX39mYqpVo00DirGnSHb');

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
                $clientSecret = $intent->client_secret; 

                 // Stocker les informations de la réservation dans la session
                 Session::set('number_of_tickets', $number_of_tickets);
                 Session::set('total_price', $total_price);
                 Session::set('event_id', $event_id);

                echo View::render('front/reservations/payment.twig', [
                    'event' => $event,
                    'paymentIntent' => $paymentId,
                    'clientSecret' => $clientSecret,
                    'totalPrice' => $total_price
                ]);
                exit();
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
        try {
            Stripe::setApiKey('sk_test_51QrgWJFaMh57r4UFxaapRc4fD0gF4qFrDSwOS9JTt6SVX3dhbcKjWyx6yijEpI1CmeL0swiq2SjIMbsX39mYqpVo00DirGnSHb');
            $intent = \Stripe\PaymentIntent::retrieve($paymentIntent);
            $paymentIntentStatus = $intent->status;
        } catch (Error\InvalidRequest $e) {
            Session::set('error', 'Intent de paiement invalid.');
            header('Location: /events/' . $event_id);
            exit();
        }
        echo View::render('front/reservations/payment.twig', ['event' => $event,'paymentIntent' => $paymentIntent,'paymentIntentStatus' => $paymentIntentStatus]);
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

    $startDate = $event->getDateStart();
    $endDate = $event->getDateEnd();

    $paymentProcessed = Session::get('payment_processed_' . $paymentIntent, false);

    if ($paymentProcessed) {
        Session::set('success', 'Ce paiement a déjà été traité.');
        header('Location: /events/' . $event_id);
        exit();
    }

    try {
        $intent = \Stripe\PaymentIntent::retrieve($paymentIntent);

        if ($intent->status == "succeeded") {
            Session::set('success', 'Paiement effectué.');

            $number_of_tickets = Session::get('number_of_tickets');
            $total_price = Session::get('total_price');
            $event_id = Session::get('event_id');

            $reservationModel = new Reservation();
            $reservation = new Reservation();
            $uuid = Uuid::uuid4()->toString();
            $reservation->setId($uuid);

            // Générer un QR code pour la réservation
            $qrCodeData = "Réservation ID: " . $uuid . " | Événement: " . $event->getTitle() . " | Utilisateur: " . $_SESSION['user_id'];
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L,
            ]);
            $qrCode = (new QRCode($options))->render($qrCodeData);

            // Enregistrer le QR code dans la réservation
            $reservation->setQrCode($qrCode);

            $reservation->setTotalPrice($total_price);
            $reservation->setNumberOfTickets($number_of_tickets);
            $reservation->setEventId($event_id);
            $reservation->setUserId($_SESSION['user_id']);
            $reservation->setStatus('confirmed');
            $reservation->setPaymentId($paymentIntent);

            if (!$reservationModel->create($reservation)) {
                echo "Erreur lors de la création de la réservation.";
                exit;
            }

            $username = Session::get('username');
            $to = $user->getEmail();
            $subject = "Confirmation de votre réservation pour" . $event->getTitle() . "!";
            $body = "Bonjour $username,<br><br>Votre réservation pour " . $event->getTitle() . " a été confirmée! Veuillez trouver votre billet en pièce jointe.<br><br>Cordialement,<br>L'équipe MonSiteEvenements";
            if (EmailService::sendEmailNotification($to, $subject, $body)) {
                Session::set('success', 'Réservation confirmée et email de confirmation envoyé!');
                exit;
            } else {
                Session::set('success', 'Réservation confirmée, mais l\'envoi de l\'email a échoué!');
                exit;
            }
            
            $nouveauNombreDePlacesDisponibles = $event->getAvailableSeats() - $number_of_tickets;
            if ($nouveauNombreDePlacesDisponibles < 0) {
                $nouveauNombreDePlacesDisponibles = 0;
            }

            $event->setAvailableSeats($nouveauNombreDePlacesDisponibles);
            $event->setDateStart($startDate);
            $event->setDateEnd($endDate);
            $eventModel->updateAvailableSeats($event_id, $nouveauNombreDePlacesDisponibles);
            ob_start();

            try {
                $billetsHTML = '';
                $options = new Options();
                $options->set('isRemoteEnabled', true);

                for ($i = 0; $i < $reservation->getNumberOfTickets(); $i++) {
                    $billetsHTML .= View::render('front/reservations/billet.twig', [
                        'event' => $event,
                        'reservation' => $reservation,
                        'ticket_number' => $i + 1,
                    ]);
                }

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($billetsHTML);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="billets_evenement_' . $event->getId() . '.pdf"');
                $dompdf->stream("billets_evenement_" . $event->getId() . ".pdf", ["Attachment" => 0]);
                exit;

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
                $error = ob_get_clean();
                error_log("Erreur PDF: " . $e->getMessage());
                echo htmlentities($error);
                exit;
            }

        } else {
            Session::set('success', 'Paiement échoué.');
            header('Location: /events/' . $event_id);
            exit();
        }

    } catch (Error\InvalidRequest $e) {
        Session::set('error', 'Intent de paiement invalide.');
        header('Location: /events/' . $event_id);
        exit();
    }
}
}
?>