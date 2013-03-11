-- MySQL dump 10.13  Distrib 5.5.29, for Linux (x86_64)
--
-- Host: localhost    Database: repobuild
-- ------------------------------------------------------
-- Server version	5.5.29-log

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
-- Current Database: `repobuild`
--

/*!40000 DROP DATABASE IF EXISTS `repobuild`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `repobuild` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `repobuild`;

--
-- Table structure for table `archs`
--

DROP TABLE IF EXISTS `archs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archs` (
  `id` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `display_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `builds`
--

DROP TABLE IF EXISTS `builds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `builds` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `repo` int(11) NOT NULL,
  `packet` smallint(3) NOT NULL,
  `key` varchar(32) DEFAULT NULL,
  `builded` enum('yes','no') NOT NULL DEFAULT 'no',
  `version` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `builds_opts`
--

DROP TABLE IF EXISTS `builds_opts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `builds_opts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `build` int(11) NOT NULL,
  `option` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `id` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `display_name` varchar(64) NOT NULL,
  `packet` smallint(3) NOT NULL,
  `description` text,
  `deps` varchar(255) DEFAULT NULL,
  `custom` varchar(255) DEFAULT NULL,
  `allow_custom` enum('no','yes') NOT NULL DEFAULT 'no',
  `default` enum('yes','no') NOT NULL DEFAULT 'no',
  `need` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `os`
--

DROP TABLE IF EXISTS `os`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `os` (
  `id` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `display_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `packets`
--

DROP TABLE IF EXISTS `packets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packets` (
  `id` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `display_name` varchar(64) DEFAULT NULL,
  `deps` varchar(255) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `repos`
--

DROP TABLE IF EXISTS `repos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `os` smallint(2) NOT NULL,
  `arch` smallint(2) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `active` enum('no','yes') NOT NULL DEFAULT 'no',
  `trial` enum('no','yes') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-03-11 15:43:58
