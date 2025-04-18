-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bookwagon_db;
USE bookwagon_db;

-- Drop existing tables if they exist (in correct order due to foreign key constraints)
DROP TABLE IF EXISTS seller_verification;
DROP TABLE IF EXISTS seller_identification;
DROP TABLE IF EXISTS seller_selfies;
DROP TABLE IF EXISTS seller_addresses;
DROP TABLE IF EXISTS seller_ids;
DROP TABLE IF EXISTS seller_details;
DROP TABLE IF EXISTS admin_activity_logs;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(50),
    lastName VARCHAR(50),
    middleInitial VARCHAR(1),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_seller BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Create admin_activity_logs table
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Create seller_details table
CREATE TABLE IF NOT EXISTS seller_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    social_media_link VARCHAR(255),
    verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    registration_complete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create seller_ids table
CREATE TABLE IF NOT EXISTS seller_ids (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    id_type ENUM('primary', 'secondary') NOT NULL,
    id_name VARCHAR(50) NOT NULL,
    id_front_image VARCHAR(255) NOT NULL,
    id_back_image VARCHAR(255) NOT NULL,
    other_id_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES seller_details(id)
);

-- Create seller_selfies table
CREATE TABLE IF NOT EXISTS seller_selfies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    selfie_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES seller_details(id)
);

-- Create seller_addresses table
CREATE TABLE IF NOT EXISTS seller_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES seller_details(id)
);

-- Create seller_verification table
CREATE TABLE IF NOT EXISTS seller_verification (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    verified_by INT,
    verification_date TIMESTAMP NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES seller_details(id),
    FOREIGN KEY (verified_by) REFERENCES admins(id)
);

-- Create sellers table
CREATE TABLE IF NOT EXISTS `sellers` (
  `seller_id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` char(1) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `social_media_link` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`seller_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create seller_identification table
CREATE TABLE IF NOT EXISTS `seller_identification` (
  `id_id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `id_type` enum('primary','secondary') NOT NULL,
  `id_document_type` varchar(50) NOT NULL,
  `other_id_type` varchar(100) DEFAULT NULL,
  `id_front_image` varchar(255) NOT NULL,
  `id_back_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_id`),
  KEY `seller_id` (`seller_id`),
  CONSTRAINT `seller_identification_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create seller_verification table
CREATE TABLE IF NOT EXISTS `seller_verification` (
  `verification_id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`verification_id`),
  KEY `seller_id` (`seller_id`),
  CONSTRAINT `seller_verification_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`seller_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create indexes for better performance
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_user_seller ON users(is_seller);
CREATE INDEX idx_admin_username ON admins(username);
CREATE INDEX idx_seller_details_user ON seller_details(user_id);
CREATE INDEX idx_seller_details_status ON seller_details(verification_status);
CREATE INDEX idx_seller_details_complete ON seller_details(registration_complete);
CREATE INDEX idx_seller_ids_seller ON seller_ids(seller_id);
CREATE INDEX idx_seller_selfies_seller ON seller_selfies(seller_id);
CREATE INDEX idx_seller_addresses_seller ON seller_addresses(seller_id);
CREATE INDEX idx_seller_verification_seller ON seller_verification(seller_id);
CREATE INDEX idx_seller_verification_status ON seller_verification(status);
CREATE INDEX idx_admin_activity_logs_admin ON admin_activity_logs(admin_id);

-- Insert default admin account
INSERT INTO admins (username, password, email) 
VALUES ('Admin', '$2y$10$8VQqUJqyGV0vxS2S/t5E/.eGhYo6UyGsRdkIQhb7TAWcaCvwYyTB.', 'admin@bookwagon.com')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;
-- Note: Default password is "123456" (hashed) 