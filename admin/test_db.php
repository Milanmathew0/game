<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "Database connection successful!\n";
        
        // Test admin table
        $query = "SELECT * FROM admins";
        $stmt = $db->query($query);
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nFound " . count($admins) . " admin(s) in database:\n";
        foreach ($admins as $admin) {
            echo "Username: " . $admin['username'] . "\n";
            echo "Email: " . $admin['email'] . "\n";
            echo "Password Hash: " . $admin['password'] . "\n\n";
        }
        
        // Test password verification
        $testPassword = 'admin123';
        foreach ($admins as $admin) {
            echo "Testing password for " . $admin['email'] . ":\n";
            $verify = password_verify($testPassword, $admin['password']);
            echo "Password verification " . ($verify ? "SUCCESSFUL" : "FAILED") . "\n\n";
        }
        
        // Generate new hash for reference
        echo "Generating new hash for 'admin123':\n";
        $newHash = password_hash('admin123', PASSWORD_BCRYPT);
        echo $newHash . "\n";
        
    } else {
        echo "Database connection failed!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>