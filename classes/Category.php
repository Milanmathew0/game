<?php
class Category {
    private $conn;
    private $table_name = "categories";
    
    // Category properties
    public $id;
    public $name;
    public $slug;
    public $description;
    public $created;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all categories
    public function getAllCategories() {
        // Query to get all categories
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get category by ID
    public function getCategoryById($id) {
        // Query to get category by ID
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create new category
    public function createCategory() {
        // Query to insert new category
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name = :name, 
                      slug = :slug,
                      description = :description,
                      created_at = NOW()";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Update category
    public function updateCategory() {
        // Query to update category
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      slug = :slug,
                      description = :description
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Delete category
    public function deleteCategory($id) {
        // Query to delete category
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize id
        $id = htmlspecialchars(strip_tags($id));
        
        // Bind value
        $stmt->bindParam(1, $id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Generate slug from category name
    public function generateSlug($name) {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(str_replace(' ', '-', $name));
        // Remove special characters
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
    }
}
?>