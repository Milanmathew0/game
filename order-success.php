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

// Redirect to login if not logged in
if(!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Get order details
$order->id = $_GET['id'];
$orderDetails = $order->getOrderById($_GET['id']);

// Get payment details
$paymentDetails = $payment->getPaymentByOrderId($_GET['id']);

// Check if order belongs to current user
if($orderDetails['user_id'] != $_SESSION['user_id']) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="order-success">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Successful!</h1>
                <p>Thank you for your purchase. Your order has been successfully processed.</p>
                
                <div class="order-details">
                    <h2>Order Details</h2>
                    <div class="detail-item">
                        <span>Order ID:</span>
                        <span>#<?php echo $orderDetails['id']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Date:</span>
                        <span><?php echo date('F j, Y', strtotime($orderDetails['created_at'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Total Amount:</span>
                        <span>$<?php echo number_format($orderDetails['total_amount'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Payment Method:</span>
                        <span><?php echo ucfirst(str_replace('_', ' ', $paymentDetails['payment_method'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span>Transaction ID:</span>
                        <span><?php echo $paymentDetails['transaction_id']; ?></span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>