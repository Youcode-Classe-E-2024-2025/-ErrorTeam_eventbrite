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

}