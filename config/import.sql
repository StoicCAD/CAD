-- Create `users` table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discord_id` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dept` varchar(100) NOT NULL DEFAULT 'LSPD',
  `rank` varchar(100) DEFAULT NULL,
  `badge_number` varchar(50) DEFAULT NULL,
  `super` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discord_id` (`discord_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create `nd_characters` table
CREATE TABLE IF NOT EXISTS `nd_characters` (
  `charid` int(10) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(200) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `cash` int(10) DEFAULT 0,
  `bank` int(10) DEFAULT 0,
  `groups` longtext DEFAULT '[]',
  `metadata` longtext DEFAULT '[]',
  `inventory` longtext DEFAULT '[]',
  `phone_number` varchar(20) DEFAULT NULL,
  `mugshot` varchar(255) DEFAULT NULL,
  `driverslicense` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`charid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create `civillians` table
CREATE TABLE IF NOT EXISTS `civillians` (
  `civillian_id` int(10) NOT NULL AUTO_INCREMENT,
  `discord_id` varchar(255) DEFAULT NULL,
  `char_id` int(11) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `cash` int(10) DEFAULT NULL,
  `bank` int(10) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`civillian_id`),
  KEY `char_id` (`char_id`),  -- Ensure this is indexed
  KEY `discord_id` (`discord_id`),
  CONSTRAINT `civillians_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create `arrests` table
CREATE TABLE IF NOT EXISTS `arrests` (
  `arrest_id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `officer_name` varchar(100) DEFAULT NULL,
  `arrest_date` datetime DEFAULT NULL,
  `charges` text DEFAULT NULL,
  `bail_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`arrest_id`),
  KEY `char_id` (`char_id`),
  CONSTRAINT `arrests_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create `incidents` table
CREATE TABLE IF NOT EXISTS `incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) DEFAULT 'Open',
  PRIMARY KEY (`id`),
  KEY `reported_by` (`reported_by`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create `reports` table
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `perpetrator` varchar(100) DEFAULT NULL,
  `report_date` datetime DEFAULT NULL,
  `report_content` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Open',
  PRIMARY KEY (`report_id`),
  KEY `char_id` (`char_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create `tickets` table
CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  `issue_date` datetime DEFAULT NULL,
  `violation` text DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `char_id` (`char_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`char_id`) REFERENCES `nd_characters` (`charid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adding columns to `nd_characters`
ALTER TABLE `nd_characters`
ADD COLUMN `mugshot` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `driverslicense` VARCHAR(255) DEFAULT NULL;

