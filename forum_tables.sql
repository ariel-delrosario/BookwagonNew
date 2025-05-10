-- Drop tables if they exist (in reverse order to avoid foreign key constraints)
DROP TABLE IF EXISTS `forum_comments`;
DROP TABLE IF EXISTS `forum_posts`;
DROP TABLE IF EXISTS `forum_categories`;

-- Create forum categories table
CREATE TABLE `forum_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50) DEFAULT 'fa-comments',
  `color` varchar(20) DEFAULT '#d9b99b',
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create forum posts table
CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` enum('active','closed','hidden') NOT NULL DEFAULT 'active',
  `views` int(11) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE current_timestamp,
  PRIMARY KEY (`post_id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `forum_categories` (`category_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create forum comments table
CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `likes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE current_timestamp,
  PRIMARY KEY (`comment_id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_id`) REFERENCES `forum_comments` (`comment_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default categories
INSERT INTO `forum_categories` (`name`, `description`, `icon`, `color`, `order_index`) VALUES
('Book Discussions', 'Discuss your favorite books, authors, and literary works', 'fa-book-open', '#3498db', 1),
('Reading Recommendations', 'Ask for and share book recommendations based on interests', 'fa-list', '#2ecc71', 2),
('Book Reviews', 'Share your thoughts and reviews on books you\'ve read', 'fa-star', '#f39c12', 3),
('Writing Corner', 'For aspiring writers to share their work and get feedback', 'fa-pen-fancy', '#9b59b6', 4),
('Book Clubs', 'Find and join book clubs or start your own', 'fa-users', '#e74c3c', 5),
('Literary Events', 'Discuss book fairs, author signings, and other literary events', 'fa-calendar', '#1abc9c', 6);

-- Create table for likes and bookmarks
CREATE TABLE `forum_user_interactions` (
  `interaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `interaction_type` enum('like','bookmark','follow') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`interaction_id`),
  UNIQUE KEY `unique_interaction` (`user_id`, `post_id`, `comment_id`, `interaction_type`),
  KEY `post_id` (`post_id`),
  KEY `comment_id` (`comment_id`),
  CONSTRAINT `fk_interaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interaction_post` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interaction_comment` FOREIGN KEY (`comment_id`) REFERENCES `forum_comments` (`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 