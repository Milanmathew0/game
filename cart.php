<?php
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Game.php';
require_once 'classes/Cart.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$user = new User($db);
$game = new Game($db);
$cart = new Cart($db);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) ? true : false;

// Redirect to login if not logged in
if(!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

// Process cart actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    switch($action) {
        case 'add':
            // Get game details
            $gameDetails = $game->getGameById($id);
            
            if($gameDetails) {
                // Set cart properties
                $cart->user_id = $_SESSION['user_id'];
                $cart->game_id = $id;
                $cart->quantity = 1;
                
                // Add item to cart
                if($cart->addItem()) {
                    header("Location: cart.php?success=added");
                    exit();
                }
            }
            break;
            
        case 'remove':
            // Remove item from cart
            if($cart->removeItem($id)) {
                header("Location: cart.php?success=removed");
                exit();
            }
            break;
            
        case 'clear':
            // Clear cart
            if($cart->clearCart($_SESSION['user_id'])) {
                header("Location: cart.php?success=cleared");
                exit();
            }
            break;
    }
}

// Get cart items
$cartItems = $cart->getCartItems($_SESSION['user_id']);
$cartTotal = $cart->getCartTotal($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container cart-container">
            <h1 class="cart-title">Shopping Cart</h1>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    switch($_GET['success']) {
                        case 'added':
                            echo "Item added to cart successfully.";
                            break;
                        case 'removed':
                            echo "Item removed from cart successfully.";
                            break;
                        case 'cleared':
                            echo "Cart cleared successfully.";
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if(count($cartItems) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Game</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cartItems as $item): ?>
                        <tr>
                            <td>
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="cart-item-image">
                            </td>
                            <td class="cart-item-title"><?php echo $item['title']; ?></td>
                            <td class="cart-item-price">₹<?php echo $item['price']; ?></td>
                            <td>
                                <div class="cart-quantity">
                                    <?php echo $item['quantity']; ?>
                                </div>
                            </td>
                            <td class="cart-item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <a href="cart.php?action=clear&id=0" class="btn btn-danger">Clear Cart</a>
                </div>
                
                <div class="cart-summary">
                    <h3 class="cart-summary-title">Order Summary</h3>
                    <div class="cart-summary-item">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                    <div class="cart-summary-item">
                        <span>Tax (10%):</span>
                        <span>₹<?php echo number_format($cartTotal * 0.1, 2); ?></span>
                    </div>
                    <div class="cart-total">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($cartTotal * 1.1, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <p>Your cart is empty.</p>
                    <a href="games.php" class="btn btn-primary">Browse Games</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>