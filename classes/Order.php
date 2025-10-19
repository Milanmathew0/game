<?php
class Order {
    private $conn;
    private $table_name = "orders";
    
    // Order properties
    public $id;
    public $user_id;
    public $total_amount;
    public $payment_method;
    public $status;
    public $created;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get total count of orders
    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Get total revenue
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_amount) as total FROM " . $this->table_name . " WHERE status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ? $row['total'] : 0;
    }
    
    // Get recent orders
    public function getRecentOrders($limit = 5) {
        $query = "SELECT o.*, u.name as user_name 
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 ORDER BY o.created_at DESC LIMIT " . $limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create order
    public function createOrder() {
        // Query to insert new order
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id = :user_id, 
                      total_amount = :total_amount, 
                      status = :status, 
                      created_at = NOW()";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if($stmt->execute()) {
            // Get last inserted ID
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Get order by ID
    public function getOrderById($id) {
        // Query to get order by ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get orders by user ID
    public function getOrdersByUserId($user_id) {
        // Query to get orders by user ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY created_at DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Read all orders for admin
    public function readAll() {
        $query = "SELECT o.*, u.name as user_name, u.email as user_email, p.payment_method 
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 LEFT JOIN payments p ON p.order_id = o.id
                 ORDER BY o.created_at DESC";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Read one order with details
    public function readOne() {
        $query = "SELECT o.*, u.name as user_name, u.email as user_email, p.payment_method 
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 LEFT JOIN payments p ON p.order_id = o.id
                 WHERE o.id = ?
                 LIMIT 0,1";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get order items
    public function getOrderItems() {
        $query = "SELECT oi.*, g.title as game_title 
                 FROM order_items oi
                 LEFT JOIN games g ON oi.game_id = g.id
                 WHERE oi.order_id = ?";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update order status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                 SET status = :status
                 WHERE id = :id";
                 
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}