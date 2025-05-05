CREATE TABLE `badge_definitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(35) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
