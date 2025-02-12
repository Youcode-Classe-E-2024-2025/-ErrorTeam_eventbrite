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
    private $event_date;
    private $location;
    private $price;
    private $capacity;
    private $available_seats;
    private $image_url;
    private $is_published;
    private $created_at;
    private $updated_at;

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

    public function setEventDate($event_date) { $this->event_date = $event_date; }
    public function getEventDate() { return $this->event_date; }

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
}