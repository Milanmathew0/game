<?php
class User {
    private $conn;
    private $table_name = "users";
    
    // User properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $created;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Register new user
    public function register() {
        // Query to insert new user
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name = :username, 
                      email = :email, 
                      password = :password, 
                      created_at = NOW()";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        
        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        
        // Hash password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Login user
    public function login() {
        // Query to check if email exists
        $query = "SELECT id, name, password 
                  FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind value
        $stmt->bindParam(":email", $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get row count
        $num = $stmt->rowCount();
        
        // If email exists, check password
        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if(password_verify($this->password, $row['password'])) {
                // Set user properties
                $this->id = $row['id'];
                $this->username = $row['username'];
                
                return true;
            }
        }
        
        return false;
    }
    
    // Check if email exists
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT id, name, password 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind value
        $stmt->bindParam(1, $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get row count
        $num = $stmt->rowCount();
        
        // If email exists, return true
        if($num > 0) {
            return true;
        }
        
        return false;
    }
    
    // Get total number of users
    public function getTotalUsers() {
        // Query to get total users
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get user details by ID
    public function getUserById($id) {
        // Query to get user details
        $query = "SELECT id, name, email, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = ? 
                  LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>