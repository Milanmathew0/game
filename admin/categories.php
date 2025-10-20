<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Admin.php';
require_once '../classes/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$admin = new Admin($db);
$category = new Category($db);

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin details
$adminData = $admin->getAdminById($_SESSION['admin_id']);

// Get all categories
$categories = $category->getAllCategories();

$success = '';
$error = '';

// Handle category deletion
if(isset($_POST['delete_category']) && isset($_POST['category_id'])) {
    if($category->deleteCategory($_POST['category_id'])) {
        $success = "Category deleted successfully.";
        // Refresh categories list
        $categories = $category->getAllCategories();
    } else {
        $error = "Failed to delete category. Make sure it's not assigned to any games.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - GameStore Admin</title>
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
                <a href="dashboard.php">
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
                <a href="categories.php" class="active">
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
                <h1>Manage Categories</h1>
                <div class="admin-header-actions">
                    <a href="category-form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Category
                    </a>
                </div>
            </div>
            
            <?php if($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-content">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo $cat['name']; ?></td>
                                <td><?php echo isset($cat['slug']) ? $cat['slug'] : '(auto-generated)'; ?></td>
                                <td><?php echo $cat['description']; ?></td>
                                <td class="actions">
                                    <a href="category-form.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? This will affect all associated games.');">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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