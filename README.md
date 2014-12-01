GTFS RESTful WebService
=======================

First of all, what is GTFS? It's the General Transport Feed Specification, a standard for managing public transportation data for a city or defined area. In short words, a GTFS repository is a .zip file containing lots of .txt files as comma-separated-values (CSV). More info: https://developers.google.com/transit/gtfs/

This project will try to create a WebService which will allow multiple users to manage multiple GTFS repositories in the same system, based on a RESTful point of view about web services, using Chris Kacerguis' REST implementation for CodeIgniter (https://github.com/chriskacerguis/codeigniter-restserver).

Requirements:
- The same as CodeIgniter (http://www.codeigniter.com/)
- MySQL 5+ database
- Some web server permissions for importing .zip files containing GTFS info (depending on the repository: lots of RAM and processing cycles; depending on the web server: permissions to upload files; etc.)

What this project aims for:
- Avoiding to implement and pay different GTFS editors and servers for each city, when you should be able to manage public transportation data in an unique system.
- Generating a web service, which any software (view) should be able to connect, gather, update or delete data from the database.
- Being able to import data from GTFS .zip files to the database and vice-versa.
- Being free software and easy to understand, so people could take the code and generate new versions from it (even new cool software!) without too much effort.

For who is this useful?
- Authorities who would want to keep public transportation data (schedules, agencies, etc.) in a single system and then being able to export it for public knowledge.
- Any person/company who would like to manage GTFS repositories and use them for any reason.
- Companies/people that want to start digging and learning about GTFS repositories.
- Companies/people that want to start consulting for another companies using public transportation information.

License?

Basically GPLv3 (see LICENSE.md). **You will not, however, be able to sell derivative software. Even more, derivative software will need to be FULLY GPL LICENSED, that means software which mixes any part of the code of this project with propietary code will not be tolerated. All diagrams, SQL sentences and other documentation included with this project is also restricted this way** (you can install this software on propietary servers, but you can't get money for that). You can, however, give support and get paid for it without problems after the system is functional (remember that modifications are GPL licensed!).


Which SQL is needed for creating the MySQL tables?
==================================================

**gtfsws_sessions** (this is the classic **CI_Session** for CodeIgniter)
```SQL
CREATE TABLE `gtfsws_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**gtfsws_repositories**
```SQL
CREATE TABLE `gtfsws_repositories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `start_position_lat` float(13,7) NOT NULL DEFAULT '0.0000000',
  `start_position_lon` float(13,7) NOT NULL DEFAULT '0.0000000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
```


**gtfsws_users**
```SQL
CREATE TABLE `gtfsws_users` (
  `email` varchar(255) NOT NULL,
  `password` char(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Es administrador; 0 = Es usuario normal (otros roles)',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**NOTE:** For creating the first admin account, use this SQL (replacing ```<admin_email>``` and ```<admin_name>``` for any values you want): ```INSERT INTO `gtfsws_users`(`email`, `password`, `name`, `is_admin`, `enabled`) VALUES('<admin_email>', '098f6bcd4621d373cade4e832627b4f6', '<admin_name>', 1, 1)```. The default password using this sentence is **test**.



**gtfsws_repository_users** (relationship to associate gtfsws_repositories to particular gtfsws_users with a role)
```SQL
CREATE TABLE `gtfsws_repository_users` (
  `user_email` varchar(255) NOT NULL,
  `repository_id` int(10) unsigned NOT NULL,
  `role` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_email`,`repository_id`),
  KEY `repository_id` (`repository_id`),
  CONSTRAINT `gtfsws_repository_users_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `gtfsws_users` (`email`),
  CONSTRAINT `gtfsws_repository_users_ibfk_2` FOREIGN KEY (`repository_id`) REFERENCES `gtfsws_repositories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**agency.txt**
```SQL
CREATE TABLE `agency` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `agency_id` varchar(100) NOT NULL,
  `agency_name` varchar(255) NOT NULL,
  `agency_url` varchar(255) DEFAULT NULL,
  `agency_timezone` varchar(50) NOT NULL DEFAULT 'America/Santiago',
  `agency_lang` char(2) NOT NULL DEFAULT 'es',
  `agency_phone` varchar(25) DEFAULT NULL,
  `agency_fare_url` varchar(255) DEFAULT NULL,
  UNIQUE KEY `agency_id` (`agency_id`),
  KEY `agency_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `agency_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**calendar.txt**
```SQL
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
  UNIQUE KEY `service_id` (`service_id`),
  KEY `calendar_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `calendar_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**routes.txt**
```SQL
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
  UNIQUE KEY `route_id` (`route_id`),
  KEY `routes_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**shapes.txt**
```SQL
CREATE TABLE `shapes` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `shape_id` varchar(100) NOT NULL,
  `shape_pt_lat` float(14,6) NOT NULL DEFAULT '0.000000',
  `shape_pt_lon` float(14,6) NOT NULL DEFAULT '0.000000',
  `shape_pt_sequence` int(10) unsigned NOT NULL,
  `shape_dist_traveled` float(7,2) NOT NULL DEFAULT '0.00',
  KEY `shapes_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `shapes_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**stop_times.txt**
```SQL
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
  `shape_dist_traveled` float(7,2) DEFAULT NULL,
  KEY `stop_times_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `stop_times_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**stops.txt**
```SQL
CREATE TABLE `stops` (
  `gtfsws_repository_id` int(10) unsigned NOT NULL,
  `stop_id` varchar(100) NOT NULL,
  `stop_code` varchar(50) DEFAULT NULL,
  `stop_name` varchar(255) DEFAULT NULL,
  `stop_desc` varchar(255) DEFAULT NULL,
  `stop_lat` float(19,13) NOT NULL DEFAULT '0.0000000000000',
  `stop_lon` float(19,13) NOT NULL DEFAULT '0.0000000000000',
  `zone_id` int(10) unsigned DEFAULT NULL,
  `stop_url` varchar(255) NOT NULL,
  `location_type` int(1) NOT NULL DEFAULT '0',
  `parent_station` int(10) unsigned DEFAULT NULL,
  `stop_timezone` varchar(255) DEFAULT 'America/Santiago',
  `wheelchair_boarding` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `stop_id` (`stop_id`),
  KEY `stops_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `stops_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```


**trips.txt**
```SQL
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
  UNIQUE KEY `trip_id` (`trip_id`),
  KEY `trips_ibfk_1` (`gtfsws_repository_id`),
  CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`gtfsws_repository_id`) REFERENCES `gtfsws_repositories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Where are the missing tables?
=============================
As this is a long work, I'll implement them by stages and the first one will aim to manage only these, which is the most basic usage for doing cool stuff.
You can, however, implement them by yourself if you like. Become a contributor!
