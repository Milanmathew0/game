CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add some default categories
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Action', 'action', 'Action games emphasize physical challenges, including hand–eye coordination and reaction-time.'),
('Adventure', 'adventure', 'Adventure games emphasize exploration, puzzle-solving, and narrative storytelling.'),
('RPG', 'rpg', 'Role-playing games where players assume the roles of characters in a fictional setting.'),
('Strategy', 'strategy', 'Strategy games emphasize skillful thinking and planning to achieve victory.'),
('Sports', 'sports', 'Sports games simulate traditional physical sports and activities.'),
('Racing', 'racing', 'Racing games that emphasize driving and racing vehicles in competitive scenarios.'),
('Simulation', 'simulation', 'Games designed to simulate real-world activities or scenarios.');