<?php
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Game.php';
require_once 'classes/Cart.php';
require_once 'classes/Order.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$user = new User($db);
$game = new Game($db);
$cart = new Cart($db);
$order = new Order($db);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) ? true : false;

// Redirect to login if not logged in
if(!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

// Get cart items and total
$cartItems = $cart->getCartItems($_SESSION['user_id']);
$cartTotal = $cart->getCartTotal($_SESSION['user_id']);

// Check if cart is empty
if(count($cartItems) == 0) {
    header("Location: cart.php");
    exit();
}

// Get user details
$userDetails = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <h1 class="page-title">Checkout</h1>
            
            <div class="checkout-container">
                <div class="checkout-form">
                    <h2>Shipping Information</h2>
                    <form action="payment.php" method="post">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo $userDetails['name']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo $userDetails['email']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-control" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="zip">ZIP Code</label>
                                <input type="text" id="zip" name="zip" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" class="form-control" required>
                            </div>
                        </div>
                        
                        <h2>Order Summary</h2>
                        <div class="order-summary">
                            <div class="order-item">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            <div class="order-item">
                                <span>Tax (10%):</span>
                                <span>₹<?php echo number_format($cartTotal * 0.1, 2); ?></span>
                            </div>
                            <div class="order-total">
                                <span>Total:</span>
                                <span>₹<?php echo number_format($cartTotal * 1.1, 2); ?></span>
                            </div>
                        </div>
                        
                        <input type="hidden" name="total" value="<?php echo $cartTotal * 1.1; ?>">
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Proceed to Payment</button>
                        </div>
                    </form>
                </div>
                
                <div class="order-items">
                    <h2>Your Order</h2>
                    <?php foreach($cartItems as $item): ?>
                    <div class="order-item-card">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="order-item-image">
                        <div class="order-item-details">
                            <h3><?php echo $item['title']; ?></h3>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p class="price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>