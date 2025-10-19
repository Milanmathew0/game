<?php
class Cart {
    private $conn;
    private $table_name = "cart";
    
    // Cart properties
    public $id;
    public $user_id;
    public $game_id;
    public $quantity;
    public $created;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Add item to cart
    public function addItem() {
        // Check if item already exists in cart
        if($this->itemExists()) {
            // Update quantity
            return $this->updateQuantity();
        } else {
            // Insert new item
            $query = "INSERT INTO " . $this->table_name . " 
                      SET user_id = :user_id, 
                          game_id = :game_id, 
                          quantity = :quantity, 
                          created_at = NOW()";
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Sanitize inputs
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->game_id = htmlspecialchars(strip_tags($this->game_id));
            $this->quantity = htmlspecialchars(strip_tags($this->quantity));
            
            // Bind values
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":game_id", $this->game_id);
            $stmt->bindParam(":quantity", $this->quantity);
            
            // Execute query
            if($stmt->execute()) {
                return true;
            }
        }
        
        return false;
    }
    
    // Check if item exists in cart
    private function itemExists() {
        // Query to check if item exists
        $query = "SELECT id, quantity FROM " . $this->table_name . " 
                  WHERE user_id = ? AND game_id = ? 
                  LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(1, $this->user_id);
        $stmt->bindParam(2, $this->game_id);
        
        // Execute query
        $stmt->execute();
        
        // Get row count
        $num = $stmt->rowCount();
        
        // If item exists, set ID and quantity
        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->quantity += $row['quantity'];
            return true;
        }
        
        return false;
    }
    
    // Update item quantity
    private function updateQuantity() {
        // Query to update quantity
        $query = "UPDATE " . $this->table_name . " 
                  SET quantity = :quantity 
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get cart items by user ID
    public function getCartItems($user_id) {
        // Query to get cart items with game details
        $query = "SELECT c.id, c.quantity, g.id as game_id, g.title, g.price, g.image 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN games g ON c.game_id = g.id 
                  WHERE c.user_id = ? 
                  ORDER BY c.created_at DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Remove item from cart
    public function removeItem($item_id) {
        // Query to remove item
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $item_id = htmlspecialchars(strip_tags($item_id));
        
        // Bind value
        $stmt->bindParam(1, $item_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Clear cart
    public function clearCart($user_id) {
        // Query to clear cart
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $user_id = htmlspecialchars(strip_tags($user_id));
        
        // Bind value
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get cart total
    public function getCartTotal($user_id) {
        // Query to get cart total
        $query = "SELECT SUM(g.price * c.quantity) as total 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN games g ON c.game_id = g.id 
                  WHERE c.user_id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }
}
?>