<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Admin.php';
require_once '../classes/Game.php';
require_once '../classes/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$admin = new Admin($db);
$game = new Game($db);
$category = new Category($db);

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin details
$adminData = $admin->getAdminById($_SESSION['admin_id']);

// Get all categories for the select dropdown
$categories = $category->getAllCategories();

$success = '';
$error = '';
$gameData = [
    'id' => '',
    'title' => '',
    'description' => '',
    'price' => '',
    'image' => '',
    'category_id' => '',
    'is_featured' => 0
];

// Check if editing existing game
if(isset($_GET['id'])) {
    $gameData = $game->getGameById($_GET['id']);
    if(!$gameData) {
        header("Location: games.php");
        exit();
    }
}

    // Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify the category exists
    $categoryExists = $category->getCategoryById($_POST['category_id']);
    if(!$categoryExists) {
        $error = "Selected category does not exist.";
    } else {
        // Prepare game data
        $gameData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id'],
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];
        
        // Keep existing image if not uploading a new one
        if(isset($_GET['id']) && empty($_FILES['image']['name'])) {
            $existingGame = $game->getGameById($_GET['id']);
            $gameData['image'] = $existingGame['image'];
        }

        // Handle image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../assets/images/games/";
            if(!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            // Check file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if(in_array($file_extension, $allowed_types)) {
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Remove old image if exists
                    if(isset($_GET['id'])) {
                        $existingGame = $game->getGameById($_GET['id']);
                        if($existingGame && !empty($existingGame['image'])) {
                            $oldFile = "../" . $existingGame['image'];
                            if(file_exists($oldFile)) {
                                unlink($oldFile);
                            }
                        }
                    }
                    $gameData['image'] = 'assets/images/games/' . $new_filename;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }

        // Process form submission if no errors occurred
        if(!$error) {
            if(isset($_GET['id'])) {
                // Update existing game
                $gameData['id'] = $_GET['id'];
                if($game->updateGame($gameData)) {
                    $success = "Game updated successfully.";
                    // Refresh game data
                    $gameData = $game->getGameById($_GET['id']);
                } else {
                    $error = "Failed to update game.";
                    // Keep the form data to avoid losing user input
                    $gameData = array_merge($gameData, $_POST);
                }
            } else {
                // Create new game
                if(!isset($gameData['image'])) {
                    $error = "Please upload a game image.";
                } else if($game->createGame($gameData)) {
                    $success = "Game created successfully.";
                    // Reset form
                    $gameData = [
                        'id' => '',
                        'title' => '',
                        'description' => '',
                        'price' => '',
                        'image' => '',
                        'category_id' => '',
                        'is_featured' => 0
                    ];
                } else {
                    $error = "Failed to create game.";
                    // Keep the form data to avoid losing user input
                    $gameData = array_merge([
                        'id' => '',
                        'image' => '',
                        'is_featured' => 0
                    ], $_POST);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Game - GameStore Admin</title>
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
                <a href="games.php" class="active">
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
                <h1><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Game</h1>
                <div class="admin-header-actions">
                    <a href="games.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Games
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
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($gameData['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($gameData['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo htmlspecialchars($gameData['price']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $gameData['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Game Image</label>
                            <?php if($gameData['image']): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($gameData['image']); ?>" alt="Current game image" style="max-width: 200px;">
                                <p>Current image</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" id="image" name="image" class="form-control" <?php echo !isset($_GET['id']) ? 'required' : ''; ?>>
                            <small class="form-text text-muted">Allowed file types: JPG, JPEG, PNG, GIF</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured" value="1" <?php echo $gameData['is_featured'] ? 'checked' : ''; ?>>
                                Featured Game
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo isset($_GET['id']) ? 'Update' : 'Create'; ?> Game
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>