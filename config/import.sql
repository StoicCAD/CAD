CREATE DATABASE IF NOT EXISTS stoiccad;

USE stoiccad;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discord_id` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dept` varchar(100) DEFAULT 'CIV',
  `active_department` varchar(50) DEFAULT NULL,
  `rank` varchar(100) DEFAULT NULL,
  `badge_number` varchar(50) DEFAULT NULL,
  `super` tinyint(1) DEFAULT NULL,
  `online` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discord_id` (`discord_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `characters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discord` varchar(50) DEFAULT NULL,
  `steamid` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `twitter_name` varchar(50) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `dept` varchar(50) DEFAULT NULL,
  `active_department` varchar(50) DEFAULT NULL,
  `level` text DEFAULT NULL,
  `lastLoc` varchar(250) DEFAULT NULL,
  `mugshot` text DEFAULT NULL,
  `driverslicense` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `civilians` (
  `civilian_id` int(10) NOT NULL AUTO_INCREMENT,
  `discord_id` varchar(255) DEFAULT NULL,
  `character_id` int(11) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `cash` int(10) DEFAULT NULL,
  `bank` int(10) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`civilian_id`),
  KEY `character_id` (`character_id`),
  KEY `discord_id` (`discord_id`),
  CONSTRAINT `civilians_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`),
  CONSTRAINT `civilians_ibfk_2` FOREIGN KEY (`discord_id`) REFERENCES `users` (`discord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reported_by` int(11) NOT NULL,
  `status` varchar(255) DEFAULT 'Open',
  `attached_users` varchar(255) DEFAULT NULL, -- Added column for attached users
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `arrests` (
  `arrest_id` int(11) NOT NULL AUTO_INCREMENT,
  `character_id` int(11) NOT NULL,
  `officer_name` varchar(100) DEFAULT NULL,
  `arrest_date` datetime DEFAULT NULL,
  `charges` text DEFAULT NULL,
  `bail_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`arrest_id`),
  KEY `character_id` (`character_id`),
  CONSTRAINT `arrests_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `character_id` int(11) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `perpetrator` varchar(100) DEFAULT NULL,
  `report_date` datetime DEFAULT NULL,
  `report_content` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Open',
  PRIMARY KEY (`report_id`),
  KEY `character_id` (`character_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `character_id` int(11) NOT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  `issue_date` datetime DEFAULT NULL,
  `violation` text DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `character_id` (`character_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- CREATE TABLE IF NOT EXISTS `vehicles` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `plate` varchar(15) NOT NULL,
--   `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`properties`)),
--   `owner` int(11) DEFAULT NULL,
--   `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `plate` (`plate`),
--   KEY `owner` (`owner`),
--   CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `characters` (`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
