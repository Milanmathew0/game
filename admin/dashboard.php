<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Admin.php';
require_once '../classes/Order.php';
require_once '../classes/User.php';
require_once '../classes/Game.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$admin = new Admin($db);
$order = new Order($db);
$user = new User($db);
$game = new Game($db);

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin details
$adminData = $admin->getAdminById($_SESSION['admin_id']);

// Get statistics
$recentOrders = $order->getRecentOrders(5);
$orderStats = $order->getOrderStats();
$totalUsers = $user->getTotalUsers();
$totalGames = $game->getTotalGames();
$totalRevenue = $order->getTotalRevenue();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GameStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-gamepad"></i>
                <span>GameStore Admin</span>
            </div>
            
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <div class="admin-user-info">
                    <span class="admin-username"><?php echo $adminData['username']; ?></span>
                    <span class="admin-role">Administrator</span>
                </div>
            </div>
            
            <nav class="admin-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="games.php">
                    <i class="fas fa-gamepad"></i>
                    Games
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    Orders
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    Users
                </a>
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    Categories
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </nav>
        </div>
        
        <!-- Admin Content -->
        <div class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-header-actions">
                    <a href="profile.php" class="btn btn-sm">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p><?php echo $orderStats['total_orders']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Revenue</h3>
                        <p>$<?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Games</h3>
                        <p><?php echo $totalGames; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="btn btn-sm">View All</a>
                </div>
                
                <div class="card-content">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo $order['user_name']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>