# ************************************************************
# Sequel Pro SQL dump
# Version 4383
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.19-0ubuntu0.14.04.1)
# Database: you-want-food-dev
# Generation Time: 2015-01-26 08:14:46 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table email_subscriptions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `email_subscriptions`;

CREATE TABLE `email_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT '1',
  `signup` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `token` varchar(100) NOT NULL DEFAULT '',
  `ipaddress` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table facility_mappings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `facility_mappings`;

CREATE TABLE `facility_mappings` (
  `id` int(11) unsigned NOT NULL,
  `facility_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table inspections_facilities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `inspections_facilities`;

CREATE TABLE `inspections_facilities` (
  `id` varchar(36) NOT NULL DEFAULT '',
  `name` varchar(100) DEFAULT NULL,
  `telephone` varchar(14) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `eatsmart` varchar(10) DEFAULT NULL,
  `open_date` date DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table inspections_infractions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `inspections_infractions`;

CREATE TABLE `inspections_infractions` (
  `id` varchar(38) NOT NULL DEFAULT '',
  `inspection_id` varchar(38) NOT NULL DEFAULT '',
  `type` varchar(20) DEFAULT NULL,
  `category_code` text,
  `letter_code` text,
  `description` text,
  `inspection_date` date DEFAULT NULL,
  `charge_details` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table inspections_inspections
# ------------------------------------------------------------

DROP TABLE IF EXISTS `inspections_inspections`;

CREATE TABLE `inspections_inspections` (
  `id` varchar(38) NOT NULL DEFAULT '',
  `facility_id` varchar(36) NOT NULL DEFAULT '',
  `inspection_date` date DEFAULT NULL,
  `require_reinspection` tinyint(1) DEFAULT NULL,
  `certified_food_handler` tinyint(1) DEFAULT NULL,
  `inspection_type` varchar(100) DEFAULT NULL,
  `charge_revoked` tinyint(1) DEFAULT NULL,
  `actions` text,
  `charge_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table outlet_reviews
# ------------------------------------------------------------

DROP TABLE IF EXISTS `outlet_reviews`;

CREATE TABLE `outlet_reviews` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` int(11) unsigned NOT NULL,
  `reviewer_name` varchar(255) DEFAULT NULL,
  `reviewer_email` varchar(255) DEFAULT NULL,
  `review` text,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `ipaddress` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
