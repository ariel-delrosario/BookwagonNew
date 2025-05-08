-- Table structure for return_requests table
CREATE TABLE IF NOT EXISTS `return_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_method` enum('dropoff','pickup') NOT NULL,
  `return_location` text NOT NULL,
  `status` enum('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `is_overdue` tinyint(1) NOT NULL DEFAULT 0,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `days_overdue` int(11) DEFAULT 0,
  `admin_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rental_id` (`rental_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_return_request_rental` FOREIGN KEY (`rental_id`) REFERENCES `book_rentals` (`rental_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_return_request_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 