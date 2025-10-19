<?php
session_start();
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Game.php';

// Check if user is logged in as admin
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../admin-login.php");
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize game object
$game = new Game($db);

// Process form submissions
$message = '';
$error = '';

// Delete game
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $game->id = $_GET['delete'];
    if($game->delete()) {
        $message = "Game was deleted successfully.";
    } else {
        $error = "Unable to delete game.";
    }
}

// Process add/edit form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_game']) || isset($_POST['update_game'])) {
        // Set game properties
        $game->title = $_POST['title'];
        $game->description = $_POST['description'];
        $game->price = $_POST['price'];
        $game->category = $_POST['category'];
        
        // Handle image upload
        $target_dir = "../assets/images/games/";
        $upload_success = true;
        
        // Create directory if it doesn't exist
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Check if image was uploaded
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_file = $target_dir . basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if($check === false) {
                $error = "File is not an image.";
                $upload_success = false;
            }
            
            // Check file size
            if($_FILES['image']['size'] > 5000000) {
                $error = "Sorry, your file is too large.";
                $upload_success = false;
            }
            
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $upload_success = false;
            }
            
            // If everything is ok, try to upload file
            if($upload_success) {
                if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $game->image = basename($_FILES['image']['name']);
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                    $upload_success = false;
                }
            }
        } else if(isset($_POST['add_game'])) {
            // Image is required for new games
            $error = "Please select an image for the game.";
            $upload_success = false;
        }
        
        // If upload was successful or no new image was provided for update
        if($upload_success) {
            if(isset($_POST['add_game'])) {
                if($game->create()) {
                    $message = "Game was created successfully.";
                    // Clear form data
                    $game = new Game($db);
                } else {
                    $error = "Unable to create game.";
                }
            } else if(isset($_POST['update_game'])) {
                $game->id = $_POST['game_id'];
                if($game->update()) {
                    $message = "Game was updated successfully.";
                } else {
                    $error = "Unable to update game.";
                }
            }
        }
    }
}

// Get game for editing
$edit_mode = false;
if(isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $game->id = $_GET['edit'];
    $game->readOne();
}

// Get all games
$games = $game->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games - Admin Panel</title>
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
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .admin-form {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-form h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-group textarea {
            height: 150px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-success {
            background-color: #2ecc71;
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
        
        .game-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Games</h1>
            <div>
                <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="../admin-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="games.php" class="active">Games</a></li>
                <li><a href="users.php">Users</a></li>
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
            <div class="admin-form">
                <h2><?php echo $edit_mode ? 'Edit Game' : 'Add New Game'; ?></h2>
                <form action="games.php" method="post" enctype="multipart/form-data">
                    <?php if($edit_mode): ?>
                    <input type="hidden" name="game_id" value="<?php echo $game->id; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo $edit_mode ? $game->title : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo $edit_mode ? $game->description : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo $edit_mode ? $game->price : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Action" <?php echo ($edit_mode && $game->category == 'Action') ? 'selected' : ''; ?>>Action</option>
                            <option value="Adventure" <?php echo ($edit_mode && $game->category == 'Adventure') ? 'selected' : ''; ?>>Adventure</option>
                            <option value="RPG" <?php echo ($edit_mode && $game->category == 'RPG') ? 'selected' : ''; ?>>RPG</option>
                            <option value="Strategy" <?php echo ($edit_mode && $game->category == 'Strategy') ? 'selected' : ''; ?>>Strategy</option>
                            <option value="Sports" <?php echo ($edit_mode && $game->category == 'Sports') ? 'selected' : ''; ?>>Sports</option>
                            <option value="Simulation" <?php echo ($edit_mode && $game->category == 'Simulation') ? 'selected' : ''; ?>>Simulation</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image</label>
                        <?php if($edit_mode && $game->image): ?>
                        <div>
                            <img src="../assets/images/games/<?php echo $game->image; ?>" alt="<?php echo $game->title; ?>" class="game-image" style="margin-bottom: 10px;">
                            <p>Current image: <?php echo $game->image; ?></p>
                        </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" <?php echo $edit_mode ? '' : 'required'; ?>>
                        <?php if($edit_mode): ?>
                        <small>Leave empty to keep current image</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <?php if($edit_mode): ?>
                        <button type="submit" name="update_game" class="btn btn-success">Update Game</button>
                        <a href="games.php" class="btn">Cancel</a>
                        <?php else: ?>
                        <button type="submit" name="add_game" class="btn btn-success">Add Game</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div>
                <h2>All Games</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($games): ?>
                            <?php foreach($games as $item): ?>
                                <tr>
                                    <td>
                                        <?php if($item['image']): ?>
                                            <img src="../assets/images/games/<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="game-image">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['title']; ?></td>
                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['category']; ?></td>
                                    <td>
                                        <a href="games.php?edit=<?php echo $item['id']; ?>" class="btn">Edit</a>
                                        <a href="games.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this game?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No games found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>