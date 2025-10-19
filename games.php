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

// Get all games
$games = $game->getAllGames();

// Handle search
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $games = $game->searchGames($_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Games - GameStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="container">
            <h1 class="page-title">All Games</h1>
            
            <div class="search-container">
                <form action="games.php" method="get">
                    <input type="text" name="search" placeholder="Search games..." class="search-input">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            
            <div class="games-grid">
                <?php if(count($games) > 0): ?>
                    <?php foreach($games as $gameItem): ?>
                    <div class="game-card">
                        <img src="<?php echo $gameItem['image']; ?>" alt="<?php echo $gameItem['title']; ?>">
                        <div class="game-info">
                            <h3><?php echo $gameItem['title']; ?></h3>
                            <p class="price">$<?php echo $gameItem['price']; ?></p>
                            <p class="description"><?php echo substr($gameItem['description'], 0, 100); ?>...</p>
                            <div class="game-actions">
                                <a href="game-details.php?id=<?php echo $gameItem['id']; ?>" class="btn btn-info">Details</a>
                                <a href="cart.php?action=add&id=<?php echo $gameItem['id']; ?>" class="btn btn-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>No games found. Please try a different search term.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>