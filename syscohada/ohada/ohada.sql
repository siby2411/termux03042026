-- MySQL dump 10.13  Distrib 8.3.0, for Win64 (x86_64)
--
-- Host: localhost    Database: ohada
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `classes_ohada`
--

DROP TABLE IF EXISTS `classes_ohada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes_ohada` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_classe` varchar(10) NOT NULL,
  `intitule_classe` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes_ohada`
--

LOCK TABLES `classes_ohada` WRITE;
/*!40000 ALTER TABLE `classes_ohada` DISABLE KEYS */;
INSERT INTO `classes_ohada` VALUES (1,'Classe 1','Comptes de capitaux'),(2,'Classe 2','Comptes d?immobilisations'),(3,'Classe 3','Comptes de stocks');
/*!40000 ALTER TABLE `classes_ohada` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comptes_ohada`
--

DROP TABLE IF EXISTS `comptes_ohada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comptes_ohada` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_compte` varchar(20) NOT NULL,
  `intitule` varchar(255) NOT NULL,
  `sous_classe_id` int DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `sous_classe_id` (`sous_classe_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comptes_ohada`
--

LOCK TABLES `comptes_ohada` WRITE;
/*!40000 ALTER TABLE `comptes_ohada` DISABLE KEYS */;
INSERT INTO `comptes_ohada` VALUES (1,'123456','Capital social',1,NULL);
/*!40000 ALTER TABLE `comptes_ohada` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sous_classes_ohada`
--

DROP TABLE IF EXISTS `sous_classes_ohada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sous_classes_ohada` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_sous_classe` varchar(10) NOT NULL,
  `intitule_sous_classe` varchar(255) NOT NULL,
  `classe_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classe_id` (`classe_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sous_classes_ohada`
--

LOCK TABLES `sous_classes_ohada` WRITE;
/*!40000 ALTER TABLE `sous_classes_ohada` DISABLE KEYS */;
INSERT INTO `sous_classes_ohada` VALUES (1,'101','Capital social',1),(2,'201','Frais d?établissement',2),(3,'301','Stocks de marchandises',3);
/*!40000 ALTER TABLE `sous_classes_ohada` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-23 16:48:51
