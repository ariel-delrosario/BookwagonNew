-- Drop table if exists
DROP TABLE IF EXISTS `book_swaps`;

-- Create book_swaps table
CREATE TABLE `book_swaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text,
  `condition` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `genre` varchar(255),
  `status` varchar(50) NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `book_swaps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test data
INSERT INTO `book_swaps` (`user_id`, `book_title`, `author`, `description`, `condition`, `image_path`, `genre`, `status`) VALUES
(1, 'The Great Gatsby', 'F. Scott Fitzgerald', 'A classic novel about the American Dream', 'Good', 'uploads/books/gatsby.jpg', 'Fiction', 'available'),
(1, 'To Kill a Mockingbird', 'Harper Lee', 'A powerful story about justice and racism', 'Like New', 'uploads/books/mockingbird.jpg', 'Fiction', 'available'); 