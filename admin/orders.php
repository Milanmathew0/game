<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Order.php';

// Check if user is logged in as admin
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize order object
$order = new Order($db);

// Process actions
$message = '';
$error = '';

// Update order status
if(isset($_POST['update_status']) && !empty($_POST['order_id'])) {
    $order->id = $_POST['order_id'];
    $order->status = $_POST['status'];
    
    if($order->updateStatus()) {
        $message = "Order status updated successfully.";
    } else {
        $error = "Unable to update order status.";
    }
}

// View single order
$view_mode = false;
$order_details = null;
if(isset($_GET['view']) && !empty($_GET['view'])) {
    $view_mode = true;
    $order->id = $_GET['view'];
    $order_details = $order->readOne();
    $order_items = [];
}

// Get all orders
$orders = $order->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .admin-container {
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
        }
        
        .admin-nav {
            background-color: #333;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .admin-nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .admin-nav li {
            margin-right: 20px;
        }
        
        .admin-nav a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            color: #3498db;
        }
        
        .admin-content {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th, .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .admin-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-success {
            background-color: #2ecc71;
        }
        
        .btn-warning {
            background-color: #f39c12;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            margin-bottom: 30px;
        }
        
        .order-details h3 {
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            width: 150px;
            font-weight: bold;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Orders</h1>
            <div>
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="../admin-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="games.php">Games</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="../index.php" target="_blank">View Site</a></li>
            </ul>
        </div>
        
        <?php if($message): ?>
        <div class="alert alert-success">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="admin-content">
            <?php if($view_mode && $order_details): ?>
                <a href="orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                
                <div class="order-details">
                    <h3>Order #<?php echo $order_details['id']; ?> Details</h3>
                    
                    <div class="detail-row">
                        <div class="detail-label">Order ID:</div>
                        <div><?php echo $order_details['id']; ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Customer:</div>
                        <div><?php echo $order_details['user_name']; ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div><?php echo $order_details['user_email']; ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Date:</div>
                        <div><?php echo $order_details['created_at']; ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Total Amount:</div>
                        <div>₹<?php echo number_format($order_details['total_amount'], 2); ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Payment Method:</div>
                        <div><?php echo isset($order_details['payment_method']) && $order_details['payment_method'] ? ucfirst(str_replace('_',' ', $order_details['payment_method'])) : 'N/A'; ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div>
                            <span class="status-badge status-<?php echo strtolower($order_details['status']); ?>">
                                <?php echo $order_details['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Order items are not tracked in the current schema -->
                
                <div class="status-form">
                    <h3>Update Order Status</h3>
                    <form action="orders.php" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="pending" <?php echo ($order_details['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($order_details['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo ($order_details['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($order_details['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
                    </form>
                </div>
            <?php else: ?>
                <h2>All Orders</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($orders): ?>
                            <?php foreach($orders as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo $item['user_name']; ?></td>
                                    <td><?php echo $item['created_at']; ?></td>
                                    <td>₹<?php echo number_format($item['total_amount'], 2); ?></td>
                                    <td><?php echo isset($item['payment_method']) && $item['payment_method'] ? ucfirst(str_replace('_',' ', $item['payment_method'])) : 'N/A'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($item['status']); ?>">
                                            <?php echo $item['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?view=<?php echo $item['id']; ?>" class="btn">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>