<?php
class Order {
    private $conn;
    private $table_name = "orders";
    
    // Order properties
    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $created;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create order
    public function createOrder() {
        try {
            // Query to insert new order
            $query = "INSERT INTO " . $this->table_name . " 
                      SET user_id = :user_id, 
                          total_amount = :total_amount, 
                          status = :status, 
                          created_at = NOW()";
            
            // Log the query and values
            error_log("Creating order with: user_id = " . $this->user_id . ", total_amount = " . $this->total_amount . ", status = " . $this->status);
            
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
            error_log("Order created successfully with ID: " . $this->id);
            return true;
        }
        
        error_log("Failed to create order: " . implode(", ", $stmt->errorInfo()));
        return false;
    } catch (Exception $e) {
        error_log("Exception creating order: " . $e->getMessage());
        return false;
    }
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
    
    // Update order status
    public function updateStatus() {
        // Query to update order status
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
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

    // Get recent orders with user details
    public function getRecentOrders($limit = 5) {
        // Query to get recent orders with user details
        $query = "SELECT o.*, u.name as user_name 
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 ORDER BY o.created_at DESC 
                 LIMIT " . $limit;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get order statistics
    public function getOrderStats() {
        // Query to get order counts by status
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
                 FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get total revenue
    public function getTotalRevenue() {
        // Query to get total revenue from completed orders
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
                 FROM " . $this->table_name . "
                 WHERE status = 'completed'";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_revenue'];
    }
}?>
