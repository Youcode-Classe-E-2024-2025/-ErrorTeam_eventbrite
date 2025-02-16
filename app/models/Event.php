<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Ramsey\Uuid\Uuid;
use DateTime;

class Event
{
    private $id;
    private $organizer_id;
    private $category_id;
    private $title;
    private $description;
    private $dateStart;
    private $dateEnd;
    private $location;
    private $price;
    private $capacity;
    private $available_seats;
    private $image_url;
    private $is_published;
    private $created_at;
    private $updated_at;
    private $status;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    public function setOrganizerId($organizer_id) { $this->organizer_id = $organizer_id; }
    public function getOrganizerId() { return $this->organizer_id; }
  
    public function setCategoryId($category_id) { $this->category_id = $category_id; }
    public function getCategoryId() { return $this->category_id; }

    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }

    public function setDescription($description) { $this->description = $description; }
    public function getDescription() { return $this->description; }

    public function setDateStart($dateStart) {
        // Si $dateStart est une chaîne de caractères, convertissez-la en objet DateTime
        if (is_string($dateStart)) {
            $this->dateStart = new DateTime($dateStart);
        } elseif ($dateStart instanceof DateTime) {
            $this->dateStart = $dateStart;
        } else {
            throw new InvalidArgumentException("Invalid date format");
        }
    }

    public function getDateStart() {
        $stmt = $this->db->prepare("SELECT date_start FROM events WHERE id = :id");
        $stmt->bindValue(':id', $this->id, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function setDateEnd($date_end) { $this->dateEnd = $date_end; }
    public function getDateEnd() { return $this->dateEnd; }

    public function setLocation($location) { $this->location = $location; }
    public function getLocation() { return $this->location; }

     public function setPrice($price) { $this->price = $price; }
    public function getPrice() { return $this->price; }

    public function setCapacity($capacity) { $this->capacity = $capacity; }
    public function getCapacity() { return $this->capacity; }
  
    public function setAvailableSeats($available_seats) { $this->available_seats = $available_seats; }
    public function getAvailableSeats() { return $this->available_seats; }

    public function setImageUrl($image_url) { $this->image_url = $image_url; }
    public function getImageUrl() { return $this->image_url; }

    public function setIsPublished($is_published) { $this->is_published = $is_published; }
    public function getIsPublished() { return $this->is_published; }
    
    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                id, 
                organizer_id, 
                category_id, 
                title, 
                description, 
                date_start AS dateStart, 
                date_end AS dateEnd, 
                location, 
                price, 
                capacity, 
                available_seats, 
                image_url, 
                is_published,
                created_at,
                updated_at
            FROM events 
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
        return $stmt->fetch();
    }

    public function getAll(int $limit, int $offset)
    {
         try {
            $stmt = $this->db->prepare("SELECT * FROM events LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (\PDOException $e) {
            error_log("Error getting all events with pagination: " . $e->getMessage());
            return false;
        }
    }

    public function getByOrganiser($id){
        $stmt = $this->db->prepare("SELECT * FROM events WHERE organizer_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $evs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $events = []; // To hold all event objects
        
        foreach($evs as $ev) {
            $event = new self();
            $event->setId($ev['id']);
            $event->setOrganizerId($ev['organizer_id']);
            $event->setCategoryId($ev['category_id']);
            $event->setTitle($ev['title']);
            $event->setDescription($ev['description']);
            $event->setDateStart($ev['date_start']); 
            $event->setDateEnd($ev['date_end']); 
            $event->setLocation($ev['location']);
            $event->setPrice($ev['price']);
            $event->setCapacity($ev['capacity']);
            $event->setStatus($ev['status']);
            $event->setIsPublished($ev['status'] === 'published' ? true : false);
            $events[] = $event;
        }
        return $events;
        
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: /myevents');
    }

    public function search(string $query, int $limit, int $offset)
    {
        try {
                $stmt = $this->db->prepare("SELECT * FROM events WHERE title LIKE :query OR description LIKE :query LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':query', '%' . $query . '%');
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (\PDOException $e) {
            error_log("Error during events search with pagination: " . $e->getMessage());
            return false;
        }
    }

    public function getByCategory(string $id, int $limit, int $offset)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE category_id = :id LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (\PDOException $e) {
            error_log("Error during events search with pagination: " . $e->getMessage());
            return false;
        }
    }

    public function update(Event $event)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE events
                SET 
                    organizer_id = :organizer_id,
                    category_id = :category_id,
                    title = :title,
                    description = :description,
                    date_start = :date_start,
                    date_end = :date_end,
                    location = :location,
                    price = :price,
                    capacity = :capacity,
                    available_seats = :available_seats,
                    image_url = :image_url,
                    is_published = :is_published,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->bindValue(':organizer_id', $event->getOrganizerId(), PDO::PARAM_STR);
            $stmt->bindValue(':category_id', $event->getCategoryId(), PDO::PARAM_STR);
            $stmt->bindValue(':title', $event->getTitle(), PDO::PARAM_STR);
            $stmt->bindValue(':description', $event->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':date_start', $event->getDateStart(), PDO::PARAM_STR);
            $stmt->bindValue(':date_end', $event->getDateEnd() ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':location', $event->getLocation(), PDO::PARAM_STR);
            $stmt->bindValue(':price', $event->getPrice(), PDO::PARAM_STR);
            $stmt->bindValue(':capacity', $event->getCapacity(), PDO::PARAM_INT);
            $stmt->bindValue(':available_seats', $event->getAvailableSeats(), PDO::PARAM_INT);
            $stmt->bindValue(':image_url', $event->getImageUrl(), PDO::PARAM_STR);
            $stmt->bindValue(':is_published', $event->getIsPublished(), PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $event->getId(), PDO::PARAM_STR);

            $result = $stmt->execute();

            if ($result === false) {
                $errorInfo = $stmt->errorInfo();
                error_log("Erreur SQL lors de la mise à jour de l'événement : " . json_encode($errorInfo));
                return false;
            }

            return true;

        } catch (\PDOException $e) {
            error_log("Erreur PDO lors de la mise à jour de l'événement : " . $e->getMessage());
            return false;
        }
    }

    public function getTotalEvents(): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM events");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting total events count: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalParticipants() 
    {
        $stmt = $this->db->prepare("SELECT SUM(capacity) AS total FROM events");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalRevenue()
    {
        $stmt = $this->db->prepare("SELECT SUM(price * capacity) AS total FROM events WHERE price IS NOT NULL");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getLatestEvents($limit = 3)
    {
        $stmt = $this->db->prepare("SELECT title, dateStart, status FROM events ORDER BY dateStart DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAvailableSeats(string $eventId, int $newAvailableSeats): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE events
                SET available_seats = :available_seats
                WHERE id = :id
            ");

            $stmt->bindValue(':available_seats', $newAvailableSeats, PDO::PARAM_INT);
            $stmt->bindValue(':id', $eventId, PDO::PARAM_STR);

            $result = $stmt->execute();

            if ($result === false) {
                $errorInfo = $stmt->errorInfo();
                error_log("SQLSTATE: " . $errorInfo[0] . ", Code: " . $errorInfo[1] . ", Message: " . $errorInfo[2]);
                return false;
            }

            return true;

        } catch (PDOException $e) {
            error_log("Error updating available seats in Event model: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTotalSearchResults(string $query): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM events WHERE title LIKE :query OR description LIKE :query");
            $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting total search results: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalEventsByCategory(string $category_id): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM events WHERE category_id = :category_id");
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_STR);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting total category events: " . $e->getMessage());
            return 0;
        }
    }
}