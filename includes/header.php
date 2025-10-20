<header>
    <div class="container header-container">
        <a href="index.php" class="logo">GameStore</a>
        
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="games.php">Games</a></li>
            <li><a href="category.php?cat=action">Action</a></li>
            <li><a href="category.php?cat=adventure">Adventure</a></li>
            <li><a href="category.php?cat=rpg">RPG</a></li>
            <li><a href="category.php?cat=strategy">Strategy</a></li>
        </ul>
        
        <div class="user-actions">
            <?php if($isLoggedIn): ?>
                <a href="my-account.php">My Account</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            
            <a href="cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if($isLoggedIn): ?>
                    <?php 
                    $cartItems = $cart->getCartItems($_SESSION['user_id']);
                    $cartCount = count($cartItems);
                    ?>
                    <?php if($cartCount > 0): ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>