<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Game.php';
require_once '../classes/User.php';
require_once '../classes/Order.php';

// Check if user is logged in as admin
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$game = new Game($db);
$user = new User($db);
$order = new Order($db);

// Get counts for dashboard
$gameCount = $game->getCount();
$userCount = $user->getCount();
$orderCount = $order->getCount();
$revenueTotal = $order->getTotalRevenue();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
        
        .admin-nav a:hover {
            color: #3498db;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #333;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #777;
            font-size: 0.9rem;
        }
        
        .recent-section {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .recent-section h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
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
        
        .logout-btn {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <div>
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="../admin-logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="games.php">Games</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="../index.php" target="_blank">View Site</a></li>
            </ul>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Games</h3>
                <div class="number"><?php echo $gameCount; ?></div>
                <div class="label">Games in inventory</div>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $userCount; ?></div>
                <div class="label">Registered users</div>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $orderCount; ?></div>
                <div class="label">Completed orders</div>
            </div>
            
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="number">₹<?php echo number_format($revenueTotal, 2); ?></div>
                <div class="label">Revenue generated</div>
            </div>
        </div>
        
        <div class="recent-section">
            <h2>Recent Orders</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recentOrders = $order->getRecentOrders(5);
                    if($recentOrders) {
                        foreach($recentOrders as $order) {
                            echo "<tr>";
                            echo "<td>{$order['id']}</td>";
                            echo "<td>{$order['user_name']}</td>";
                            echo "<td>{$order['created_at']}</td>";
                            echo "<td>₹" . number_format($order['total_amount'], 2) . "</td>";
                            echo "<td>{$order['status']}</td>";
                            echo "<td><a href='orders.php?view={$order['id']}' class='btn'>View</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No recent orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="recent-section">
            <h2>Recent Games Added</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recentGames = $game->getRecentGames(5);
                    if($recentGames) {
                        foreach($recentGames as $game) {
                            echo "<tr>";
                            echo "<td>{$game['id']}</td>";
                            echo "<td>{$game['title']}</td>";
                            echo "<td>₹" . number_format($game['price'], 2) . "</td>";
                            echo "<td>{$game['category']}</td>";
                            echo "<td>
                                    <a href='games.php?edit={$game['id']}' class='btn'>Edit</a>
                                    <a href='games.php?delete={$game['id']}' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No games found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>