-- MySQL dump 10.13  Distrib 5.5.29, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bloodhound
-- ------------------------------------------------------
-- Server version	5.5.29-0ubuntu0.12.04.1

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
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `idaudit` int(15) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `idusers_fk` int(15) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `action` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`idaudit`),
  KEY `idusers_fk` (`idusers_fk`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COMMENT='Audit table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `new_studies`
--

DROP TABLE IF EXISTS `new_studies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new_studies` (
  `idnewstudies` bigint(20) NOT NULL AUTO_INCREMENT,
  `study_date` varchar(25) DEFAULT NULL,
  `study_time` varchar(25) DEFAULT NULL,
  `accession` varchar(25) DEFAULT NULL,
  `institution` varchar(25) DEFAULT NULL,
  `exam_description` varchar(75) DEFAULT NULL,
  `patient_name` varchar(75) DEFAULT NULL,
  `mrn` varchar(25) DEFAULT NULL,
  `idusers_fk` int(11) DEFAULT NULL,
  `idstudies_fk` bigint(20) DEFAULT NULL,
  `created_dttm` datetime DEFAULT NULL,
  `modality` varchar(25) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`idnewstudies`),
  KEY `idusers_fk` (`idusers_fk`),
  KEY `idstudies_fk` (`idstudies_fk`)
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `primary_studies`
--

DROP TABLE IF EXISTS `primary_studies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `primary_studies` (
  `idstudies` bigint(20) NOT NULL AUTO_INCREMENT,
  `accession` varchar(25) NOT NULL,
  `mrn` varchar(25) NOT NULL,
  `study_date` varchar(25) DEFAULT NULL,
  `modality` varchar(25) DEFAULT NULL,
  `idusers_fk` int(11) DEFAULT NULL,
  `notes` text,
  `study_time` varchar(25) DEFAULT NULL,
  `created_dttm` datetime DEFAULT NULL,
  `institution` varchar(25) DEFAULT NULL,
  `exam_description` varchar(75) DEFAULT NULL,
  `patient_name` varchar(75) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `email_status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`idstudies`),
  KEY `idusers_fk` (`idusers_fk`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracked_bodyparts`
--

DROP TABLE IF EXISTS `tracked_bodyparts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracked_bodyparts` (
  `idtrbodyparts` bigint(20) NOT NULL AUTO_INCREMENT,
  `bodypart` varchar(25) DEFAULT NULL,
  `idstudies_fk` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`idtrbodyparts`),
  KEY `idstudies_fk` (`idstudies_fk`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracked_modalities`
--

DROP TABLE IF EXISTS `tracked_modalities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracked_modalities` (
  `idtrmods` bigint(20) NOT NULL AUTO_INCREMENT,
  `modality` varchar(10) DEFAULT NULL,
  `idstudies_fk` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`idtrmods`),
  KEY `idstudies_fk` (`idstudies_fk`)
) ENGINE=MyISAM AUTO_INCREMENT=83 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `idusers` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) DEFAULT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `user_type` varchar(25) DEFAULT NULL,
  `pgy` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idusers`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-12-03  8:42:15
