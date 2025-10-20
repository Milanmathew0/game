<?php
class Admin {
    private $conn;
    private $table_name = "admins";
    
    // Admin properties
    public $id;
    public $username;
    public $email;
    public $password;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Login admin
    public function login() {
        try {
            // Log login attempt
            error_log("Admin login attempt for email: " . $this->email);

            // Query to check if email exists
            $query = "SELECT id, username, password 
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
            
            error_log("Found " . $num . " admin accounts with this email");
            
            // If email exists
            if($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if(password_verify($this->password, $row['password'])) {
                    // Set admin properties
                    $this->id = $row['id'];
                    $this->username = $row['username'];
                    error_log("Password verified successfully for admin: " . $this->username);
                    return true;
                } else {
                    error_log("Password verification failed");
                }
            } else {
                error_log("No admin account found with this email");
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in admin login: " . $e->getMessage());
            return false;
        }
    }
    
    // Get admin details by ID
    public function getAdminById($id) {
        // Query to get admin by ID
        $query = "SELECT id, username, email, created_at 
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