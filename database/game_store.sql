-- Create database
CREATE DATABASE IF NOT EXISTS game_store;
USE game_store;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create games table
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    category_id INT,
    is_featured BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('credit_card', 'paypal', 'bank_transfer') NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES
('Action', 'action', 'Fast-paced games focused on combat and movement'),
('Adventure', 'adventure', 'Games that emphasize exploration and puzzle-solving'),
('RPG', 'rpg', 'Role-playing games with character development and storytelling'),
('Strategy', 'strategy', 'Games that require careful planning and tactical thinking'),
('Sports', 'sports', 'Games based on real-world sports and athletic competitions'),
('Simulation', 'simulation', 'Games that simulate real-world activities or systems');

-- Insert sample games
INSERT INTO games (title, description, price, image, category_id, is_featured) VALUES
('Cyberpunk 2077', 'An open-world, action-adventure RPG set in the megalopolis of Night City', 5000, 'https://cdn.akamai.steamstatic.com/steam/apps/1091500/header.jpg', 3, 1),
('FIFA 23', 'The latest edition of the popular football simulation game', 4175, 'https://cdn.akamai.steamstatic.com/steam/apps/1811260/header.jpg', 5, 1),
('Assassin''s Creed Valhalla', 'Become a legendary Viking warrior raised on tales of battle and glory', 3340, 'https://cdn.akamai.steamstatic.com/steam/apps/2208920/header.jpg', 2, 1),
('Call of Duty: Modern Warfare', 'Experience the ultimate online playground with classic multiplayer', 3750, 'https://cdn.akamai.steamstatic.com/steam/apps/2000950/header.jpg', 1, 1),
('The Sims 4', 'Create unique characters, build dream homes, and explore vibrant worlds', 2505, 'https://cdn.akamai.steamstatic.com/steam/apps/1222670/header.jpg', 6, 0),
('Civilization VI', 'Build your greatest empire on a landscape that is your playground', 1670, 'https://cdn.akamai.steamstatic.com/steam/apps/289070/header.jpg', 4, 0),
('Grand Theft Auto V', 'The biggest, most dynamic and most diverse open world ever created', 2505, 'https://cdn.akamai.steamstatic.com/steam/apps/271590/header.jpg', 1, 1),
('The Witcher 3: Wild Hunt', 'Become a professional monster slayer and embark on an epic journey', 2090, 'https://cdn.akamai.steamstatic.com/steam/apps/292030/header.jpg', 3, 0),
('Red Dead Redemption 2', 'An epic tale of life in America''s unforgiving heartland', 3340, 'https://cdn.akamai.steamstatic.com/steam/apps/1174180/header.jpg', 2, 1),
('Minecraft', 'Explore randomly generated worlds and build amazing things', 1670, 'https://upload.wikimedia.org/wikipedia/en/5/51/Minecraft_cover.png', 2, 0),
('League of Legends', 'A team-based strategy game with over 140 champions', 0, 'https://upload.wikimedia.org/wikipedia/en/7/77/League_of_Legends_logo.png', 4, 0),
('NBA 2K23', 'The latest installment in the world-renowned NBA 2K series', 5000, 'https://cdn.akamai.steamstatic.com/steam/apps/1919590/header.jpg', 5, 0);
