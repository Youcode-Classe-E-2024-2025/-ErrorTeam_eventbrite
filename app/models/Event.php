<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Ramsey\Uuid\Uuid;

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

    public function setDateStart($dateStart) { $this->dateStart = $dateStart; }
    public function getDateStart() { return $this->dateStart; }
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

        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchObject(__CLASS__);

    }

    public function getAll()
    {

        $stmt = $this->db->prepare("SELECT * FROM events");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);

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
            $event->setDateStart($ev['start_date']); 
            $event->setDateEnd($ev['end_date']); 
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

    public function search($query)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE title LIKE :query OR description LIKE :query");
            $stmt->bindValue(':query', '%' . $query . '%');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (\PDOException $e) {
            error_log("Error searching events: " . $e->getMessage());
            return false;
        }
    }

    public function getByCategory($category_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE category_id = :category_id");
            $stmt->bindValue(':category_id', $category_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        } catch (\PDOException $e) {
            error_log("Error getting events by category: " . $e->getMessage());
            return false;
        }
    }
    public function update(Event $event)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE events
                SET organizer_id = :organizer_id,
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

            $stmt->bindValue(':organizer_id', $event->getOrganizerId());
            $stmt->bindValue(':category_id', $event->getCategoryId());
            $stmt->bindValue(':title', $event->getTitle());
            $stmt->bindValue(':description', $event->getDescription());
            $stmt->bindValue(':date_start', $event->getDateStart());
            $stmt->bindValue(':date_end', $event->getDateEnd());
            $stmt->bindValue(':location', $event->getLocation());
            $stmt->bindValue(':price', $event->getPrice());
            $stmt->bindValue(':capacity', $event->getCapacity());
            $stmt->bindValue(':available_seats', $event->getAvailableSeats());
            $stmt->bindValue(':image_url', $event->getImageUrl());
            $stmt->bindValue(':is_published', $event->getIsPublished());
            $stmt->bindValue(':id', $event->getId());

            return $stmt->execute();

        } catch (\PDOException $e) {
            error_log("Error updating event: " . $e->getMessage());
            return false;
        }
    }

    // Obtenir le nombre total d'événements
public function getTotalEvents() {
    $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM events");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Obtenir le nombre total de participants
public function getTotalParticipants() {
    $stmt = $this->db->prepare("SELECT SUM(capacity) AS total FROM events");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Obtenir les revenus totaux des événements
public function getTotalRevenue() {
    $stmt = $this->db->prepare("SELECT SUM(price * capacity) AS total FROM events WHERE price IS NOT NULL");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Récupérer les derniers événements
public function getLatestEvents($limit = 3) {
    $stmt = $this->db->prepare("SELECT title, dateStart, status FROM events ORDER BY dateStart DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}