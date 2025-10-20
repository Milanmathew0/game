<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Admin.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed!");
}

// Initialize admin object
$admin = new Admin($db);

// Check if already logged in
if(isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Check if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $admin->email = $_POST['email'];
    $admin->password = $_POST['password'];
    
    error_log("Login attempt - Email: " . $admin->email);
    
    // Check if the admin exists first
    $query = "SELECT * FROM admins WHERE email = '" . $admin->email . "'";
    $stmt = $db->query($query);
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            error_log("Admin found in database. Stored hash: " . $result['password']);
            error_log("Verifying password...");
            if (password_verify($admin->password, $result['password'])) {
                error_log("Password verified successfully!");
            } else {
                error_log("Password verification failed!");
            }
        } else {
            error_log("No admin found with this email");
        }
    }
    
    // Attempt to login
    if($admin->login()) {
        error_log("Login successful for admin ID: " . $admin->id);
        // Create session
        $_SESSION['admin_id'] = $admin->id;
        $_SESSION['admin_username'] = $admin->username;
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        error_log("Login failed");
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GameStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h1>Admin Login</h1>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>