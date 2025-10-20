<?php
class Game {
    private $conn;
    private $table_name = "games";
    
    // Game properties
    public $id;
    public $title;
    public $description;
    public $price;
    public $category_id;
    public $image;
    public $is_featured;
    public $created_at;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all games with category names
    public function getAllGames() {
        // Query to get all games with category names
        $query = "SELECT g.*, c.name as category_name 
                  FROM " . $this->table_name . " g 
                  LEFT JOIN categories c ON g.category_id = c.id 
                  ORDER BY g.created_at DESC";
        
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
    
    // Get total number of games
    public function getTotalGames() {
        // Query to get total games
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Create new game
    public function createGame($data) {
        // Query to insert new game
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title = :title, 
                      description = :description, 
                      price = :price,
                      image = :image,
                      category_id = :category_id,
                      is_featured = :is_featured,
                      created_at = NOW()";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $title = htmlspecialchars(strip_tags($data['title']));
        $description = htmlspecialchars(strip_tags($data['description']));
        $price = htmlspecialchars(strip_tags($data['price']));
        $image = isset($data['image']) ? htmlspecialchars(strip_tags($data['image'])) : null;
        $category_id = htmlspecialchars(strip_tags($data['category_id']));
        $is_featured = isset($data['is_featured']) ? 1 : 0;
        
        // Bind values
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":image", $image);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":is_featured", $is_featured);
        
        // Execute query
        try {
            if($stmt->execute()) {
                return true;
            }
        } catch(PDOException $e) {
            // Log error or handle it appropriately
            error_log("Failed to create game: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // Update game
    public function updateGame($data) {
        // Build the query dynamically based on what data is provided
        $fields = [];
        $values = [];
        
        if(isset($data['title'])) {
            $fields[] = "title = :title";
            $values[':title'] = htmlspecialchars(strip_tags($data['title']));
        }
        
        if(isset($data['description'])) {
            $fields[] = "description = :description";
            $values[':description'] = htmlspecialchars(strip_tags($data['description']));
        }
        
        if(isset($data['price'])) {
            $fields[] = "price = :price";
            $values[':price'] = htmlspecialchars(strip_tags($data['price']));
        }
        
        if(isset($data['image'])) {
            $fields[] = "image = :image";
            $values[':image'] = htmlspecialchars(strip_tags($data['image']));
        }
        
        if(isset($data['category_id'])) {
            $fields[] = "category_id = :category_id";
            $values[':category_id'] = htmlspecialchars(strip_tags($data['category_id']));
        }
        
        $fields[] = "is_featured = :is_featured";
        $values[':is_featured'] = isset($data['is_featured']) ? 1 : 0;
        
        if(empty($fields)) {
            return false; // Nothing to update
        }
        
        // Build and prepare query
        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        // Add ID to values
        $values[':id'] = htmlspecialchars(strip_tags($data['id']));
        
        // Bind all values
        foreach($values as $param => &$value) {
            $stmt->bindParam($param, $value);
        }
        
        // Execute query
        try {
            if($stmt->execute()) {
                return true;
            }
        } catch(PDOException $e) {
            // Log error or handle it appropriately
            error_log("Failed to update game: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // Delete game
    public function deleteGame($id) {
        // Query to delete game
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize id
        $id = htmlspecialchars(strip_tags($id));
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
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