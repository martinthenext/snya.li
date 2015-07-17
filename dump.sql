-- MySQL dump 10.13  Distrib 5.5.43, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: snyali
-- ------------------------------------------------------
-- Server version	5.5.43-0+deb8u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dev_advert_types`
--

DROP TABLE IF EXISTS `dev_advert_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_advert_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_adverts`
--

DROP TABLE IF EXISTS `dev_adverts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_adverts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `type` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `vk_post_id` varchar(50) NOT NULL,
  `vk_owner_id` int(11) NOT NULL,
  `vk_owner_avatar` varchar(333) NOT NULL,
  `vk_owner_first_name` varchar(333) NOT NULL,
  `vk_owner_last_name` varchar(333) NOT NULL,
  `city_id` int(10) unsigned NOT NULL,
  `relevance` int(11) NOT NULL,
  `metro_id` int(11) NOT NULL,
  `export_vk_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `export_tweet_status_id` varchar(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `vk_post_id` (`vk_post_id`),
  KEY `type` (`type`),
  KEY `vk_owner_id` (`vk_owner_id`),
  KEY `city_id` (`city_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12460 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_attachments`
--

DROP TABLE IF EXISTS `dev_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `advert_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `src` varchar(333) NOT NULL,
  `src_big` varchar(333) NOT NULL,
  `src_xbig` varchar(333) NOT NULL,
  `src_xxbig` varchar(333) NOT NULL,
  `src_xxxbig` varchar(333) NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `adwert_id` (`advert_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18698 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_cities`
--

DROP TABLE IF EXISTS `dev_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `link` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `area` varchar(200) NOT NULL DEFAULT '',
  `region` varchar(200) NOT NULL DEFAULT '',
  `vk_city_id` int(10) unsigned NOT NULL DEFAULT '0',
  `with_geo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `geo_city_id` int(10) unsigned NOT NULL DEFAULT '0',
  `geo_lat` double NOT NULL,
  `geo_lon` double NOT NULL,
  `geo_region_id` int(10) unsigned NOT NULL DEFAULT '0',
  `geo_region_title` varchar(200) NOT NULL,
  `geo_region_link` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_contacts`
--

DROP TABLE IF EXISTS `dev_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `advert_id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'phone',
  `value` varchar(333) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `adwert_id` (`advert_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13514 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_groups`
--

DROP TABLE IF EXISTS `dev_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `client_secret` varchar(200) NOT NULL,
  `access_token` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_metro`
--

DROP TABLE IF EXISTS `dev_metro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_metro` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `link` varchar(50) NOT NULL,
  `city_id` int(10) unsigned NOT NULL,
  `pattern` varchar(333) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `city_id` (`city_id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_publics`
--

DROP TABLE IF EXISTS `dev_publics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_publics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `app_sercet` varchar(200) NOT NULL,
  `app_accesstoken` varchar(333) NOT NULL,
  `public_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_search_queries`
--

DROP TABLE IF EXISTS `dev_search_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_search_queries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(150) NOT NULL,
  `type` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_users`
--

DROP TABLE IF EXISTS `dev_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `service` varchar(255) NOT NULL DEFAULT 'aliebay',
  `service_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `disabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_service_id` (`service`,`service_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dev_users_blacklist`
--

DROP TABLE IF EXISTS `dev_users_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dev_users_blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vk_user_id` int(11) NOT NULL,
  `comment` varchar(333) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vk_user_id` (`vk_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1438 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(50) NOT NULL,
  `post_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`),
  KEY `post_id` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_migration`
--

DROP TABLE IF EXISTS `tbl_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-17 20:04:21
