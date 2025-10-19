<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Check if user is logged in as admin
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Process actions
$message = '';
$error = '';

// Delete user
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $user->id = $_GET['delete'];
    if($user->delete()) {
        $message = "User was deleted successfully.";
    } else {
        $error = "Unable to delete user.";
    }
}

// Toggle admin status
if(isset($_GET['toggle_admin']) && !empty($_GET['toggle_admin'])) {
    $user->id = $_GET['toggle_admin'];
    if($user->toggleAdminStatus()) {
        $message = "User admin status updated successfully.";
    } else {
        $error = "Unable to update user admin status.";
    }
}

// Get all users
$users = $user->readAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
            margin-right: 5px;
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
        
        .admin-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .admin-badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .admin-badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Users</h1>
            <div>
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="../admin-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="games.php">Games</a></li>
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="orders.php">Orders</a></li>
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
            <h2>All Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users): ?>
                        <?php foreach($users as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td><?php echo $item['email']; ?></td>
                                <td><?php echo $item['created_at']; ?></td>
                                <td>
                                    <?php if($item['is_admin'] == 1): ?>
                                        <span class="admin-badge admin-badge-success">Admin</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-secondary">User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($item['id'] != $_SESSION['admin_id']): ?>
                                        <?php if($item['is_admin'] == 1): ?>
                                            <a href="users.php?toggle_admin=<?php echo $item['id']; ?>" class="btn btn-warning" onclick="return confirm('Remove admin privileges for this user?')">Remove Admin</a>
                                        <?php else: ?>
                                            <a href="users.php?toggle_admin=<?php echo $item['id']; ?>" class="btn btn-success" onclick="return confirm('Make this user an admin?')">Make Admin</a>
                                        <?php endif; ?>
                                        <a href="users.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    <?php else: ?>
                                        <span>Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>