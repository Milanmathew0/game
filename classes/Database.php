<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;
    
    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            error_log("Attempting to connect to database: " . $this->db_name . " on host: " . $this->host);
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->exec("set names utf8");
            
            error_log("Database connection successful");
            
            // Test the connection with a simple query
            $test = $this->conn->query("SELECT 1");
            if ($test) {
                error_log("Database connection test successful");
            }
            
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database Connection Error: " . $e->getMessage());
        }
        
        return $this->conn;
    }
}
?>