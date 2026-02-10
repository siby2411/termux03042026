-- MySQL dump 10.13  Distrib 8.3.0, for Win64 (x86_64)
--
-- Host: localhost    Database: compta
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
-- Table structure for table `classe`
--

DROP TABLE IF EXISTS `classe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classe`
--

LOCK TABLES `classe` WRITE;
/*!40000 ALTER TABLE `classe` DISABLE KEYS */;
INSERT INTO `classe` VALUES (1,'Classe 1','Comptes de ressources durables'),(2,'Classe 2','Comptes d\'actif immobilisé'),(3,'Classe 3','Comptes de stocks'),(4,'Classe 4','Comptes de tiers'),(5,'Classe 5','Comptes de trésorerie'),(6,'Classe 6','Comptes de charges des activités ordinaires'),(7,'Classe 7','Comptes de produits des activités ordinaires'),(8,'Classe 8','Comptes de ressources durables'),(9,'Classe 9','Comptes des engagements hors bilan et comptabilité analytique de gestion');
/*!40000 ALTER TABLE `classe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classe_comptes`
--

DROP TABLE IF EXISTS `classe_comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classe_comptes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classe_comptes`
--

LOCK TABLES `classe_comptes` WRITE;
/*!40000 ALTER TABLE `classe_comptes` DISABLE KEYS */;
INSERT INTO `classe_comptes` VALUES (1,'Classe 1','Comptes de ressources durables'),(2,'Classe 2','Comptes d\'actif immobilisé'),(3,'Classe 3','Comptes de stocks'),(4,'Classe 4','Comptes de tiers'),(5,'Classe 5','Comptes de trésorerie'),(6,'Classe 6','Comptes de charges des activités ordinaires'),(7,'Classe 7','Comptes de produits des activités ordinaires'),(8,'Classe 8','Comptes de ressources durables'),(9,'Classe 9','Comptes des engagements hors bilan et comptabilité analytique de gestion');
/*!40000 ALTER TABLE `classe_comptes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comptes`
--

DROP TABLE IF EXISTS `comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comptes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_compte` varchar(10) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `id_classe` int NOT NULL,
  `classe_id` int DEFAULT NULL,
  `statut` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_compte` (`numero_compte`),
  KEY `id_classe` (`id_classe`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comptes`
--

LOCK TABLES `comptes` WRITE;
/*!40000 ALTER TABLE `comptes` DISABLE KEYS */;
INSERT INTO `comptes` VALUES (13,'101','Capital social',1,1,'passif'),(14,'102','Capital par dotation',1,1,'passif'),(15,'103','Capital personnel',1,1,'passif'),(16,'104','Compte de l\'exploitant',1,1,'passif'),(17,'105','Primes liées aux capitaux propres',1,1,'passif'),(18,'106','Ecarts de réévaluation',1,1,'passif'),(19,'109','Apporteurs, capital souscrit, non appelé',1,1,'passif'),(20,'11','Réserves',1,1,'passif'),(21,'12','Report à nouveau',1,1,'passif'),(22,'13','Résultat net de l\'exercice',1,1,'passif'),(23,'14','Subventions d\'investissement',1,1,'passif'),(24,'15','Provisions réglementées et fonds assimilés',1,1,'passif'),(25,'16','Emprunts et dettes assimilées',1,1,'passif'),(26,'17','Dettes de location acquisition',1,1,'passif'),(27,'18','Dettes liées à des participations',1,1,'passif'),(28,'19','Provisions pour risques et charges',1,1,'passif'),(29,'21','Immobilisations incorporelles',0,2,'actif'),(30,'22','Terrains',0,2,'actif'),(31,'23','Bâtiments, installations techniques et agencements',0,2,'actif'),(32,'24','Matériel, Mobilier et Actifs biologiques',0,2,'actif'),(33,'25','Avances et acomptes versés sur immobilisations',0,2,'actif'),(34,'26','Titres de participation',0,2,'actif'),(35,'27','Autres immobilisations financières',0,2,'actif'),(36,'28','Amortissements',0,2,'actif'),(37,'29','Dépréciations',0,2,'actif'),(38,'31','Marchandises',0,3,'actif'),(39,'32','Matières premières et fournitures liées',0,3,'actif'),(40,'33','Autres approvisionnements',0,3,'actif'),(41,'34','Produits en cours',0,3,'actif'),(42,'35','Services en cours',0,3,'actif'),(43,'36','Produits finis',0,3,'actif'),(44,'37','Produits intermédiaires et résiduels',0,3,'actif'),(45,'38','Stocks en cours de route',0,3,'actif'),(46,'39','Dépréciations des stocks',0,3,'actif'),(47,'40','Fournisseurs et comptes rattachés',0,4,'passif'),(48,'41','Clients et comptes rattachés',0,4,'actif'),(49,'42','Personnel',0,4,'actif'),(50,'43','Organismes sociaux',0,4,'passif'),(51,'44','Etat et Collectivités publiques',0,4,'passif'),(52,'45','Organismes internationaux',0,4,'passif'),(53,'46','Apporteurs, Associés et Groupe',0,4,'passif'),(54,'47','Débiteurs et créditeurs divers',0,4,'passif'),(55,'48','Créances et dettes hors activités ordinaires',0,4,'passif'),(56,'49','Dépréciations et provisions pour risques à court terme (Tiers)',0,4,'passif'),(57,'50','Titres de placement',0,5,'actif'),(58,'51','Valeurs à encaisser',0,5,'actif'),(59,'52','Banques',0,5,'actif'),(60,'53','Etablissements financiers et assimilés',0,5,'actif'),(61,'54','Instruments de trésorerie',0,5,'actif'),(62,'55','Instruments de monnaie électronique',0,5,'actif'),(63,'56','Banques, crédits de trésorerie et d\'escompte',0,5,'actif'),(64,'57','Caisse',0,5,'actif'),(65,'58','Régies d\'avances',0,5,'actif'),(66,'59','Dépréciations et provisions pour risques à court terme (Trésorerie)',0,5,'actif'),(67,'60','Achats',0,6,'passif'),(68,'603','Variations des stocks de biens achetés',0,6,'passif'),(69,'61','Transports',0,6,'passif'),(70,'62','Services extérieurs',0,6,'passif'),(71,'63','Autres services extérieurs',0,6,'passif'),(72,'64','Impôts et taxes',0,6,'passif'),(73,'65','Autres charges',0,6,'passif'),(74,'659','Charges pour dépréciations et provisions pour risques a court',0,6,'passif'),(75,'66','Charges de personnel',0,6,'passif'),(76,'67','Frais financiers et charges assimilées',0,6,'passif'),(77,'68','Dotations aux amortissements',0,6,'passif'),(78,'69','Dotations aux provisions et aux dépréciations',0,6,'passif'),(79,'70','Ventes',0,7,'actif'),(80,'71','Subventions d\'exploitation',0,7,'actif'),(81,'72','Production immobilisée',0,7,'actif'),(82,'73','Variations des stocks de biens et services produits',0,7,'actif'),(83,'75','Autres produits',0,7,'actif'),(84,'759','Reprises de charges pour dépréciations',0,7,'actif'),(85,'77','Revenus financiers et produits assimilés',0,7,'actif'),(86,'78','Transferts de charges',0,7,'passif'),(87,'79','Reprises de provisions, dépréciations et autres',0,7,'actif'),(88,'81','Valeurs comptables des cessions d\'immobilisations',0,8,'actif'),(89,'9011','Crédits confirmés obtenus',0,9,'actif'),(90,'9012','Emprunts restant à encaisser',0,9,'actif'),(91,'9013','Facilités de financement renouvelables',0,9,'actif'),(92,'9014','Facilités d\'émission',0,9,'actif'),(93,'9018','Autres engagements de financement obtenus',0,9,'actif'),(94,'9021','Avals obtenus',0,9,'actif'),(95,'9022','Cautions, garanties obtenues',0,9,'actif'),(96,'9023','Hypothèques obtenues',0,9,'actif'),(97,'9024','Effets endossés par des tiers',0,9,'actif'),(98,'9028','Autres garanties obtenues',0,9,'actif'),(99,'9031','Achats de marchandises à terme',0,9,'passif'),(100,'9032','Achats à terme de devises',0,9,'passif'),(101,'9033','Commandes fermes des clients',0,9,'passif'),(102,'82','Produits des cessions d\'immobilisations',0,8,'passif'),(103,'83','Charges hors activités ordinaires',0,8,'passif'),(104,'84','Produits hors activités ordinaires',0,8,'actif'),(105,'85','Dotations hors activités ordinaires',0,8,'passif'),(106,'86','Reprises hors activités ordinaires',0,8,'actif'),(107,'87','Participation des travailleurs',0,8,'passif'),(108,'88','Subventions d\'équilibre',0,8,'actif'),(109,'89','Impôts sur le résultat',0,8,'passif');
/*!40000 ALTER TABLE `comptes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-01  1:55:42
