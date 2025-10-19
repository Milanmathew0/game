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

// Get featured games
$featuredGames = $game->getFeaturedGames();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameStore - Your Ultimate Gaming Destination</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome to GameStore</h1>
                <p>Your ultimate destination for the latest and greatest games</p>
                <a href="#featured-games" class="btn">Explore Games</a>
            </div>
        </section>

        <section id="featured-games" class="featured-games">
            <div class="container">
                <h2>Featured Games</h2>
                <div class="games-grid">
                    <?php foreach($featuredGames as $game): ?>
                    <div class="game-card">
                        <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['title']; ?>">
                        <div class="game-info">
                            <h3><?php echo $game['title']; ?></h3>
                            <p class="price">$<?php echo $game['price']; ?></p>
                            <p class="description"><?php echo substr($game['description'], 0, 100); ?>...</p>
                            <div class="game-actions">
                                <a href="game-details.php?id=<?php echo $game['id']; ?>" class="btn btn-info">Details</a>
                                <a href="cart.php?action=add&id=<?php echo $game['id']; ?>" class="btn btn-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <h2>Browse by Category</h2>
                <div class="category-grid">
                    <a href="category.php?cat=action" class="category-card">
                        <div class="category-icon"><i class="fas fa-running"></i></div>
                        <h3>Action</h3>
                    </a>
                    <a href="category.php?cat=adventure" class="category-card">
                        <div class="category-icon"><i class="fas fa-mountain"></i></div>
                        <h3>Adventure</h3>
                    </a>
                    <a href="category.php?cat=rpg" class="category-card">
                        <div class="category-icon"><i class="fas fa-dragon"></i></div>
                        <h3>RPG</h3>
                    </a>
                    <a href="category.php?cat=strategy" class="category-card">
                        <div class="category-icon"><i class="fas fa-chess"></i></div>
                        <h3>Strategy</h3>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
</body>
</html>