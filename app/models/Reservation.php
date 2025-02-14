<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Ramsey\Uuid\Uuid;

class Reservation
{
    private $id;
    private $user_id;
    private $event_id;
    private $reservation_date;
    private $number_of_tickets;
    private $total_price;
    private $status;
    private $qr_code;
    private $payment_id;
    private $created_at;
    private $updated_at;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Getters et Setters pour chaque propriété (id, user_id, event_id, etc.)

    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function getUserId() { return $this->user_id; }

    public function setEventId($event_id) { $this->event_id = $event_id; }
    public function getEventId() { return $this->event_id; }

    public function setReservationDate($reservation_date) { $this->reservation_date = $reservation_date; }
    public function getReservationDate() { return $this->reservation_date; }

    public function setNumberOfTickets($number_of_tickets) { $this->number_of_tickets = $number_of_tickets; }
    public function getNumberOfTickets() { return $this->number_of_tickets; }

    public function setTotalPrice($total_price) { $this->total_price = $total_price; }
    public function getTotalPrice() { return $this->total_price; }

    public function setStatus($status) { $this->status = $status; }
    public function getStatus() { return $this->status; }

    public function setQrCode($qr_code) { $this->qr_code = $qr_code; }
    public function getQrCode() { return $this->qr_code; }

    public function setPaymentId($payment_id) { $this->payment_id = $payment_id; }
    public function getPaymentId() { return $this->payment_id; }

    // Méthodes CRUD (Create, Read, Update, Delete)

    public function create(Reservation $reservation) {
        try {
            $uuid = Uuid::uuid4()->toString();
            $stmt = $this->db->prepare("
                INSERT INTO reservations (id, user_id, event_id, number_of_tickets, total_price, status, qr_code, payment_id)
                VALUES (:id, :user_id, :event_id, :number_of_tickets, :total_price, :status, :qr_code, :payment_id)
            ");
            $stmt->bindValue(':id', $uuid);
            $stmt->bindValue(':user_id', $reservation->getUserId());
            $stmt->bindValue(':event_id', $reservation->getEventId());
            $stmt->bindValue(':number_of_tickets', $reservation->getNumberOfTickets());
            $stmt->bindValue(':total_price', $reservation->getTotalPrice());
            $stmt->bindValue(':status', $reservation->getStatus());
            $stmt->bindValue(':qr_code', $reservation->getQrCode());
            $stmt->bindValue(':payment_id', $reservation->getPaymentId());

            $result = $stmt->execute();

            if ($result) {
                $reservation->setId($uuid);
                return true;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            error_log("Error creating reservation: " . $e->getMessage());
            return false;
        }
    }

    // Récupère la réservation par ID
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchObject(__CLASS__);
        } catch (PDOException $e) {
            error_log("Error fetching reservation by ID: " . $e->getMessage());
            return false;
        }
    }

    // Récupère toutes les réservations par Event ID
    public function getAllByEventId($eventId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM reservations WHERE event_id = :event_id");
            $stmt->bindValue(':event_id', $eventId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (PDOException $e) {
            error_log("Error getting reservations by event ID: " . $e->getMessage());
            return false;
        }
    }

    // Méthodes d'agrégation pour obtenir des statistiques

    public function getTotalReservations()
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error fetching total reservations: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalParticipants()
    {
        try {
            $stmt = $this->db->prepare("SELECT SUM(number_of_tickets) FROM reservations");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error fetching total participants: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalRevenue()
    {
        try {
            $stmt = $this->db->prepare("SELECT SUM(total_price) FROM reservations");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error fetching total revenue: " . $e->getMessage());
            return 0;
        }
    }

    // Récupère les dernières réservations
    public function getLatestReservations($limit)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM reservations ORDER BY reservation_date DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (PDOException $e) {
            error_log("Error fetching latest reservations: " . $e->getMessage());
            return false;
        }
    }
}
