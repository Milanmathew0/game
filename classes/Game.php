<?php
class Game {
    private $conn;
    private $table_name = "games";
    
    // Game properties
    public $id;
    public $title;
    public $description;
    public $price;
    public $image;
    public $category;
    public $created;
    
    // Get total count of games
    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Get recent games
    public function getRecentGames($limit = 5) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT " . $limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Read all games
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY title";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Read one game
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->image = $row['image'];
            $this->category = $row['category'];
            return true;
        }
        
        return false;
    }
    
    // Create game
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title = :title, 
                      description = :description, 
                      price = :price, 
                      image = :image, 
                      category = :category, 
                      created_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->category = htmlspecialchars(strip_tags($this->category));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":category", $this->category);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update game
    public function update() {
        // If no image is uploaded, keep the existing one
        if(empty($this->image)) {
            $query = "UPDATE " . $this->table_name . " 
                      SET title = :title, 
                          description = :description, 
                          price = :price, 
                          category = :category 
                      WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET title = :title, 
                          description = :description, 
                          price = :price, 
                          image = :image, 
                          category = :category 
                      WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->category = htmlspecialchars(strip_tags($this->category));
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category", $this->category);
        
        // Bind image if it's set
        if(!empty($this->image)) {
            $this->image = htmlspecialchars(strip_tags($this->image));
            $stmt->bindParam(":image", $this->image);
        }
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete game
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind value
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all games
    public function getAllGames() {
        // Query to get all games
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get featured games
    public function getFeaturedGames() {
        // Query to get featured games
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 6";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get game by ID
    public function getGameById($id) {
        // Query to get game by ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get games by category
    public function getGamesByCategory($category) {
        // Query to get games by category
        $query = "SELECT * FROM " . $this->table_name . " WHERE category = ? ORDER BY created DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $category);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Search games
    public function searchGames($keyword) {
        // Query to search games
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE title LIKE ? OR description LIKE ? 
                  ORDER BY created DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize keyword
        $keyword = htmlspecialchars(strip_tags($keyword));
        $keyword = "%{$keyword}%";
        
        // Bind values
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>