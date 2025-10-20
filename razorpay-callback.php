<?php
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Game.php';
require_once 'classes/Cart.php';
require_once 'classes/Order.php';
require_once 'classes/Payment.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$user = new User($db);
$game = new Game($db);
$cart = new Cart($db);
$order = new Order($db);
$payment = new Payment($db);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) ? true : false;

// Redirect to login if not logged in
if(!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

// Check if Razorpay payment data is received
if(isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_order_id']) && isset($_POST['razorpay_signature'])) {
    $paymentId = $_POST['razorpay_payment_id'];
    $orderId = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    $amount = isset($_POST['amount']) ? $_POST['amount'] : 0;
    
    // Verify payment signature - simplified for demo
    if($payment->verifyRazorpayPayment($paymentId, $orderId, $signature)) {
        // Create order in database
        $order->user_id = $_SESSION['user_id'];
        $order->total_amount = $amount;
        $order->status = 'completed';
        
        if($order->createOrder()) {
            // Process payment
            $payment->user_id = $_SESSION['user_id'];
            $payment->order_id = $order->id;
            $payment->amount = $amount;
            $payment->payment_method = 'razorpay';
            $payment->transaction_id = $paymentId;
            $payment->status = 'completed';
            
            if($payment->processPayment()) {
                error_log("Razorpay payment processed successfully: Payment ID = " . $paymentId . ", Order ID = " . $order->id);
                // Clear cart
                $cart->clearCart($_SESSION['user_id']);
                
                // Redirect to success page
                header("Location: order-success.php?id=" . $order->id);
                exit();
            } else {
                error_log("Razorpay payment processing failed: Payment ID = " . $paymentId);
                $error = "Payment processing failed. Please try again.";
            }
        } else {
            $error = "Order creation failed. Please try again.";
        }
    } else {
        $error = "Payment verification failed. Please try again.";
    }
} else {
    $error = "Invalid payment data. Please try again.";
}

// If there was an error, redirect to payment page with error message
$_SESSION['payment_error'] = $error;
header("Location: payment.php");
exit();
?>