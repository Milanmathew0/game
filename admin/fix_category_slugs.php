<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Category object
$category = new Category($db);

// Get all categories
$query = "SELECT * FROM categories";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update categories with missing slugs
foreach($categories as $cat) {
    if(empty($cat['slug'])) {
        $query = "UPDATE categories SET slug = :slug WHERE id = :id";
        $stmt = $db->prepare($query);
        
        $slug = $category->generateSlug($cat['name']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':id', $cat['id']);
        
        if($stmt->execute()) {
            echo "Updated slug for category '{$cat['name']}' to '{$slug}'<br>";
        }
    }
}

echo "Category slug update complete!";
?>