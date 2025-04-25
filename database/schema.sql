-- Create database
CREATE DATABASE IF NOT EXISTS booking_system;
USE booking_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Resource categories table
CREATE TABLE IF NOT EXISTS resource_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Resources table
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    capacity INT,
    status ENUM('available', 'maintenance', 'inactive') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES resource_categories(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('admin', '$2y$10$8Y4mQB2TKhXeYUplOsXfPeEzI5roldxUvE3g6gYn1YpA1Yv9SFNB.', 'admin@example.com', 'System Administrator', 'admin');

-- Insert default resource categories
INSERT INTO resource_categories (name, description) VALUES 
('Meeting Rooms', 'Various sizes of meeting rooms'),
('Vehicles', 'Company cars and other vehicles'),
('Equipment', 'Office equipment for booking');

-- Insert sample resources
INSERT INTO resources (category_id, name, description, location, capacity) VALUES 
(1, 'Conference Room A', 'Large conference room with projector', 'Building A, Floor 2', 20),
(1, 'Meeting Room B', 'Small meeting room with whiteboard', 'Building A, Floor 1', 6),
(2, 'Toyota Camry', 'Company sedan, white color', 'Parking Lot B', 5),
(2, 'Ford Transit', 'Company van for larger groups', 'Parking Lot B', 8),
(3, 'Projector XD200', 'Portable projector with HDMI support', 'Storage Room 101', NULL); 