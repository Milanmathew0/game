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

$success = '';
$error = '';
$categoryData = [
    'id' => '',
    'name' => '',
    'description' => ''
];

// Check if editing existing category
if(isset($_GET['id'])) {
    $categoryData = $category->getCategoryById($_GET['id']);
    if(!$categoryData) {
        header("Location: categories.php");
        exit();
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category->name = $_POST['name'];
    $category->description = $_POST['description'];
    $category->slug = $category->generateSlug($_POST['name']);
    
    if(isset($_GET['id'])) {
        // Update existing category
        $category->id = $_GET['id'];
        if($category->updateCategory()) {
            $success = "Category updated successfully.";
            $categoryData = $category->getCategoryById($_GET['id']);
        } else {
            $error = "Failed to update category.";
        }
    } else {
        // Create new category
        if($category->createCategory()) {
            $success = "Category created successfully.";
            // Reset form
            $categoryData = [
                'id' => '',
                'name' => '',
                'description' => ''
            ];
        } else {
            $error = "Failed to create category.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Category - GameStore Admin</title>
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
                <h1><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Category</h1>
                <div class="admin-header-actions">
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Categories
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
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($categoryData['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($categoryData['description']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo isset($_GET['id']) ? 'Update' : 'Create'; ?> Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>