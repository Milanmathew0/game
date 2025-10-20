<?php
session_start();

require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Payment.php';
require_once 'classes/Cart.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$user = new User($db);
$order = new Order($db);
$payment = new Payment($db);
$cart = new Cart($db);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) ? true : false;

if(!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

// Get user details
$user->id = $_SESSION['user_id'];
$userData = $user->getUserById($_SESSION['user_id']);

// Get user orders
$order->user_id = $_SESSION['user_id'];
$userOrders = $order->getOrdersByUserId($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <h1 class="page-title">My Account</h1>
            
            <div class="account-container">
                <div class="account-sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-name">
                            <?php echo $userData['name']; ?>
                        </div>
                        <div class="user-email">
                            <?php echo $userData['email']; ?>
                        </div>
                    </div>
                    
                    <ul class="account-menu">
                        <li class="active"><a href="#profile">Profile</a></li>
                        <li><a href="#orders">Order History</a></li>
                    </ul>
                </div>
                
                <div class="account-content">
                    <div id="profile" class="account-section active">
                        <h2>Profile Information</h2>
                        
                        <div class="profile-info">
                            <div class="info-group">
                                <label>Name:</label>
                                <span><?php echo $userData['name']; ?></span>
                            </div>
                            
                            <div class="info-group">
                                <label>Email:</label>
                                <span><?php echo $userData['email']; ?></span>
                            </div>
                            
                            <div class="info-group">
                                <label>Member Since:</label>
                                <span><?php echo date('F j, Y', strtotime($userData['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div id="orders" class="account-section">
                        <h2>Order History</h2>
                        
                        <?php if(count($userOrders) > 0): ?>
                        <div class="order-history">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($userOrders as $userOrder): ?>
                                    <tr>
                                        <td>#<?php echo $userOrder['id']; ?></td>
                                        <td><?php echo date('F j, Y', strtotime($userOrder['created_at'])); ?></td>
                                        <td>$<?php echo number_format($userOrder['total_amount'], 2); ?></td>
                                        <td><span class="status-<?php echo $userOrder['status']; ?>"><?php echo ucfirst($userOrder['status']); ?></span></td>
                                        <td><a href="order-success.php?id=<?php echo $userOrder['id']; ?>" class="btn btn-sm">View</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="no-orders">
                            <p>You haven't placed any orders yet.</p>
                            <a href="games.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Simple tab functionality for account sections
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.account-menu li');
            const sections = document.querySelectorAll('.account-section');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all menu items
                    menuItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked menu item
                    this.classList.add('active');
                    
                    // Get target section id
                    const targetId = this.querySelector('a').getAttribute('href');
                    
                    // Hide all sections
                    sections.forEach(section => section.classList.remove('active'));
                    
                    // Show target section
                    document.querySelector(targetId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>