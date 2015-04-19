# ************************************************************
# Sequel Pro SQL dump
# Versión 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 5.5.25)
# Base de datos: gtfsws
# Tiempo de Generación: 2015-04-19 03:12:42 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla agency
# ------------------------------------------------------------

DROP TABLE IF EXISTS `agency`;

CREATE TABLE `agency` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `agency_id` varchar(100) NOT NULL,
  `agency_name` varchar(255) NOT NULL,
  `agency_url` varchar(255) DEFAULT NULL,
  `agency_timezone` varchar(50) NOT NULL DEFAULT 'America/Santiago',
  `agency_lang` char(2) NOT NULL DEFAULT 'es',
  `agency_phone` varchar(25) DEFAULT NULL,
  `agency_fare_url` varchar(255) DEFAULT NULL,
  KEY `agency_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `agency_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla calendar
# ------------------------------------------------------------

DROP TABLE IF EXISTS `calendar`;

CREATE TABLE `calendar` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `service_id` varchar(100) NOT NULL,
  `monday` int(1) unsigned NOT NULL DEFAULT '1',
  `tuesday` int(1) unsigned NOT NULL DEFAULT '1',
  `wednesday` int(1) unsigned NOT NULL DEFAULT '1',
  `thursday` int(1) unsigned NOT NULL DEFAULT '1',
  `friday` int(1) unsigned NOT NULL DEFAULT '1',
  `saturday` int(1) unsigned NOT NULL DEFAULT '1',
  `sunday` int(1) unsigned NOT NULL DEFAULT '1',
  `start_date` char(8) NOT NULL DEFAULT '20140101',
  `end_date` char(8) NOT NULL DEFAULT '20141231',
  KEY `calendar_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `calendar_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla gtfsws_repositories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `gtfsws_repositories`;

CREATE TABLE `gtfsws_repositories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `start_position_lat` decimal(13,7) NOT NULL DEFAULT '0.0000000',
  `start_position_lon` decimal(13,7) NOT NULL DEFAULT '0.0000000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla gtfsws_repository_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `gtfsws_repository_users`;

CREATE TABLE `gtfsws_repository_users` (
  `user_email` varchar(255) NOT NULL,
  `repository_id` int(10) unsigned NOT NULL,
  `role` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_email`,`repository_id`),
  KEY `gtfsws_repository_users_ibfk_2` (`repository_id`),
  CONSTRAINT `gtfsws_repository_users_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `gtfsws_users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gtfsws_repository_users_ibfk_2` FOREIGN KEY (`repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla gtfsws_sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `gtfsws_sessions`;

CREATE TABLE `gtfsws_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla gtfsws_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `gtfsws_users`;

CREATE TABLE `gtfsws_users` (
  `email` varchar(255) NOT NULL,
  `password` char(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `gtfsws_users` WRITE;
/*!40000 ALTER TABLE `gtfsws_users` DISABLE KEYS */;

INSERT INTO `gtfsws_users` (`email`, `password`, `name`, `is_admin`, `enabled`)
VALUES
	('test@test.cl','098f6bcd4621d373cade4e832627b4f6','Usuario de prueba (Administrador)',1,1);

/*!40000 ALTER TABLE `gtfsws_users` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla routes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `routes`;

CREATE TABLE `routes` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `route_id` varchar(100) NOT NULL,
  `agency_id` varchar(100) NOT NULL,
  `route_short_name` varchar(50) DEFAULT NULL,
  `route_long_name` varchar(255) DEFAULT NULL,
  `route_desc` text,
  `route_type` int(2) NOT NULL DEFAULT '3',
  `route_url` varchar(255) DEFAULT NULL,
  `route_color` char(6) NOT NULL DEFAULT 'ffffff',
  `route_text_color` char(6) NOT NULL DEFAULT '000000',
  KEY `routes_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla shapes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shapes`;

CREATE TABLE `shapes` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `shape_id` varchar(100) NOT NULL,
  `shape_pt_lat` decimal(14,6) NOT NULL DEFAULT '0.000000',
  `shape_pt_lon` decimal(14,6) NOT NULL DEFAULT '0.000000',
  `shape_pt_sequence` int(10) unsigned NOT NULL,
  `shape_dist_traveled` decimal(7,2) NOT NULL DEFAULT '0.00',
  KEY `shapes_ibfk_1` (`gtfsws_repository_id`),
  KEY `Aceleración de búsqueda` (`shape_id`),
  CONSTRAINT `shapes_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla stop_times
# ------------------------------------------------------------

DROP TABLE IF EXISTS `stop_times`;

CREATE TABLE `stop_times` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `trip_id` varchar(100) NOT NULL,
  `arrival_time` char(8) NOT NULL DEFAULT '12:00:00',
  `departure_time` char(8) NOT NULL DEFAULT '12:00:00',
  `stop_id` varchar(100) NOT NULL,
  `stop_sequence` int(10) unsigned NOT NULL,
  `stop_headsign` varchar(255) DEFAULT NULL,
  `pickup_type` int(1) NOT NULL DEFAULT '3',
  `drop_off_type` int(1) NOT NULL DEFAULT '3',
  `shape_dist_traveled` decimal(7,2) DEFAULT NULL,
  KEY `stop_times_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `stop_times_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla stops
# ------------------------------------------------------------

DROP TABLE IF EXISTS `stops`;

CREATE TABLE `stops` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `stop_id` varchar(100) NOT NULL,
  `stop_code` varchar(50) DEFAULT NULL,
  `stop_name` varchar(255) DEFAULT NULL,
  `stop_desc` varchar(255) DEFAULT NULL,
  `stop_lat` decimal(19,13) NOT NULL DEFAULT '0.0000000000000',
  `stop_lon` decimal(19,13) NOT NULL DEFAULT '0.0000000000000',
  `zone_id` int(10) unsigned DEFAULT NULL,
  `stop_url` varchar(255) NOT NULL,
  `location_type` int(1) NOT NULL DEFAULT '0',
  `parent_station` int(10) unsigned DEFAULT NULL,
  `stop_timezone` varchar(255) DEFAULT 'America/Santiago',
  `wheelchair_boarding` int(1) NOT NULL DEFAULT '0',
  KEY `stops_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `stops_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla trips
# ------------------------------------------------------------

DROP TABLE IF EXISTS `trips`;

CREATE TABLE `trips` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `route_id` varchar(100) NOT NULL,
  `service_id` varchar(100) NOT NULL,
  `trip_id` varchar(100) NOT NULL,
  `trip_headsign` varchar(255) DEFAULT NULL,
  `trip_short_name` varchar(255) NOT NULL,
  `direction_id` int(1) NOT NULL DEFAULT '0',
  `block_id` int(10) unsigned DEFAULT NULL,
  `shape_id` varchar(100) NOT NULL,
  `wheelchair_accessible` int(1) NOT NULL DEFAULT '0',
  KEY `trips_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
