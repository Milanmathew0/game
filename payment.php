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

// Check if form is submitted
$error = '';
$success = '';
$razorpayOrder = null;

// Get cart total
$cartTotal = $cart->getCartTotal($_SESSION['user_id']);

// Create Razorpay order when page loads
if($cartTotal > 0) {
    $razorpayOrder = $payment->createRazorpayOrder($cartTotal);
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $total = isset($_POST['total']) ? $_POST['total'] : 0;
    
    // Check if payment method is submitted
    if(isset($_POST['payment_method'])) {
        if($_POST['payment_method'] == 'razorpay') {
            // Razorpay payment is handled via JavaScript and callback
            // This section is for traditional payment methods
        } else {
            // Create order
            $order->user_id = $_SESSION['user_id'];
            $order->total_amount = $total;
            $order->status = 'pending';
            
            if($order->createOrder()) {
                // Process payment
                $payment->user_id = $_SESSION['user_id'];
                $payment->order_id = $order->id;
                $payment->amount = $total;
                $payment->payment_method = $_POST['payment_method'];
                $payment->transaction_id = $payment->generateTransactionId();
                $payment->status = 'completed';
                
                if($payment->processPayment()) {
                    // Update order status
                    $order->id = $order->id;
                    $order->status = 'completed';
                    $order->updateStatus();
                    
                    // Clear cart
                    $cart->clearCart($_SESSION['user_id']);
                    
                    // Redirect to success page
                    header("Location: order-success.php?id=" . $order->id);
                    exit();
                } else {
                    $error = "Payment processing failed. Please try again.";
                }
            } else {
                $error = "Order creation failed. Please try again.";
            }
        }
    }
}

// Get cart total
$cartTotal = $cart->getCartTotal($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <h1 class="page-title">Payment</h1>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <!-- Razorpay JavaScript - Using basic checkout -->
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            
            <div class="payment-container">
                <form action="payment.php" method="post">
                    <h2>Select Payment Method</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="credit-card" name="payment_method" value="credit_card">
                            <label for="credit-card">
                                <div class="payment-method-header">
                                    <div class="payment-method-icon"><i class="fas fa-credit-card"></i></div>
                                    <div class="payment-method-title">Credit Card</div>
                                </div>
                            </label>
                            
                            <div class="payment-method-details">
                                <div class="form-group">
                                    <label for="card-number">Card Number</label>
                                    <input type="text" id="card-number" class="form-control" placeholder="1234 5678 9012 3456">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="expiry">Expiry Date</label>
                                        <input type="text" id="expiry" class="form-control" placeholder="MM/YY">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" class="form-control" placeholder="123">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="paypal" name="payment_method" value="paypal">
                            <label for="paypal">
                                <div class="payment-method-header">
                                    <div class="payment-method-icon"><i class="fab fa-paypal"></i></div>
                                    <div class="payment-method-title">PayPal</div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="razorpay" name="payment_method" value="razorpay" checked>
                            <label for="razorpay">
                                <div class="payment-method-header">
                                    <div class="payment-method-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="payment-method-title">Razorpay</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="order-item">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <div class="order-item">
                            <span>Tax (10%):</span>
                            <span>$<?php echo number_format($cartTotal * 0.1, 2); ?></span>
                        </div>
                        <div class="order-total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($cartTotal * 1.1, 2); ?></span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="total" value="<?php echo $cartTotal * 1.1; ?>">
                    
                    <div class="form-group">
                        <button type="button" id="razorpay-button" class="btn btn-primary" style="width: 100%;">Pay with Razorpay</button>
                        <button type="submit" id="standard-button" class="btn btn-primary" style="width: 100%; display:none;">Complete Payment</button>
                    </div>
                </form>
            </div>
            
            <script>
                // Toggle payment buttons based on selected method
                document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        if (this.value === 'razorpay') {
                            document.getElementById('razorpay-button').style.display = 'block';
                            document.getElementById('standard-button').style.display = 'none';
                        } else {
                            document.getElementById('razorpay-button').style.display = 'none';
                            document.getElementById('standard-button').style.display = 'block';
                        }
                    });
                });
                
                // Initialize Razorpay payment with minimal options
                document.getElementById('razorpay-button').addEventListener('click', function() {
                    <?php if($razorpayOrder): ?>
                    var options = {
                        "key": "<?php echo RAZORPAY_KEY_ID; ?>",
                        "amount": "<?php echo $razorpayOrder->amount; ?>",
                        "currency": "<?php echo RAZORPAY_CURRENCY; ?>",
                        "name": "<?php echo SITE_NAME; ?>",
                        "description": "Game Purchase",
                        "handler": function (response) {
                            // Submit form with Razorpay response
                            var form = document.createElement('form');
                            form.method = 'POST';
                            form.action = 'razorpay-callback.php';
                            
                            var fields = {
                                'razorpay_payment_id': response.razorpay_payment_id,
                                'amount': <?php echo $cartTotal; ?>
                            };
                            
                            for (var key in fields) {
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = fields[key];
                                form.appendChild(input);
                            }
                            
                            document.body.appendChild(form);
                            form.submit();
                        },
                        "theme": {
                            "color": "#3399cc"
                        }
                    };
                    var rzp = new Razorpay(options);
                    rzp.on('payment.failed', function (response){
                        alert("Payment failed. Please try again.");
                    });
                    rzp.open();
                    <?php else: ?>
                    alert('Unable to create payment order. Please try again.');
                    <?php endif; ?>
                });
            </script>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>