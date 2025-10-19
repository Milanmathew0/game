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

// Get game ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Get game details
$gameDetails = $game->getGameById($id);

// Check if game exists
if(!$gameDetails) {
    header("Location: games.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $gameDetails['title']; ?> - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="game-details">
                <div class="game-details-image">
                    <img src="<?php echo $gameDetails['image']; ?>" alt="<?php echo $gameDetails['title']; ?>">
                </div>
                <div class="game-details-info">
                    <h1><?php echo $gameDetails['title']; ?></h1>
                    <p class="game-category">Category: <span><?php echo ucfirst($gameDetails['category']); ?></span></p>
                    <p class="game-price">$<?php echo $gameDetails['price']; ?></p>
                    <div class="game-description">
                        <h3>Description</h3>
                        <p><?php echo $gameDetails['description']; ?></p>
                    </div>
                    <div class="game-actions">
                        <a href="cart.php?action=add&id=<?php echo $gameDetails['id']; ?>" class="btn btn-primary">Add to Cart</a>
                        <a href="games.php" class="btn btn-secondary">Back to Games</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>