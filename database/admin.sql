-- Create admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@gamestore.com', '$2y$10$WQTibXuDejo/zkBlPfVrAO4d9wEN.2vhmhGPOhy/p1Bq2NtMYGn4G');