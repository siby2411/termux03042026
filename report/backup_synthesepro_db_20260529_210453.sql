/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: synthesepro_db
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ACTIONS_PREFERENCE`
--

DROP TABLE IF EXISTS `ACTIONS_PREFERENCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ACTIONS_PREFERENCE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_emission` date NOT NULL,
  `nb_actions` int(11) NOT NULL,
  `valeur_nominale` decimal(15,2) NOT NULL,
  `droits_particuliers` text DEFAULT NULL,
  `taux_dividende_preferentiel` decimal(5,2) DEFAULT NULL,
  `type` enum('DIVIDENDE_PRIORITAIRE','VOTE_DOUBLE','RACHAT_PRIORITAIRE') NOT NULL,
  `statut` enum('ACTIF','RACHE','CONVERTI') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ACTIONS_PREFERENCE`
--

LOCK TABLES `ACTIONS_PREFERENCE` WRITE;
/*!40000 ALTER TABLE `ACTIONS_PREFERENCE` DISABLE KEYS */;
/*!40000 ALTER TABLE `ACTIONS_PREFERENCE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ACTIVITES_ABC`
--

DROP TABLE IF EXISTS `ACTIVITES_ABC`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ACTIVITES_ABC` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `inducteur` varchar(50) NOT NULL,
  `cout_total` decimal(15,2) DEFAULT NULL,
  `cout_unitaire_inducteur` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ACTIVITES_ABC`
--

LOCK TABLES `ACTIVITES_ABC` WRITE;
/*!40000 ALTER TABLE `ACTIVITES_ABC` DISABLE KEYS */;
INSERT INTO `ACTIVITES_ABC` VALUES
(1,'ACT-APPRO','Approvisonnement','Nombre de commandes',NULL,NULL,'2026-05-16 19:03:35'),
(2,'ACT-PROD','Production','Heures machine',NULL,NULL,'2026-05-16 19:03:35'),
(3,'ACT-CONTROLE','Contrôle qualité','Nombre de contrôles',NULL,NULL,'2026-05-16 19:03:35'),
(4,'ACT-LIVRAISON','Livraison','Nombre de livraisons',NULL,NULL,'2026-05-16 19:03:35');
/*!40000 ALTER TABLE `ACTIVITES_ABC` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AFFECTATIONS_RESULTAT`
--

DROP TABLE IF EXISTS `AFFECTATIONS_RESULTAT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `AFFECTATIONS_RESULTAT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `date_proposition` date NOT NULL,
  `statut` enum('PROPOSEE','APPROUVEE','REJETEE') DEFAULT 'PROPOSEE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AFFECTATIONS_RESULTAT`
--

LOCK TABLES `AFFECTATIONS_RESULTAT` WRITE;
/*!40000 ALTER TABLE `AFFECTATIONS_RESULTAT` DISABLE KEYS */;
/*!40000 ALTER TABLE `AFFECTATIONS_RESULTAT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ALERTES_ECHEANCES`
--

DROP TABLE IF EXISTS `ALERTES_ECHEANCES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ALERTES_ECHEANCES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `echeance_id` int(11) NOT NULL,
  `type_alerte` enum('SMS','EMAIL','WHATSAPP','NOTIFICATION') DEFAULT 'NOTIFICATION',
  `destinataire` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` datetime DEFAULT NULL,
  `statut` enum('EN_ATTENTE','ENVOYE','ECHEC') DEFAULT 'EN_ATTENTE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `echeance_id` (`echeance_id`),
  CONSTRAINT `ALERTES_ECHEANCES_ibfk_1` FOREIGN KEY (`echeance_id`) REFERENCES `ECHEANCIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ALERTES_ECHEANCES`
--

LOCK TABLES `ALERTES_ECHEANCES` WRITE;
/*!40000 ALTER TABLE `ALERTES_ECHEANCES` DISABLE KEYS */;
/*!40000 ALTER TABLE `ALERTES_ECHEANCES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AMORTISSEMENTS`
--

DROP TABLE IF EXISTS `AMORTISSEMENTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `AMORTISSEMENTS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `immobilisation_id` int(11) NOT NULL,
  `compte_immobilisation` int(11) NOT NULL,
  `compte_amortissement` int(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `date_acquisition` date NOT NULL,
  `valeur_originale` decimal(15,2) NOT NULL,
  `valeur_residuelle` decimal(15,2) DEFAULT 0.00,
  `duree_ans` int(11) NOT NULL,
  `type_amort` enum('LINEAIRE','DECROISSANT','DEROGATOIRE') DEFAULT 'LINEAIRE',
  `taux` decimal(5,2) NOT NULL,
  `amortissement_cumule` decimal(15,2) DEFAULT 0.00,
  `exercice_en_cours` int(11) DEFAULT NULL,
  `statut` enum('ACTIF','AMORTI','CESSED') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_amortissements_immo` (`immobilisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AMORTISSEMENTS`
--

LOCK TABLES `AMORTISSEMENTS` WRITE;
/*!40000 ALTER TABLE `AMORTISSEMENTS` DISABLE KEYS */;
/*!40000 ALTER TABLE `AMORTISSEMENTS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ANALYSES_FINANCIERES`
--

DROP TABLE IF EXISTS `ANALYSES_FINANCIERES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ANALYSES_FINANCIERES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `type_analyse` enum('BILAN','CR','SIG','RATIOS') NOT NULL,
  `indicateur` varchar(100) NOT NULL,
  `valeur` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ANALYSES_FINANCIERES`
--

LOCK TABLES `ANALYSES_FINANCIERES` WRITE;
/*!40000 ALTER TABLE `ANALYSES_FINANCIERES` DISABLE KEYS */;
/*!40000 ALTER TABLE `ANALYSES_FINANCIERES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ANALYSE_COUTS`
--

DROP TABLE IF EXISTS `ANALYSE_COUTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ANALYSE_COUTS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `cout_fixe` decimal(15,2) DEFAULT 0.00,
  `cout_variable_unitaire` decimal(15,2) DEFAULT 0.00,
  `prix_vente_unitaire` decimal(15,2) DEFAULT 0.00,
  `quantite_vendue` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ANALYSE_COUTS`
--

LOCK TABLES `ANALYSE_COUTS` WRITE;
/*!40000 ALTER TABLE `ANALYSE_COUTS` DISABLE KEYS */;
/*!40000 ALTER TABLE `ANALYSE_COUTS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ARBRES_DECISION`
--

DROP TABLE IF EXISTS `ARBRES_DECISION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ARBRES_DECISION` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_id` int(11) NOT NULL,
  `noeud` varchar(100) NOT NULL,
  `type_noeud` enum('DECISION','ALEATOIRE','TERMINAL') NOT NULL,
  `probabilite` decimal(5,2) DEFAULT NULL,
  `valeur` decimal(15,2) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projet_id` (`projet_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `ARBRES_DECISION_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `PROJETS_INVESTISSEMENT` (`id`),
  CONSTRAINT `ARBRES_DECISION_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `ARBRES_DECISION` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ARBRES_DECISION`
--

LOCK TABLES `ARBRES_DECISION` WRITE;
/*!40000 ALTER TABLE `ARBRES_DECISION` DISABLE KEYS */;
/*!40000 ALTER TABLE `ARBRES_DECISION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ARTICLES_STOCK`
--

DROP TABLE IF EXISTS `ARTICLES_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ARTICLES_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_article` varchar(50) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `compte_stock` int(11) NOT NULL,
  `compte_charge` int(11) NOT NULL,
  `unite` varchar(20) DEFAULT 'Pièce',
  `stock_minimum` int(11) DEFAULT 0,
  `stock_maximum` int(11) DEFAULT 0,
  `stock_actuel` int(11) DEFAULT 0,
  `valeur_stock_actuel` decimal(15,2) DEFAULT 0.00,
  `methode_valorisation` enum('CUMP','PEPS','FIFO') DEFAULT 'CUMP',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `prix_unitaire` decimal(15,2) DEFAULT 0.00,
  `stock_initial` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_article` (`code_article`),
  KEY `compte_stock` (`compte_stock`),
  KEY `compte_charge` (`compte_charge`),
  CONSTRAINT `ARTICLES_STOCK_ibfk_1` FOREIGN KEY (`compte_stock`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `ARTICLES_STOCK_ibfk_2` FOREIGN KEY (`compte_charge`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ARTICLES_STOCK`
--

LOCK TABLES `ARTICLES_STOCK` WRITE;
/*!40000 ALTER TABLE `ARTICLES_STOCK` DISABLE KEYS */;
INSERT INTO `ARTICLES_STOCK` VALUES
(1,'B001','Bois massif (lot de 10m³)',31,601,'Lot',0,0,0,0.00,'CUMP','2026-05-23 20:38:10',25000.00,50),
(2,'V001','Vernis (pot 5L)',31,601,'Pot',0,0,0,0.00,'CUMP','2026-05-23 20:38:10',5000.00,100),
(3,'Q001','Quincaillerie (lot de 100 pièces)',31,601,'Lot',0,0,0,0.00,'CUMP','2026-05-23 20:38:10',15000.00,80);
/*!40000 ALTER TABLE `ARTICLES_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AUDIT_ECRITURES`
--

DROP TABLE IF EXISTS `AUDIT_ECRITURES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `AUDIT_ECRITURES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecriture_id` int(11) NOT NULL,
  `date_audit` date NOT NULL,
  `auditeur` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` enum('VALIDE','REJETE','A_CORRIGER') DEFAULT 'A_CORRIGER',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ecriture_id` (`ecriture_id`),
  CONSTRAINT `AUDIT_ECRITURES_ibfk_1` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AUDIT_ECRITURES`
--

LOCK TABLES `AUDIT_ECRITURES` WRITE;
/*!40000 ALTER TABLE `AUDIT_ECRITURES` DISABLE KEYS */;
/*!40000 ALTER TABLE `AUDIT_ECRITURES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AUDIT_TRAIL`
--

DROP TABLE IF EXISTS `AUDIT_TRAIL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `AUDIT_TRAIL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT','VALIDATE') NOT NULL,
  `table_concernee` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `anciennes_valeurs` text DEFAULT NULL,
  `nouvelles_valeurs` text DEFAULT NULL,
  `ip_adresse` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `date_action` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_date` (`date_action`),
  KEY `idx_audit_utilisateur` (`utilisateur_id`),
  CONSTRAINT `AUDIT_TRAIL_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `USERS` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AUDIT_TRAIL`
--

LOCK TABLES `AUDIT_TRAIL` WRITE;
/*!40000 ALTER TABLE `AUDIT_TRAIL` DISABLE KEYS */;
/*!40000 ALTER TABLE `AUDIT_TRAIL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BALANCED_SCORECARD`
--

DROP TABLE IF EXISTS `BALANCED_SCORECARD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BALANCED_SCORECARD` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `perspective` enum('FINANCIERE','CLIENT','PROCESSUS_INTERNES','APPRENTISSAGE') NOT NULL,
  `objectif` varchar(255) DEFAULT NULL,
  `indicateur` varchar(100) DEFAULT NULL,
  `cible` decimal(15,2) DEFAULT NULL,
  `realise` decimal(15,2) DEFAULT NULL,
  `poids` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BALANCED_SCORECARD`
--

LOCK TABLES `BALANCED_SCORECARD` WRITE;
/*!40000 ALTER TABLE `BALANCED_SCORECARD` DISABLE KEYS */;
/*!40000 ALTER TABLE `BALANCED_SCORECARD` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BFR_CALCUL`
--

DROP TABLE IF EXISTS `BFR_CALCUL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BFR_CALCUL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `date_calcul` date NOT NULL,
  `actif_circulant` decimal(15,2) NOT NULL,
  `passif_circulant` decimal(15,2) NOT NULL,
  `bfr` decimal(15,2) GENERATED ALWAYS AS (`actif_circulant` - `passif_circulant`) STORED,
  `tresorerie_nette` decimal(15,2) DEFAULT NULL,
  `fonds_roulement` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bfr_exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BFR_CALCUL`
--

LOCK TABLES `BFR_CALCUL` WRITE;
/*!40000 ALTER TABLE `BFR_CALCUL` DISABLE KEYS */;
/*!40000 ALTER TABLE `BFR_CALCUL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BILANS_PREVISIONNELS`
--

DROP TABLE IF EXISTS `BILANS_PREVISIONNELS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BILANS_PREVISIONNELS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `poste` varchar(100) NOT NULL,
  `montant_prevu` decimal(15,2) NOT NULL,
  `montant_reel` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BILANS_PREVISIONNELS`
--

LOCK TABLES `BILANS_PREVISIONNELS` WRITE;
/*!40000 ALTER TABLE `BILANS_PREVISIONNELS` DISABLE KEYS */;
/*!40000 ALTER TABLE `BILANS_PREVISIONNELS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BUDGETS`
--

DROP TABLE IF EXISTS `BUDGETS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BUDGETS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `type_budget` enum('VENTES','ACHATS','CHARGES','TRESORERIE') NOT NULL,
  `montant_prevu` decimal(15,2) NOT NULL,
  `montant_reel` decimal(15,2) DEFAULT 0.00,
  `ecart` decimal(15,2) GENERATED ALWAYS AS (`montant_reel` - `montant_prevu`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_budget` (`exercice`,`mois`,`type_budget`),
  KEY `idx_budgets_exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BUDGETS`
--

LOCK TABLES `BUDGETS` WRITE;
/*!40000 ALTER TABLE `BUDGETS` DISABLE KEYS */;
/*!40000 ALTER TABLE `BUDGETS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BUDGETS_ANALYTIQUE`
--

DROP TABLE IF EXISTS `BUDGETS_ANALYTIQUE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BUDGETS_ANALYTIQUE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `type_budget` enum('PREVISIONNEL','REVISE','REALISE') DEFAULT 'PREVISIONNEL',
  `mois` int(11) NOT NULL,
  `montant_ventes` decimal(15,2) DEFAULT 0.00,
  `montant_achats` decimal(15,2) DEFAULT 0.00,
  `montant_charges` decimal(15,2) DEFAULT 0.00,
  `montant_investissement` decimal(15,2) DEFAULT 0.00,
  `taux_absorption` decimal(5,2) GENERATED ALWAYS AS (case when `montant_ventes` > 0 then (`montant_achats` + `montant_charges`) / `montant_ventes` * 100 else 0 end) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_budget` (`exercice`,`section_id`,`mois`),
  KEY `section_id` (`section_id`),
  CONSTRAINT `BUDGETS_ANALYTIQUE_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `SECTIONS_ANALYTIQUES` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BUDGETS_ANALYTIQUE`
--

LOCK TABLES `BUDGETS_ANALYTIQUE` WRITE;
/*!40000 ALTER TABLE `BUDGETS_ANALYTIQUE` DISABLE KEYS */;
INSERT INTO `BUDGETS_ANALYTIQUE` VALUES
(1,2026,11,'PREVISIONNEL',2,2000000.00,1000000.00,500000.00,0.00,75.00,'2026-05-11 19:11:44','2026-05-11 19:11:44'),
(2,2026,11,'PREVISIONNEL',3,2500000.00,1200000.00,600000.00,0.00,72.00,'2026-05-11 19:11:44','2026-05-11 19:11:44'),
(3,2026,12,'PREVISIONNEL',2,1500000.00,800000.00,400000.00,0.00,80.00,'2026-05-11 19:11:44','2026-05-11 19:11:44');
/*!40000 ALTER TABLE `BUDGETS_ANALYTIQUE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BUDGETS_PREVISIONNELS`
--

DROP TABLE IF EXISTS `BUDGETS_PREVISIONNELS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BUDGETS_PREVISIONNELS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `type_budget` enum('VENTES','PRODUCTION','ACHATS','CHARGES_PERSONNEL','CHARGES_GENERALES','INVESTISSEMENTS') NOT NULL,
  `montant_prevu` decimal(15,2) NOT NULL,
  `montant_reel` decimal(15,2) DEFAULT 0.00,
  `ecart` decimal(15,2) GENERATED ALWAYS AS (`montant_reel` - `montant_prevu`) STORED,
  `taux_realisation` decimal(5,2) GENERATED ALWAYS AS (if(`montant_prevu` > 0,`montant_reel` / `montant_prevu` * 100,0)) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_budget_mensuel` (`exercice`,`mois`,`type_budget`),
  KEY `idx_budgets_exercice` (`exercice`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BUDGETS_PREVISIONNELS`
--

LOCK TABLES `BUDGETS_PREVISIONNELS` WRITE;
/*!40000 ALTER TABLE `BUDGETS_PREVISIONNELS` DISABLE KEYS */;
INSERT INTO `BUDGETS_PREVISIONNELS` VALUES
(1,2026,1,'Ventes meubles salon','VENTES',5000000.00,4800000.00,-200000.00,96.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(2,2026,2,'Ventes meubles salon','VENTES',5000000.00,5200000.00,200000.00,104.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(3,2026,3,'Ventes meubles salon','VENTES',6000000.00,5500000.00,-500000.00,91.67,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(4,2026,4,'Ventes meubles salon','VENTES',6000000.00,6200000.00,200000.00,103.33,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(5,2026,5,'Ventes meubles salon','VENTES',7000000.00,6800000.00,-200000.00,97.14,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(6,2026,6,'Ventes meubles salon','VENTES',7000000.00,7500000.00,500000.00,107.14,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(7,2026,1,'Achat bois/matériaux','ACHATS',3000000.00,3200000.00,200000.00,106.67,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(8,2026,2,'Achat bois/matériaux','ACHATS',3000000.00,2900000.00,-100000.00,96.67,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(9,2026,3,'Achat bois/matériaux','ACHATS',3500000.00,3800000.00,300000.00,108.57,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(10,2026,4,'Achat bois/matériaux','ACHATS',3500000.00,3400000.00,-100000.00,97.14,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(11,2026,5,'Achat bois/matériaux','ACHATS',4000000.00,4200000.00,200000.00,105.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(12,2026,6,'Achat bois/matériaux','ACHATS',4000000.00,3900000.00,-100000.00,97.50,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(13,2026,1,'Salaires atelier','CHARGES_PERSONNEL',2500000.00,2600000.00,100000.00,104.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(14,2026,2,'Salaires atelier','CHARGES_PERSONNEL',2500000.00,2500000.00,0.00,100.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(15,2026,3,'Salaires atelier','CHARGES_PERSONNEL',2500000.00,2700000.00,200000.00,108.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(16,2026,4,'Salaires atelier','CHARGES_PERSONNEL',2500000.00,2500000.00,0.00,100.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(17,2026,5,'Salaires atelier','CHARGES_PERSONNEL',3000000.00,3100000.00,100000.00,103.33,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(18,2026,6,'Salaires atelier','CHARGES_PERSONNEL',3000000.00,2950000.00,-50000.00,98.33,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(19,2026,1,'Électricité, eau','CHARGES_GENERALES',500000.00,550000.00,50000.00,110.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(20,2026,2,'Électricité, eau','CHARGES_GENERALES',500000.00,480000.00,-20000.00,96.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(21,2026,3,'Électricité, eau','CHARGES_GENERALES',500000.00,600000.00,100000.00,120.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(22,2026,4,'Électricité, eau','CHARGES_GENERALES',500000.00,520000.00,20000.00,104.00,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(23,2026,5,'Électricité, eau','CHARGES_GENERALES',600000.00,580000.00,-20000.00,96.67,'2026-05-07 18:56:27','2026-05-07 18:56:27'),
(24,2026,6,'Électricité, eau','CHARGES_GENERALES',600000.00,650000.00,50000.00,108.33,'2026-05-07 18:56:27','2026-05-07 18:56:27');
/*!40000 ALTER TABLE `BUDGETS_PREVISIONNELS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BUDGETS_TRESORERIE`
--

DROP TABLE IF EXISTS `BUDGETS_TRESORERIE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BUDGETS_TRESORERIE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `encaissements_previs` decimal(15,2) DEFAULT NULL,
  `encaissements_reels` decimal(15,2) DEFAULT NULL,
  `decaissements_previs` decimal(15,2) DEFAULT NULL,
  `decaissements_reels` decimal(15,2) DEFAULT NULL,
  `solde_debut` decimal(15,2) DEFAULT NULL,
  `solde_fin` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_budget` (`exercice`,`mois`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BUDGETS_TRESORERIE`
--

LOCK TABLES `BUDGETS_TRESORERIE` WRITE;
/*!40000 ALTER TABLE `BUDGETS_TRESORERIE` DISABLE KEYS */;
/*!40000 ALTER TABLE `BUDGETS_TRESORERIE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `BULLETINS_SALAIRE`
--

DROP TABLE IF EXISTS `BULLETINS_SALAIRE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `BULLETINS_SALAIRE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salarie_id` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `salaire_base` decimal(15,2) NOT NULL,
  `primes` decimal(15,2) DEFAULT 0.00,
  `heures_supplementaires` decimal(15,2) DEFAULT 0.00,
  `avantages_nature` decimal(15,2) DEFAULT 0.00,
  `total_brut` decimal(15,2) GENERATED ALWAYS AS (`salaire_base` + `primes` + `heures_supplementaires` + `avantages_nature`) STORED,
  `cnss_employe` decimal(15,2) DEFAULT NULL,
  `ipres_employe` decimal(15,2) DEFAULT NULL,
  `css_employe` decimal(15,2) DEFAULT NULL,
  `irpp` decimal(15,2) DEFAULT NULL,
  `autres_retenues` decimal(15,2) DEFAULT 0.00,
  `total_retenues` decimal(15,2) GENERATED ALWAYS AS (coalesce(`cnss_employe`,0) + coalesce(`ipres_employe`,0) + coalesce(`css_employe`,0) + coalesce(`irpp`,0) + `autres_retenues`) STORED,
  `net_a_payer` decimal(15,2) GENERATED ALWAYS AS (`total_brut` - `total_retenues`) STORED,
  `cnss_patronal` decimal(15,2) DEFAULT NULL,
  `ipres_patronal` decimal(15,2) DEFAULT NULL,
  `css_patronal` decimal(15,2) DEFAULT NULL,
  `total_charges_patronales` decimal(15,2) GENERATED ALWAYS AS (coalesce(`cnss_patronal`,0) + coalesce(`ipres_patronal`,0) + coalesce(`css_patronal`,0)) STORED,
  `ecriture_comptable_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `salarie_id` (`salarie_id`),
  KEY `ecriture_comptable_id` (`ecriture_comptable_id`),
  KEY `idx_bulletins_periode` (`annee`,`mois`),
  CONSTRAINT `BULLETINS_SALAIRE_ibfk_1` FOREIGN KEY (`salarie_id`) REFERENCES `SALARIES` (`id`),
  CONSTRAINT `BULLETINS_SALAIRE_ibfk_2` FOREIGN KEY (`ecriture_comptable_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `BULLETINS_SALAIRE`
--

LOCK TABLES `BULLETINS_SALAIRE` WRITE;
/*!40000 ALTER TABLE `BULLETINS_SALAIRE` DISABLE KEYS */;
/*!40000 ALTER TABLE `BULLETINS_SALAIRE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CAF_CALCUL`
--

DROP TABLE IF EXISTS `CAF_CALCUL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CAF_CALCUL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `date_calcul` date NOT NULL,
  `resultat_net` decimal(15,2) NOT NULL,
  `dotations_amortissements` decimal(15,2) DEFAULT 0.00,
  `provisions` decimal(15,2) DEFAULT 0.00,
  `plus_values_cession` decimal(15,2) DEFAULT 0.00,
  `moins_values_cession` decimal(15,2) DEFAULT 0.00,
  `caf` decimal(15,2) GENERATED ALWAYS AS (`resultat_net` + `dotations_amortissements` + `provisions` - `plus_values_cession` + `moins_values_cession`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_caf_exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CAF_CALCUL`
--

LOCK TABLES `CAF_CALCUL` WRITE;
/*!40000 ALTER TABLE `CAF_CALCUL` DISABLE KEYS */;
/*!40000 ALTER TABLE `CAF_CALCUL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CALCULS_DPS`
--

DROP TABLE IF EXISTS `CALCULS_DPS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CALCULS_DPS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_calcul` date NOT NULL,
  `cours_avant` decimal(15,2) NOT NULL,
  `prix_emission` decimal(15,2) NOT NULL,
  `nb_actions_anciennes` int(11) NOT NULL,
  `valeur_dps` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CALCULS_DPS`
--

LOCK TABLES `CALCULS_DPS` WRITE;
/*!40000 ALTER TABLE `CALCULS_DPS` DISABLE KEYS */;
/*!40000 ALTER TABLE `CALCULS_DPS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CALCULS_VAN_TRI`
--

DROP TABLE IF EXISTS `CALCULS_VAN_TRI`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CALCULS_VAN_TRI` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_nom` varchar(255) NOT NULL,
  `investissement_initial` decimal(15,2) NOT NULL,
  `duree_vie` int(11) NOT NULL,
  `taux_actualisation` decimal(5,2) NOT NULL,
  `van_calculee` decimal(15,2) DEFAULT NULL,
  `tri_calcule` decimal(5,2) DEFAULT NULL,
  `indice_rentabilite` decimal(5,2) DEFAULT NULL,
  `date_calcul` date NOT NULL,
  `details_calcul` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `projet_nom` (`projet_nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CALCULS_VAN_TRI`
--

LOCK TABLES `CALCULS_VAN_TRI` WRITE;
/*!40000 ALTER TABLE `CALCULS_VAN_TRI` DISABLE KEYS */;
/*!40000 ALTER TABLE `CALCULS_VAN_TRI` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CENTRES_ANALYSE`
--

DROP TABLE IF EXISTS `CENTRES_ANALYSE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CENTRES_ANALYSE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `type_centre` enum('PRINCIPAL','AUXILIAIRE') NOT NULL,
  `unite_oeuvre` varchar(50) DEFAULT NULL,
  `cout_unitaire_oeuvre` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CENTRES_ANALYSE`
--

LOCK TABLES `CENTRES_ANALYSE` WRITE;
/*!40000 ALTER TABLE `CENTRES_ANALYSE` DISABLE KEYS */;
INSERT INTO `CENTRES_ANALYSE` VALUES
(1,'APPRO','Approvisonnement','AUXILIAIRE','Kg acheté',NULL,'2026-05-16 19:03:34'),
(2,'PROD','Production','PRINCIPAL','Heure machine',NULL,'2026-05-16 19:03:34'),
(3,'DIST','Distribution','PRINCIPAL','Kg vendu',NULL,'2026-05-16 19:03:34'),
(4,'ADMIN','Administration','AUXILIAIRE','Coût total',NULL,'2026-05-16 19:03:34');
/*!40000 ALTER TABLE `CENTRES_ANALYSE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CENTRES_ANALYTIQUES`
--

DROP TABLE IF EXISTS `CENTRES_ANALYTIQUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CENTRES_ANALYTIQUES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `type_centre` enum('COUT','PROFIT','MIXTE') NOT NULL,
  `centre_parent` int(11) DEFAULT NULL,
  `actif` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `centre_parent` (`centre_parent`),
  CONSTRAINT `CENTRES_ANALYTIQUES_ibfk_1` FOREIGN KEY (`centre_parent`) REFERENCES `CENTRES_ANALYTIQUES` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CENTRES_ANALYTIQUES`
--

LOCK TABLES `CENTRES_ANALYTIQUES` WRITE;
/*!40000 ALTER TABLE `CENTRES_ANALYTIQUES` DISABLE KEYS */;
INSERT INTO `CENTRES_ANALYTIQUES` VALUES
(1,'ADM','Administration Générale','COUT',NULL,1,'2026-05-17 22:43:01'),
(2,'PROD','Production','PROFIT',NULL,1,'2026-05-17 22:43:01'),
(3,'COMM','Commercial','PROFIT',NULL,1,'2026-05-17 22:43:01');
/*!40000 ALTER TABLE `CENTRES_ANALYTIQUES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CERTIFICATS_INVESTISSEMENT`
--

DROP TABLE IF EXISTS `CERTIFICATS_INVESTISSEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CERTIFICATS_INVESTISSEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_emission` date NOT NULL,
  `nb_certificats` int(11) NOT NULL,
  `valeur_nominale` decimal(15,2) NOT NULL,
  `droit_vote` enum('OUI','NON','LIMITE') DEFAULT 'NON',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CERTIFICATS_INVESTISSEMENT`
--

LOCK TABLES `CERTIFICATS_INVESTISSEMENT` WRITE;
/*!40000 ALTER TABLE `CERTIFICATS_INVESTISSEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `CERTIFICATS_INVESTISSEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CHARGES_INDIRECTES`
--

DROP TABLE IF EXISTS `CHARGES_INDIRECTES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CHARGES_INDIRECTES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centre_id` int(11) DEFAULT NULL,
  `activite_id` int(11) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `nature` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `centre_id` (`centre_id`),
  KEY `activite_id` (`activite_id`),
  CONSTRAINT `CHARGES_INDIRECTES_ibfk_1` FOREIGN KEY (`centre_id`) REFERENCES `CENTRES_ANALYSE` (`id`),
  CONSTRAINT `CHARGES_INDIRECTES_ibfk_2` FOREIGN KEY (`activite_id`) REFERENCES `ACTIVITES_ABC` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CHARGES_INDIRECTES`
--

LOCK TABLES `CHARGES_INDIRECTES` WRITE;
/*!40000 ALTER TABLE `CHARGES_INDIRECTES` DISABLE KEYS */;
INSERT INTO `CHARGES_INDIRECTES` VALUES
(1,1,NULL,150000.00,'Frais d\'approvisionnement','2026-05-16 19:03:35'),
(2,2,NULL,500000.00,'Frais de production','2026-05-16 19:03:35'),
(3,3,NULL,200000.00,'Frais de distribution','2026-05-16 19:03:35'),
(4,4,NULL,150000.00,'Frais administratifs','2026-05-16 19:03:35'),
(5,NULL,1,150000.00,'Frais approvisionnement','2026-05-16 19:03:35'),
(6,NULL,2,500000.00,'Frais production','2026-05-16 19:03:35'),
(7,NULL,3,100000.00,'Frais contrôle','2026-05-16 19:03:35'),
(8,NULL,4,100000.00,'Frais livraison','2026-05-16 19:03:35');
/*!40000 ALTER TABLE `CHARGES_INDIRECTES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CLES_REPARTITION`
--

DROP TABLE IF EXISTS `CLES_REPARTITION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CLES_REPARTITION` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centre_source` varchar(20) NOT NULL,
  `centre_destination` varchar(20) NOT NULL,
  `pourcentage` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CLES_REPARTITION`
--

LOCK TABLES `CLES_REPARTITION` WRITE;
/*!40000 ALTER TABLE `CLES_REPARTITION` DISABLE KEYS */;
INSERT INTO `CLES_REPARTITION` VALUES
(1,'ADMIN','APPRO',30.00,'2026-05-16 19:03:35'),
(2,'ADMIN','PROD',40.00,'2026-05-16 19:03:35'),
(3,'ADMIN','DIST',30.00,'2026-05-16 19:03:35');
/*!40000 ALTER TABLE `CLES_REPARTITION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CLOTURE_EXERCICE`
--

DROP TABLE IF EXISTS `CLOTURE_EXERCICE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CLOTURE_EXERCICE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `benefice_net` decimal(15,2) DEFAULT NULL,
  `affectation` text DEFAULT NULL,
  `statut` enum('OUVERT','EN_COURS','CLOS','APPROUVE') DEFAULT 'OUVERT',
  `date_cloture` date DEFAULT NULL,
  `date_approbation` date DEFAULT NULL,
  `valide_par` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CLOTURE_EXERCICE`
--

LOCK TABLES `CLOTURE_EXERCICE` WRITE;
/*!40000 ALTER TABLE `CLOTURE_EXERCICE` DISABLE KEYS */;
/*!40000 ALTER TABLE `CLOTURE_EXERCICE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `COEFFICIENTS_SAISONNIERS`
--

DROP TABLE IF EXISTS `COEFFICIENTS_SAISONNIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `COEFFICIENTS_SAISONNIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `coefficient` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coeff` (`exercice`,`mois`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `COEFFICIENTS_SAISONNIERS`
--

LOCK TABLES `COEFFICIENTS_SAISONNIERS` WRITE;
/*!40000 ALTER TABLE `COEFFICIENTS_SAISONNIERS` DISABLE KEYS */;
INSERT INTO `COEFFICIENTS_SAISONNIERS` VALUES
(1,2026,1,0.85,'2026-05-20 17:00:50'),
(2,2026,2,0.80,'2026-05-20 17:00:50'),
(3,2026,3,0.90,'2026-05-20 17:00:50'),
(4,2026,4,0.95,'2026-05-20 17:00:50'),
(5,2026,5,1.00,'2026-05-20 17:00:50'),
(6,2026,6,1.05,'2026-05-20 17:00:50'),
(7,2026,7,1.10,'2026-05-20 17:00:50'),
(8,2026,8,1.08,'2026-05-20 17:00:50'),
(9,2026,9,1.12,'2026-05-20 17:00:50'),
(10,2026,10,1.15,'2026-05-20 17:00:50'),
(11,2026,11,1.18,'2026-05-20 17:00:50'),
(12,2026,12,1.20,'2026-05-20 17:00:50');
/*!40000 ALTER TABLE `COEFFICIENTS_SAISONNIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONSOMMATIONS_ACTIVITES`
--

DROP TABLE IF EXISTS `CONSOMMATIONS_ACTIVITES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONSOMMATIONS_ACTIVITES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `activite_id` int(11) NOT NULL,
  `nombre_inducteurs` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `activite_id` (`activite_id`),
  CONSTRAINT `CONSOMMATIONS_ACTIVITES_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `PRODUITS_CAE` (`id`),
  CONSTRAINT `CONSOMMATIONS_ACTIVITES_ibfk_2` FOREIGN KEY (`activite_id`) REFERENCES `ACTIVITES_ABC` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONSOMMATIONS_ACTIVITES`
--

LOCK TABLES `CONSOMMATIONS_ACTIVITES` WRITE;
/*!40000 ALTER TABLE `CONSOMMATIONS_ACTIVITES` DISABLE KEYS */;
INSERT INTO `CONSOMMATIONS_ACTIVITES` VALUES
(1,1,1,100.00,'2026-05-16 19:03:35'),
(2,1,2,800.00,'2026-05-16 19:03:35'),
(3,1,3,50.00,'2026-05-16 19:03:35'),
(4,1,4,80.00,'2026-05-16 19:03:35'),
(5,2,1,150.00,'2026-05-16 19:03:35'),
(6,2,2,1200.00,'2026-05-16 19:03:35'),
(7,2,3,80.00,'2026-05-16 19:03:35'),
(8,2,4,120.00,'2026-05-16 19:03:35'),
(9,3,1,200.00,'2026-05-16 19:03:35'),
(10,3,2,1500.00,'2026-05-16 19:03:35'),
(11,3,3,100.00,'2026-05-16 19:03:35'),
(12,3,4,150.00,'2026-05-16 19:03:35');
/*!40000 ALTER TABLE `CONSOMMATIONS_ACTIVITES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONSOMMATIONS_CENTRES`
--

DROP TABLE IF EXISTS `CONSOMMATIONS_CENTRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONSOMMATIONS_CENTRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `centre_id` int(11) NOT NULL,
  `quantite_unite_oeuvre` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `centre_id` (`centre_id`),
  CONSTRAINT `CONSOMMATIONS_CENTRES_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `PRODUITS_CAE` (`id`),
  CONSTRAINT `CONSOMMATIONS_CENTRES_ibfk_2` FOREIGN KEY (`centre_id`) REFERENCES `CENTRES_ANALYSE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONSOMMATIONS_CENTRES`
--

LOCK TABLES `CONSOMMATIONS_CENTRES` WRITE;
/*!40000 ALTER TABLE `CONSOMMATIONS_CENTRES` DISABLE KEYS */;
INSERT INTO `CONSOMMATIONS_CENTRES` VALUES
(1,1,1,500.00,'2026-05-16 19:03:35'),
(2,1,2,800.00,'2026-05-16 19:03:35'),
(3,1,3,400.00,'2026-05-16 19:03:35'),
(4,2,1,800.00,'2026-05-16 19:03:35'),
(5,2,2,1200.00,'2026-05-16 19:03:35'),
(6,2,3,800.00,'2026-05-16 19:03:35'),
(7,3,1,1000.00,'2026-05-16 19:03:35'),
(8,3,2,1500.00,'2026-05-16 19:03:35'),
(9,3,3,1200.00,'2026-05-16 19:03:35');
/*!40000 ALTER TABLE `CONSOMMATIONS_CENTRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONSTATATIONS_AVANCE`
--

DROP TABLE IF EXISTS `CONSTATATIONS_AVANCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONSTATATIONS_AVANCE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_operation` date NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `type` enum('CHARGE_AVANCE','PRODUIT_AVANCE') NOT NULL,
  `compte_charge_produit` int(11) NOT NULL,
  `compte_regularisation` int(11) NOT NULL,
  `montant_initial` decimal(15,2) NOT NULL,
  `montant_restant` decimal(15,2) NOT NULL,
  `echeance` date NOT NULL,
  `cloture` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONSTATATIONS_AVANCE`
--

LOCK TABLES `CONSTATATIONS_AVANCE` WRITE;
/*!40000 ALTER TABLE `CONSTATATIONS_AVANCE` DISABLE KEYS */;
/*!40000 ALTER TABLE `CONSTATATIONS_AVANCE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTRAINTES_RESSOURCES`
--

DROP TABLE IF EXISTS `CONTRAINTES_RESSOURCES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONTRAINTES_RESSOURCES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ressource` varchar(50) NOT NULL,
  `disponibilite` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTRAINTES_RESSOURCES`
--

LOCK TABLES `CONTRAINTES_RESSOURCES` WRITE;
/*!40000 ALTER TABLE `CONTRAINTES_RESSOURCES` DISABLE KEYS */;
INSERT INTO `CONTRAINTES_RESSOURCES` VALUES
(1,'Ressource1',100,'2026-05-20 17:00:50'),
(2,'Ressource2',80,'2026-05-20 17:00:50'),
(3,'Ressource3',40,'2026-05-20 17:00:50');
/*!40000 ALTER TABLE `CONTRAINTES_RESSOURCES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTRATS`
--

DROP TABLE IF EXISTS `CONTRATS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONTRATS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `tiers_id` int(11) NOT NULL,
  `type_contrat` enum('CLIENT','FOURNISSEUR','AUTRE') NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `objet` varchar(255) DEFAULT NULL,
  `montant_total` decimal(15,2) DEFAULT NULL,
  `statut` enum('ACTIF','SUSPENDU','TERMINE','RESILIE') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `tiers_id` (`tiers_id`),
  CONSTRAINT `CONTRATS_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTRATS`
--

LOCK TABLES `CONTRATS` WRITE;
/*!40000 ALTER TABLE `CONTRATS` DISABLE KEYS */;
/*!40000 ALTER TABLE `CONTRATS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTROLES_CONFORMITE`
--

DROP TABLE IF EXISTS `CONTROLES_CONFORMITE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONTROLES_CONFORMITE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_controle` date NOT NULL,
  `type_controle` enum('BALANCE','LETTRAGE','AMORTISSEMENT','PROVISION','STOCK') NOT NULL,
  `resultat` enum('OK','ANOMALIE','CRITIQUE') DEFAULT 'OK',
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTROLES_CONFORMITE`
--

LOCK TABLES `CONTROLES_CONFORMITE` WRITE;
/*!40000 ALTER TABLE `CONTROLES_CONFORMITE` DISABLE KEYS */;
/*!40000 ALTER TABLE `CONTROLES_CONFORMITE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTROLE_CLASSES`
--

DROP TABLE IF EXISTS `CONTROLE_CLASSES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONTROLE_CLASSES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classe` int(11) NOT NULL,
  `intitule` varchar(100) NOT NULL,
  `nature` enum('ACTIF','PASSIF','CHARGE','PRODUIT','SPECIAL') NOT NULL,
  `sens_normal` enum('DEBIT','CREDIT') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `classe` (`classe`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTROLE_CLASSES`
--

LOCK TABLES `CONTROLE_CLASSES` WRITE;
/*!40000 ALTER TABLE `CONTROLE_CLASSES` DISABLE KEYS */;
INSERT INTO `CONTROLE_CLASSES` VALUES
(1,1,'Capitaux propres','PASSIF','CREDIT','2026-05-07 22:01:05'),
(2,2,'Immobilisations','ACTIF','DEBIT','2026-05-07 22:01:05'),
(3,3,'Stocks et en-cours','ACTIF','DEBIT','2026-05-07 22:01:05'),
(4,4,'Tiers (Clients, Fournisseurs)','ACTIF','DEBIT','2026-05-07 22:01:05'),
(5,5,'Trésorerie','ACTIF','DEBIT','2026-05-07 22:01:05'),
(6,6,'Charges','CHARGE','DEBIT','2026-05-07 22:01:05'),
(7,7,'Produits','PRODUIT','CREDIT','2026-05-07 22:01:05'),
(8,8,'Engagements hors bilan','SPECIAL','DEBIT','2026-05-07 22:01:05');
/*!40000 ALTER TABLE `CONTROLE_CLASSES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTROLE_INTERNE`
--

DROP TABLE IF EXISTS `CONTROLE_INTERNE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONTROLE_INTERNE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_controle` date NOT NULL,
  `type_controle` enum('QUOTIDIEN','HEBDOMADAIRE','MENSUEL','ANNUEL') NOT NULL,
  `indicateur` varchar(100) NOT NULL,
  `valeur_attendue` decimal(15,2) DEFAULT NULL,
  `valeur_constatee` decimal(15,2) DEFAULT NULL,
  `ecart` decimal(15,2) DEFAULT NULL,
  `statut` enum('CONFORME','ECART','CRITIQUE') DEFAULT 'CONFORME',
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTROLE_INTERNE`
--

LOCK TABLES `CONTROLE_INTERNE` WRITE;
/*!40000 ALTER TABLE `CONTROLE_INTERNE` DISABLE KEYS */;
/*!40000 ALTER TABLE `CONTROLE_INTERNE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CONTROLE_REGLES`
--

DROP TABLE IF EXISTS `CONTROLE_REGLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CONTROLE_REGLES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_ecriture` varchar(50) NOT NULL,
  `compte_debit_autorise` text DEFAULT NULL,
  `compte_credit_autorise` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CONTROLE_REGLES`
--

LOCK TABLES `CONTROLE_REGLES` WRITE;
/*!40000 ALTER TABLE `CONTROLE_REGLES` DISABLE KEYS */;
INSERT INTO `CONTROLE_REGLES` VALUES
(1,'VENDEUR','521, 411','701, 703, 706','Vente de biens/services','2026-05-07 22:01:05'),
(2,'ACHETEUR','601, 602, 606, 630','401, 521','Achat de marchandises','2026-05-07 22:01:05'),
(3,'TRESORERIE','521, 53, 57','521, 53, 57','Opérations de trésorerie','2026-05-07 22:01:05'),
(4,'IMMOBILISATION','20,21,22,23,24,25','401, 521','Acquisition immobilisation','2026-05-07 22:01:05'),
(5,'CHARGE','60,61,62,63,64,65,66,67,68','401, 421, 521','Comptabilisation de charges','2026-05-07 22:01:05'),
(6,'PRODUIT','411, 521','70,71,72,73,74,75,76,77','Comptabilisation de produits','2026-05-07 22:01:05'),
(7,'EVENEMENT_ADAPTATIF','60,61,62,63,64,65,66,67,68,70,71,72,73,74,75,76,77','40,41,50,51,52,53,54,55,56,57','Événement postérieur adaptatif (à comptabiliser)','2026-05-07 22:01:51'),
(8,'EVENEMENT_NON_ADAPTATIF','','','Événement postérieur non adaptatif (mention annexe)','2026-05-07 22:01:51');
/*!40000 ALTER TABLE `CONTROLE_REGLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `COUTS_PREETABLIS`
--

DROP TABLE IF EXISTS `COUTS_PREETABLIS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `COUTS_PREETABLIS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centre_profit` varchar(50) NOT NULL,
  `cout_unitaire_standard` decimal(10,2) DEFAULT NULL,
  `cout_unitaire_reel` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `COUTS_PREETABLIS`
--

LOCK TABLES `COUTS_PREETABLIS` WRITE;
/*!40000 ALTER TABLE `COUTS_PREETABLIS` DISABLE KEYS */;
INSERT INTO `COUTS_PREETABLIS` VALUES
(1,'Centre Alpha',100.00,110.00,'2026-05-20 17:00:50'),
(2,'Centre Beta',150.00,140.00,'2026-05-20 17:00:50');
/*!40000 ALTER TABLE `COUTS_PREETABLIS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `COUTS_VARIABLES`
--

DROP TABLE IF EXISTS `COUTS_VARIABLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `COUTS_VARIABLES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `matieres_premieres` decimal(15,2) DEFAULT NULL,
  `main_oeuvre_directe` decimal(15,2) DEFAULT NULL,
  `energie` decimal(15,2) DEFAULT NULL,
  `autres_charges_variables` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  CONSTRAINT `COUTS_VARIABLES_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `PRODUITS_CAE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `COUTS_VARIABLES`
--

LOCK TABLES `COUTS_VARIABLES` WRITE;
/*!40000 ALTER TABLE `COUTS_VARIABLES` DISABLE KEYS */;
INSERT INTO `COUTS_VARIABLES` VALUES
(1,1,8000.00,5000.00,1000.00,500.00,'2026-05-16 19:03:35'),
(2,2,5000.00,3000.00,800.00,300.00,'2026-05-16 19:03:35'),
(3,3,3000.00,2000.00,500.00,200.00,'2026-05-16 19:03:35');
/*!40000 ALTER TABLE `COUTS_VARIABLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `COUT_CAPITAL`
--

DROP TABLE IF EXISTS `COUT_CAPITAL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `COUT_CAPITAL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `taux_sans_risque` decimal(5,2) DEFAULT NULL,
  `prime_risque` decimal(5,2) DEFAULT NULL,
  `beta` decimal(5,2) DEFAULT NULL,
  `cout_capitaux_propres` decimal(5,2) DEFAULT NULL,
  `cout_dette` decimal(5,2) DEFAULT NULL,
  `wacc` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `COUT_CAPITAL`
--

LOCK TABLES `COUT_CAPITAL` WRITE;
/*!40000 ALTER TABLE `COUT_CAPITAL` DISABLE KEYS */;
/*!40000 ALTER TABLE `COUT_CAPITAL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CRITERES_INVESTISSEMENT`
--

DROP TABLE IF EXISTS `CRITERES_INVESTISSEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `CRITERES_INVESTISSEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_id` int(11) NOT NULL,
  `van` decimal(15,2) DEFAULT NULL,
  `tri` decimal(5,2) DEFAULT NULL,
  `ip` decimal(5,2) DEFAULT NULL,
  `delai_recuperation` int(11) DEFAULT NULL,
  `decision` enum('ACCEPTE','REJETE','A_ETUDIER') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projet_id` (`projet_id`),
  CONSTRAINT `CRITERES_INVESTISSEMENT_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `PROJETS_INVESTISSEMENT` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CRITERES_INVESTISSEMENT`
--

LOCK TABLES `CRITERES_INVESTISSEMENT` WRITE;
/*!40000 ALTER TABLE `CRITERES_INVESTISSEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `CRITERES_INVESTISSEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DECLARATIONS_FISCALES`
--

DROP TABLE IF EXISTS `DECLARATIONS_FISCALES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DECLARATIONS_FISCALES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `periode` varchar(7) NOT NULL,
  `type_declaration` enum('TVA','IS','IR','CSS','IPRES','TAXE_SALAIRE') NOT NULL,
  `montant_du` decimal(15,2) NOT NULL,
  `montant_paye` decimal(15,2) DEFAULT NULL,
  `penalites` decimal(15,2) DEFAULT 0.00,
  `date_limite` date NOT NULL,
  `date_paiement` date DEFAULT NULL,
  `reference_paiement` varchar(100) DEFAULT NULL,
  `statut` enum('EN_COURS','PAYEE','EN_RETARD','CONTROLEE') DEFAULT 'EN_COURS',
  `fichier_produit` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_declarations_periode` (`periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DECLARATIONS_FISCALES`
--

LOCK TABLES `DECLARATIONS_FISCALES` WRITE;
/*!40000 ALTER TABLE `DECLARATIONS_FISCALES` DISABLE KEYS */;
/*!40000 ALTER TABLE `DECLARATIONS_FISCALES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DEPOTS_STOCK`
--

DROP TABLE IF EXISTS `DEPOTS_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DEPOTS_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `actif` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DEPOTS_STOCK`
--

LOCK TABLES `DEPOTS_STOCK` WRITE;
/*!40000 ALTER TABLE `DEPOTS_STOCK` DISABLE KEYS */;
INSERT INTO `DEPOTS_STOCK` VALUES
(1,'DEP01','Dépôt Principal Dakar','Dakar Plateau',NULL,1,'2026-05-11 19:31:17'),
(2,'DEP02','Entrepôt Thiès','Zone industrielle Thiès',NULL,1,'2026-05-11 19:31:17'),
(3,'DEP03','Magasin de vente','Centre ville',NULL,1,'2026-05-11 19:31:17');
/*!40000 ALTER TABLE `DEPOTS_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DEPRECIATIONS_STOCK`
--

DROP TABLE IF EXISTS `DEPRECIATIONS_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DEPRECIATIONS_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `date_depreciation` date NOT NULL,
  `valeur_nette_comptable` decimal(15,2) NOT NULL,
  `valeur_nette_reelle` decimal(15,2) NOT NULL,
  `depreciation` decimal(15,2) GENERATED ALWAYS AS (`valeur_nette_comptable` - `valeur_nette_reelle`) STORED,
  `compte_dotation` int(11) DEFAULT NULL,
  `compte_provision` int(11) DEFAULT NULL,
  `justificatif` text DEFAULT NULL,
  `ecriture_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `compte_dotation` (`compte_dotation`),
  KEY `compte_provision` (`compte_provision`),
  CONSTRAINT `DEPRECIATIONS_STOCK_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`),
  CONSTRAINT `DEPRECIATIONS_STOCK_ibfk_2` FOREIGN KEY (`compte_dotation`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `DEPRECIATIONS_STOCK_ibfk_3` FOREIGN KEY (`compte_provision`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DEPRECIATIONS_STOCK`
--

LOCK TABLES `DEPRECIATIONS_STOCK` WRITE;
/*!40000 ALTER TABLE `DEPRECIATIONS_STOCK` DISABLE KEYS */;
/*!40000 ALTER TABLE `DEPRECIATIONS_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DETAILS_FACTURE`
--

DROP TABLE IF EXISTS `DETAILS_FACTURE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DETAILS_FACTURE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facture_vente_id` int(11) DEFAULT NULL,
  `facture_achat_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `libelle` varchar(255) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(15,2) NOT NULL,
  `remise_ligne` decimal(5,2) DEFAULT 0.00,
  `montant_ht` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `facture_vente_id` (`facture_vente_id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `DETAILS_FACTURE_ibfk_1` FOREIGN KEY (`facture_vente_id`) REFERENCES `FACTURES_VENTE` (`id`),
  CONSTRAINT `DETAILS_FACTURE_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DETAILS_FACTURE`
--

LOCK TABLES `DETAILS_FACTURE` WRITE;
/*!40000 ALTER TABLE `DETAILS_FACTURE` DISABLE KEYS */;
/*!40000 ALTER TABLE `DETAILS_FACTURE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DEVISES`
--

DROP TABLE IF EXISTS `DEVISES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DEVISES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `libelle` varchar(50) NOT NULL,
  `taux_fcfa` decimal(15,4) NOT NULL,
  `date_taux` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DEVISES`
--

LOCK TABLES `DEVISES` WRITE;
/*!40000 ALTER TABLE `DEVISES` DISABLE KEYS */;
INSERT INTO `DEVISES` VALUES
(1,'EUR','Euro',655.9570,'2026-05-09','2026-05-09 02:10:44'),
(2,'USD','Dollar US',600.0000,'2026-05-09','2026-05-09 02:10:44'),
(3,'GBP','Livre sterling',760.0000,'2026-05-09','2026-05-09 02:10:44'),
(4,'CNY','Yuan chinois',82.5000,'2026-05-09','2026-05-09 02:10:44');
/*!40000 ALTER TABLE `DEVISES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DIVIDENDES`
--

DROP TABLE IF EXISTS `DIVIDENDES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DIVIDENDES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `date_distribution` date NOT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `montant_par_action` decimal(15,2) DEFAULT NULL,
  `type_paiement` enum('NUMERAIRE','ACTIONS','MIXTE') DEFAULT 'NUMERAIRE',
  `statut` enum('PROPOSE','APPROUVE','PAYE','ANNULE') DEFAULT 'PROPOSE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DIVIDENDES`
--

LOCK TABLES `DIVIDENDES` WRITE;
/*!40000 ALTER TABLE `DIVIDENDES` DISABLE KEYS */;
/*!40000 ALTER TABLE `DIVIDENDES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DOCUMENTS_COMPTABLES`
--

DROP TABLE IF EXISTS `DOCUMENTS_COMPTABLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DOCUMENTS_COMPTABLES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `type_document` enum('FACTURE','RELEVE','JUSTIFICATIF','CONTRAT','ATTESTATION','DECLARATION') NOT NULL,
  `titre` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `taille_fichier` int(11) DEFAULT NULL,
  `type_mime` varchar(100) DEFAULT NULL,
  `date_document` date NOT NULL,
  `entite_type` varchar(50) DEFAULT NULL,
  `entite_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_documents_reference` (`reference`),
  CONSTRAINT `DOCUMENTS_COMPTABLES_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `USERS` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DOCUMENTS_COMPTABLES`
--

LOCK TABLES `DOCUMENTS_COMPTABLES` WRITE;
/*!40000 ALTER TABLE `DOCUMENTS_COMPTABLES` DISABLE KEYS */;
/*!40000 ALTER TABLE `DOCUMENTS_COMPTABLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DOCUMENTS_COMPTABLES_ENUM`
--

DROP TABLE IF EXISTS `DOCUMENTS_COMPTABLES_ENUM`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DOCUMENTS_COMPTABLES_ENUM` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `type_document` enum('FACTURE','RELEVE','CONTRAT','JUSTIFICATIF','BUDGET') NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `entite_type` varchar(50) DEFAULT NULL,
  `entite_id` int(11) DEFAULT NULL,
  `date_import` date NOT NULL,
  `statut` enum('ATTENTE','TRAITE','REJETE') DEFAULT 'ATTENTE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DOCUMENTS_COMPTABLES_ENUM`
--

LOCK TABLES `DOCUMENTS_COMPTABLES_ENUM` WRITE;
/*!40000 ALTER TABLE `DOCUMENTS_COMPTABLES_ENUM` DISABLE KEYS */;
/*!40000 ALTER TABLE `DOCUMENTS_COMPTABLES_ENUM` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DONNEES_PL`
--

DROP TABLE IF EXISTS `DONNEES_PL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DONNEES_PL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit` varchar(50) NOT NULL,
  `ressource1` decimal(10,2) DEFAULT NULL,
  `ressource2` decimal(10,2) DEFAULT NULL,
  `ressource3` decimal(10,2) DEFAULT NULL,
  `marge_unitaire` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DONNEES_PL`
--

LOCK TABLES `DONNEES_PL` WRITE;
/*!40000 ALTER TABLE `DONNEES_PL` DISABLE KEYS */;
INSERT INTO `DONNEES_PL` VALUES
(1,'Produit A',2.00,1.00,0.00,40.00,'2026-05-20 17:00:50'),
(2,'Produit B',1.00,2.00,1.00,30.00,'2026-05-20 17:00:50');
/*!40000 ALTER TABLE `DONNEES_PL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DOTATIONS_AMORTISSEMENTS`
--

DROP TABLE IF EXISTS `DOTATIONS_AMORTISSEMENTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `DOTATIONS_AMORTISSEMENTS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `immobilisation_id` int(11) NOT NULL,
  `date_dotation` date NOT NULL,
  `exercice` int(11) NOT NULL,
  `montant_dotation` decimal(15,2) NOT NULL,
  `amortissement_cumule` decimal(15,2) NOT NULL,
  `valeur_nette_comptable` decimal(15,2) NOT NULL,
  `type_calcul` enum('LINEAIRE','DEGRESSIF','DEROGATOIRE') DEFAULT 'LINEAIRE',
  `ecriture_comptable_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `immobilisation_id` (`immobilisation_id`),
  KEY `ecriture_comptable_id` (`ecriture_comptable_id`),
  CONSTRAINT `DOTATIONS_AMORTISSEMENTS_ibfk_1` FOREIGN KEY (`immobilisation_id`) REFERENCES `AMORTISSEMENTS` (`id`),
  CONSTRAINT `DOTATIONS_AMORTISSEMENTS_ibfk_2` FOREIGN KEY (`ecriture_comptable_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DOTATIONS_AMORTISSEMENTS`
--

LOCK TABLES `DOTATIONS_AMORTISSEMENTS` WRITE;
/*!40000 ALTER TABLE `DOTATIONS_AMORTISSEMENTS` DISABLE KEYS */;
/*!40000 ALTER TABLE `DOTATIONS_AMORTISSEMENTS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECARTS_ACTIVITE`
--

DROP TABLE IF EXISTS `ECARTS_ACTIVITE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECARTS_ACTIVITE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `periode` varchar(20) NOT NULL,
  `centre_analytique` varchar(50) DEFAULT NULL,
  `activite_reelle` decimal(15,2) DEFAULT NULL,
  `activite_normale` decimal(15,2) DEFAULT NULL,
  `coefficient` decimal(5,2) DEFAULT NULL,
  `charges_fixes` decimal(15,2) DEFAULT NULL,
  `charges_fixes_imputees` decimal(15,2) DEFAULT NULL,
  `ecart_activite` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECARTS_ACTIVITE`
--

LOCK TABLES `ECARTS_ACTIVITE` WRITE;
/*!40000 ALTER TABLE `ECARTS_ACTIVITE` DISABLE KEYS */;
/*!40000 ALTER TABLE `ECARTS_ACTIVITE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECARTS_BUDGETAIRES`
--

DROP TABLE IF EXISTS `ECARTS_BUDGETAIRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECARTS_BUDGETAIRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `type_budget` varchar(50) NOT NULL,
  `ecart_montant` decimal(15,2) DEFAULT NULL,
  `ecart_pourcentage` decimal(5,2) DEFAULT NULL,
  `cause_principale` text DEFAULT NULL,
  `action_corrective` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECARTS_BUDGETAIRES`
--

LOCK TABLES `ECARTS_BUDGETAIRES` WRITE;
/*!40000 ALTER TABLE `ECARTS_BUDGETAIRES` DISABLE KEYS */;
/*!40000 ALTER TABLE `ECARTS_BUDGETAIRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECARTS_GESTION`
--

DROP TABLE IF EXISTS `ECARTS_GESTION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECARTS_GESTION` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `type_ecart` enum('FAVORABLE','DEFAVORABLE') NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `montant_ecart` decimal(15,2) NOT NULL,
  `cause` varchar(500) DEFAULT NULL,
  `action_corrective` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ecarts_exercice` (`exercice`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECARTS_GESTION`
--

LOCK TABLES `ECARTS_GESTION` WRITE;
/*!40000 ALTER TABLE `ECARTS_GESTION` DISABLE KEYS */;
INSERT INTO `ECARTS_GESTION` VALUES
(1,2026,1,'DEFAVORABLE','Écart sur ventes',200000.00,'À analyser',NULL,'2026-05-07 18:56:27');
/*!40000 ALTER TABLE `ECARTS_GESTION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECARTS_REEVALUATION`
--

DROP TABLE IF EXISTS `ECARTS_REEVALUATION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECARTS_REEVALUATION` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `immobilisation_id` int(11) NOT NULL,
  `date_reevaluation` date NOT NULL,
  `valeur_comptable_ancienne` decimal(15,2) NOT NULL,
  `valeur_reevaluee` decimal(15,2) NOT NULL,
  `ecart_reevaluation` decimal(15,2) GENERATED ALWAYS AS (`valeur_reevaluee` - `valeur_comptable_ancienne`) STORED,
  `compte_immobilisation` int(11) NOT NULL,
  `compte_ecart` int(11) NOT NULL,
  `justificatif` text DEFAULT NULL,
  `ecriture_comptable_id` int(11) DEFAULT NULL,
  `statut` enum('PROVISOIRE','DEFINITIF','ANNULE') DEFAULT 'PROVISOIRE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `immobilisation_id` (`immobilisation_id`),
  KEY `compte_immobilisation` (`compte_immobilisation`),
  KEY `compte_ecart` (`compte_ecart`),
  KEY `ecriture_comptable_id` (`ecriture_comptable_id`),
  CONSTRAINT `ECARTS_REEVALUATION_ibfk_1` FOREIGN KEY (`immobilisation_id`) REFERENCES `AMORTISSEMENTS` (`id`),
  CONSTRAINT `ECARTS_REEVALUATION_ibfk_2` FOREIGN KEY (`compte_immobilisation`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `ECARTS_REEVALUATION_ibfk_3` FOREIGN KEY (`compte_ecart`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `ECARTS_REEVALUATION_ibfk_4` FOREIGN KEY (`ecriture_comptable_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECARTS_REEVALUATION`
--

LOCK TABLES `ECARTS_REEVALUATION` WRITE;
/*!40000 ALTER TABLE `ECARTS_REEVALUATION` DISABLE KEYS */;
/*!40000 ALTER TABLE `ECARTS_REEVALUATION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECHEANCES_CLIENTS`
--

DROP TABLE IF EXISTS `ECHEANCES_CLIENTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECHEANCES_CLIENTS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `date_facture` date NOT NULL,
  `date_echeance` date NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `montant_regle` decimal(15,2) DEFAULT 0.00,
  `reference_facture` varchar(50) NOT NULL,
  `statut` enum('EN_ATTENTE','PARTIEL','REGLE','EN_RETARD') DEFAULT 'EN_ATTENTE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `ECHEANCES_CLIENTS_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `TIERS` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECHEANCES_CLIENTS`
--

LOCK TABLES `ECHEANCES_CLIENTS` WRITE;
/*!40000 ALTER TABLE `ECHEANCES_CLIENTS` DISABLE KEYS */;
/*!40000 ALTER TABLE `ECHEANCES_CLIENTS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECHEANCIERS`
--

DROP TABLE IF EXISTS `ECHEANCIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECHEANCIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_echeance` date NOT NULL,
  `type_echeance` enum('CLIENT','FOURNISSEUR','BANQUE','INTERNE') NOT NULL,
  `tiers_id` int(11) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `statut` enum('EN_ATTENTE','PAYE','EN_RETARD','RELANCE') DEFAULT 'EN_ATTENTE',
  `date_relance` date DEFAULT NULL,
  `nb_relances` int(11) DEFAULT 0,
  `ecriture_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tiers_id` (`tiers_id`),
  KEY `ecriture_id` (`ecriture_id`),
  CONSTRAINT `ECHEANCIERS_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`),
  CONSTRAINT `ECHEANCIERS_ibfk_2` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECHEANCIERS`
--

LOCK TABLES `ECHEANCIERS` WRITE;
/*!40000 ALTER TABLE `ECHEANCIERS` DISABLE KEYS */;
INSERT INTO `ECHEANCIERS` VALUES
(1,'2026-05-13','CLIENT',NULL,1416000.00,'Facture FACT-001 - Échéance J+2','EN_ATTENTE',NULL,0,NULL,'2026-05-11 19:11:45'),
(2,'2026-05-14','FOURNISSEUR',NULL,472000.00,'Facture fournisseur - Échéance J+3','EN_ATTENTE',NULL,0,NULL,'2026-05-11 19:11:45'),
(3,'2026-05-18','CLIENT',NULL,2950000.00,'Facture BOA - Échéance J+7','EN_ATTENTE',NULL,0,NULL,'2026-05-11 19:11:45');
/*!40000 ALTER TABLE `ECHEANCIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECHEANCIERS_PAIEMENT`
--

DROP TABLE IF EXISTS `ECHEANCIERS_PAIEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECHEANCIERS_PAIEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_echeance` date NOT NULL,
  `type_echeance` enum('CLIENT','FOURNISSEUR','SALAIRE','IMPOT','BANQUE') NOT NULL,
  `tiers_id` int(11) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `montant_regle` decimal(15,2) DEFAULT 0.00,
  `libelle` varchar(255) NOT NULL,
  `reference_facture` varchar(100) DEFAULT NULL,
  `statut` enum('EN_ATTENTE','PARTIEL','REGLE','EN_RETARD','RELANCE') DEFAULT 'EN_ATTENTE',
  `date_reglement` date DEFAULT NULL,
  `mode_paiement` enum('CHEQUE','VIREMENT','ESPECES','LCR','PRELEVEMENT') DEFAULT 'VIREMENT',
  `ecriture_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tiers_id` (`tiers_id`),
  KEY `ecriture_id` (`ecriture_id`),
  CONSTRAINT `ECHEANCIERS_PAIEMENT_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`),
  CONSTRAINT `ECHEANCIERS_PAIEMENT_ibfk_2` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECHEANCIERS_PAIEMENT`
--

LOCK TABLES `ECHEANCIERS_PAIEMENT` WRITE;
/*!40000 ALTER TABLE `ECHEANCIERS_PAIEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `ECHEANCIERS_PAIEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ECRITURES_COMPTABLES`
--

DROP TABLE IF EXISTS `ECRITURES_COMPTABLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECRITURES_COMPTABLES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_ecriture` date NOT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `compte_debite_id` int(11) DEFAULT NULL,
  `compte_credite_id` int(11) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `reference_piece` varchar(50) DEFAULT NULL,
  `piece_justificative` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `validation` tinyint(4) DEFAULT 0,
  `valide_par` int(11) DEFAULT NULL,
  `date_validation` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `type_ecriture` enum('COURANTE','REGULARISATION','CONTREPASSATION','CLOTURE','VENTE','ACHAT','PAIE','PROVISION','TRESORERIE','EXPORT','IMPORT','AMORTISSEMENT','ESCOMPTE','ECART','INVENTAIRE','EFFET','REGLEMENT','DEPRECIATION','ANALYTIQUE','REPORT','SUBVENTION','VIREMENT') DEFAULT 'COURANTE',
  `journal_id` int(11) DEFAULT NULL,
  `numero_piece` varchar(50) DEFAULT NULL,
  `section_analytique_id` int(11) DEFAULT NULL,
  `modele_id` int(11) DEFAULT NULL,
  `date_lettrage` date DEFAULT NULL,
  `lettrage_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compte_debite_id` (`compte_debite_id`),
  KEY `compte_credite_id` (`compte_credite_id`),
  KEY `idx_ecriture_date` (`date_ecriture`),
  KEY `idx_ecriture_type` (`type_ecriture`),
  KEY `fk_ecriture_validation` (`valide_par`),
  KEY `idx_journal` (`journal_id`),
  KEY `idx_section` (`section_analytique_id`),
  KEY `idx_modele` (`modele_id`),
  CONSTRAINT `ECRITURES_COMPTABLES_ibfk_1` FOREIGN KEY (`journal_id`) REFERENCES `JOURNAUX` (`id`),
  CONSTRAINT `ECRITURES_COMPTABLES_ibfk_3` FOREIGN KEY (`modele_id`) REFERENCES `MODELES_SAISIE` (`id`),
  CONSTRAINT `fk_ecriture_validation` FOREIGN KEY (`valide_par`) REFERENCES `USERS` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECRITURES_COMPTABLES`
--

LOCK TABLES `ECRITURES_COMPTABLES` WRITE;
/*!40000 ALTER TABLE `ECRITURES_COMPTABLES` DISABLE KEYS */;
INSERT INTO `ECRITURES_COMPTABLES` VALUES
(1,'2026-01-01','Capital',521,101,50000000.00,'CAP',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(2,'2026-01-05','Immobilisations',231,521,25000000.00,'IMMO',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(3,'2026-01-10','Immobilisations',241,521,8000000.00,'IMMO',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(4,'2026-01-15','Immobilisations',245,521,3500000.00,'IMMO',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(5,'2026-01-20','Immobilisations',253,521,15000000.00,'IMMO',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(6,'2026-02-10','Vente',521,701,1250000.00,'VEN',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(7,'2026-03-15','Vente',411,701,2500000.00,'VEN',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(8,'2026-03-28','Salaires',641,421,3500000.00,'PAIE',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(9,'2026-03-28','CNSS',651,431,157500.00,'PAIE',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(10,'2026-03-28','IPRES',652,432,280000.00,'PAIE',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(11,'2026-03-28','CSS',653,433,245000.00,'PAIE',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(12,'2026-04-10','Achats',601,401,500000.00,'ACH',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','ACHAT',NULL,NULL,NULL,NULL,NULL,NULL),
(13,'2026-05-01','Loyer',613,521,500000.00,'LOY',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(14,'2026-01-02','Frais',631,521,1500000.00,'FRAIS',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(15,'2026-12-31','Amortissements',681,281,1250000.00,'AMT',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','AMORTISSEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(16,'2026-12-31','Amortissements',681,284,1600000.00,'AMT',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','AMORTISSEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(17,'2026-12-31','Amortissements',681,285,350000.00,'AMT',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','AMORTISSEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(18,'2026-12-31','Amortissements',681,286,3000000.00,'AMT',NULL,NULL,0,NULL,NULL,'2026-05-18 18:52:27','AMORTISSEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(19,'2026-05-18','Vente à nouveau client OMEGA SARL',521,701,5000000.00,'FACT-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(21,'2026-05-18','Achat nouveau matériel',241,401,2000000.00,'ACH-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','ACHAT',NULL,NULL,NULL,NULL,NULL,NULL),
(23,'2026-05-18','Loyer magasin supplémentaire',613,521,750000.00,'LOY-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(24,'2026-05-18','Remboursement partiel fournisseur',401,521,250000.00,'REM-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(25,'2026-05-18','Salaire mai nouvelle recrue',641,421,500000.00,'PAIE-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(26,'2026-05-18','CNSS nouvelle recrue',651,431,22500.00,'PAIE-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(27,'2026-05-18','IPRES nouvelle recrue',652,432,40000.00,'PAIE-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(28,'2026-05-18','CSS nouvelle recrue',653,433,35000.00,'PAIE-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(29,'2026-05-18','Dotation amortissement nouveau matériel',681,284,40000.00,'AMT-NEW-001',NULL,NULL,0,NULL,NULL,'2026-05-18 21:48:18','AMORTISSEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(41,'2026-12-31','Report à nouveau de la perte',113,120,5520000.00,'REP-001',NULL,NULL,0,NULL,NULL,'2026-05-18 22:02:42','REPORT',NULL,NULL,NULL,NULL,NULL,NULL),
(47,'2026-12-31','Clôture des comptes de produits',120,701,5000000.00,'CLOT-PROD',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(48,'2026-12-31','Clôture des achats',120,601,500000.00,'CLOT-001',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(49,'2026-12-31','Clôture des loyers',120,613,500000.00,'CLOT-002',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(50,'2026-12-31','Clôture des salaires',120,641,4000000.00,'CLOT-003',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(51,'2026-12-31','Clôture des CNSS',120,651,180000.00,'CLOT-004',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(52,'2026-12-31','Clôture des IPRES',120,652,320000.00,'CLOT-005',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(53,'2026-12-31','Clôture des CSS',120,653,280000.00,'CLOT-006',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(54,'2026-12-31','Clôture des amortissements',120,681,240000.00,'CLOT-007',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(55,'2026-12-31','Report à nouveau du bénéfice',112,120,2940000.00,'REP-BENEFICE',NULL,NULL,0,NULL,NULL,'2026-05-18 22:25:46','REPORT',NULL,NULL,NULL,NULL,NULL,NULL),
(56,'2026-12-31','Intégration de la perte de l\'exercice',113,120,810000.00,'INTEG-PERTE',NULL,NULL,0,NULL,NULL,'2026-05-18 22:27:36','CLOTURE',NULL,NULL,NULL,NULL,NULL,NULL),
(59,'2026-05-18','TVA nette à payer',681,4451,0.00,'TVA-FINALE',NULL,NULL,0,NULL,NULL,'2026-05-18 22:57:04','REGULARISATION',NULL,NULL,NULL,NULL,NULL,NULL),
(60,'2026-05-18','Régularisation bilan - ajustement du résultat',681,120,5250000.00,'REG-BILAN',NULL,NULL,0,NULL,NULL,'2026-05-18 23:00:09','REGULARISATION',NULL,NULL,NULL,NULL,NULL,NULL),
(61,'2026-01-01','Facture CLI001',411,701,1000000.00,'FACT-001',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:10','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(62,'2026-02-15','Facture CLI002',411,701,500000.00,'FACT-002',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:10','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(63,'2026-03-10','Facture CLI003',411,701,750000.00,'FACT-003',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:10','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(64,'2026-02-10','Règlement partiel Alpha',521,411,300000.00,'REG-001',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:10','REGLEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(65,'2026-01-10','Facture FOUR001',601,401,800000.00,'FACH-001',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:10','ACHAT',NULL,NULL,NULL,NULL,NULL,NULL),
(66,'2026-02-20','Facture FOUR002',601,401,600000.00,'FACH-002',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:10','ACHAT',NULL,NULL,NULL,NULL,NULL,NULL),
(67,'2026-03-05','Paiement partiel FOUR001',401,521,300000.00,'PACH-001',NULL,NULL,0,NULL,NULL,'2026-05-19 15:09:11','REGLEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(68,'2026-05-21','Apport en capital',521,101,10000000.00,'CAP-001',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(69,'2026-05-21','Vente de produits A',521,701,2500000.00,'FACT-001',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(70,'2026-05-21','Vente de produits B',521,701,1800000.00,'FACT-002',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(71,'2026-05-21','Prestation de service',521,703,1200000.00,'FACT-003',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','VENTE',NULL,NULL,NULL,NULL,NULL,NULL),
(72,'2026-05-21','Achat matières premières',601,401,800000.00,'ACH-001',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','ACHAT',NULL,NULL,NULL,NULL,NULL,NULL),
(73,'2026-05-21','Achat fournitures',606,401,200000.00,'ACH-002',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','ACHAT',NULL,NULL,NULL,NULL,NULL,NULL),
(74,'2026-05-21','Salaires',641,421,1500000.00,'PAIE-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(75,'2026-05-21','CNSS employeur',651,431,100000.00,'PAIE-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(76,'2026-05-21','IPRES employeur',652,432,80000.00,'PAIE-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(77,'2026-05-21','CSS employeur',653,433,50000.00,'PAIE-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:54','PAIE',NULL,NULL,NULL,NULL,NULL,NULL),
(78,'2026-05-21','Loyer',613,521,300000.00,'LOY-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:55','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(79,'2026-05-21','Électricité',628,521,150000.00,'ENE-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:55','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(80,'2026-05-21','Entretien',615,521,100000.00,'ENT-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:55','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL),
(81,'2026-05-21','Dotation amortissement matériel',681,284,200000.00,'AMT-001',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:55','AMORTISSEMENT',NULL,NULL,NULL,NULL,NULL,NULL),
(82,'2026-05-21','Frais bancaires',671,521,25000.00,'BQ-01',NULL,NULL,0,NULL,NULL,'2026-05-21 18:57:56','COURANTE',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `ECRITURES_COMPTABLES` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER verifier_compte_syscohada
BEFORE INSERT ON ECRITURES_COMPTABLES
FOR EACH ROW
BEGIN
    DECLARE debit_existe INT;
    DECLARE credit_existe INT;
    
    SELECT COUNT(*) INTO debit_existe FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = NEW.compte_debite_id;
    SELECT COUNT(*) INTO credit_existe FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = NEW.compte_credite_id;
    
    IF debit_existe = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ERREUR SYSCOHADA: Le compte débit n''existe pas dans le plan comptable UEMOA';
    END IF;
    
    IF credit_existe = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ERREUR SYSCOHADA: Le compte crédit n''existe pas dans le plan comptable UEMOA';
    END IF;
    
    
    IF NEW.type_ecriture != 'CLOTURE' THEN
        IF NEW.compte_debite_id BETWEEN 700 AND 799 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ERREUR SYSCOHADA: Un compte de PRODUIT (Classe 7) ne peut pas être au DÉBIT';
        END IF;
        
        IF NEW.compte_credite_id BETWEEN 600 AND 699 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ERREUR SYSCOHADA: Un compte de CHARGE (Classe 6) ne peut pas être au CRÉDIT';
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ECRITURES_PREVISIONNELLES`
--

DROP TABLE IF EXISTS `ECRITURES_PREVISIONNELLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ECRITURES_PREVISIONNELLES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_prevision` date NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `compte_debit` int(11) NOT NULL,
  `compte_credit` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `probabilite` decimal(3,0) DEFAULT 100,
  `section_analytique_id` int(11) DEFAULT NULL,
  `valide` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_debit` (`compte_debit`),
  KEY `compte_credit` (`compte_credit`),
  KEY `section_analytique_id` (`section_analytique_id`),
  CONSTRAINT `ECRITURES_PREVISIONNELLES_ibfk_1` FOREIGN KEY (`compte_debit`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `ECRITURES_PREVISIONNELLES_ibfk_2` FOREIGN KEY (`compte_credit`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `ECRITURES_PREVISIONNELLES_ibfk_3` FOREIGN KEY (`section_analytique_id`) REFERENCES `SECTIONS_ANALYTIQUES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ECRITURES_PREVISIONNELLES`
--

LOCK TABLES `ECRITURES_PREVISIONNELLES` WRITE;
/*!40000 ALTER TABLE `ECRITURES_PREVISIONNELLES` DISABLE KEYS */;
/*!40000 ALTER TABLE `ECRITURES_PREVISIONNELLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EFFETS_COMMERCE`
--

DROP TABLE IF EXISTS `EFFETS_COMMERCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EFFETS_COMMERCE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_effet` varchar(50) NOT NULL,
  `date_creation` date NOT NULL,
  `date_echeance` date NOT NULL,
  `type_effet` enum('LETTRE_CHANGE','BILLET_A_ORDRE','TRAITE','PROMESSE') NOT NULL,
  `nature` enum('CLIENT','FOURNISSEUR') NOT NULL,
  `tiers_id` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `frais_escompte` decimal(15,2) DEFAULT 0.00,
  `taux_escompte` decimal(5,2) DEFAULT NULL,
  `agios` decimal(15,2) DEFAULT 0.00,
  `commission` decimal(15,2) DEFAULT 0.00,
  `montant_net` decimal(15,2) DEFAULT NULL,
  `statut` enum('EN_PORTEFEUILLE','A_L_ENCAISSEMENT','ENCAISSE','ESCOMPTE','IMPAGNE','RENOUVELE') DEFAULT 'EN_PORTEFEUILLE',
  `banque_escompte` int(11) DEFAULT NULL,
  `escompte_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_effet` (`numero_effet`),
  KEY `tiers_id` (`tiers_id`),
  CONSTRAINT `EFFETS_COMMERCE_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EFFETS_COMMERCE`
--

LOCK TABLES `EFFETS_COMMERCE` WRITE;
/*!40000 ALTER TABLE `EFFETS_COMMERCE` DISABLE KEYS */;
/*!40000 ALTER TABLE `EFFETS_COMMERCE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EFFET_LEVIER`
--

DROP TABLE IF EXISTS `EFFET_LEVIER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EFFET_LEVIER` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `rentabilite_economique` decimal(10,2) DEFAULT NULL,
  `rentabilite_financiere` decimal(10,2) DEFAULT NULL,
  `taux_endettement` decimal(10,2) DEFAULT NULL,
  `effet_leverage` decimal(10,2) DEFAULT NULL,
  `interpretation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EFFET_LEVIER`
--

LOCK TABLES `EFFET_LEVIER` WRITE;
/*!40000 ALTER TABLE `EFFET_LEVIER` DISABLE KEYS */;
INSERT INTO `EFFET_LEVIER` VALUES
(1,2026,0.00,-20.27,0.00,0.00,'L\'endettement n\'a pas d\'effet sur la rentabilité','2026-05-15 18:33:06'),
(2,2026,0.00,-20.27,0.00,0.00,'L\'endettement n\'a pas d\'effet sur la rentabilité','2026-05-15 18:33:32'),
(3,2026,0.00,-20.27,0.00,0.00,'L\'endettement n\'a pas d\'effet sur la rentabilité','2026-05-17 22:24:08'),
(4,2026,0.00,-3.93,0.00,0.00,'L\'endettement n\'a pas d\'effet sur la rentabilité','2026-05-26 21:20:17'),
(5,2026,0.00,-3.93,0.00,0.00,'L\'endettement n\'a pas d\'effet sur la rentabilité','2026-05-27 08:29:19'),
(6,2026,0.00,-3.93,0.00,0.00,'L\'endettement n\'a pas d\'effet sur la rentabilité','2026-05-27 08:35:05');
/*!40000 ALTER TABLE `EFFET_LEVIER` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EMISSIONS_DETAILS`
--

DROP TABLE IF EXISTS `EMISSIONS_DETAILS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EMISSIONS_DETAILS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emission_id` int(11) NOT NULL,
  `tiers_id` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `compte_banque` int(11) DEFAULT 521,
  `beneficiaire` varchar(255) DEFAULT NULL,
  `rib` varchar(50) DEFAULT NULL,
  `motif` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emission_id` (`emission_id`),
  KEY `tiers_id` (`tiers_id`),
  CONSTRAINT `EMISSIONS_DETAILS_ibfk_1` FOREIGN KEY (`emission_id`) REFERENCES `EMISSIONS_PAIEMENT` (`id`),
  CONSTRAINT `EMISSIONS_DETAILS_ibfk_2` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EMISSIONS_DETAILS`
--

LOCK TABLES `EMISSIONS_DETAILS` WRITE;
/*!40000 ALTER TABLE `EMISSIONS_DETAILS` DISABLE KEYS */;
/*!40000 ALTER TABLE `EMISSIONS_DETAILS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EMISSIONS_PAIEMENT`
--

DROP TABLE IF EXISTS `EMISSIONS_PAIEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EMISSIONS_PAIEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_emission` date NOT NULL,
  `type_emission` enum('VIREMENT','CHEQUE','PRELEVEMENT','LCR') NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `statut` enum('BROUILLON','EMIS','VALIDE','REJETE') DEFAULT 'BROUILLON',
  `fichier_genere` varchar(500) DEFAULT NULL,
  `date_transmission` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `EMISSIONS_PAIEMENT_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `USERS` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EMISSIONS_PAIEMENT`
--

LOCK TABLES `EMISSIONS_PAIEMENT` WRITE;
/*!40000 ALTER TABLE `EMISSIONS_PAIEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `EMISSIONS_PAIEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EMPRUNTS_OBLIGATAIRES`
--

DROP TABLE IF EXISTS `EMPRUNTS_OBLIGATAIRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EMPRUNTS_OBLIGATAIRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(200) NOT NULL,
  `date_emission` date NOT NULL,
  `date_echeance` date NOT NULL,
  `montant_emprunte` decimal(15,2) NOT NULL,
  `taux_interet` decimal(5,2) NOT NULL,
  `nb_obligations` int(11) NOT NULL,
  `valeur_nominale` decimal(15,2) NOT NULL,
  `prix_emission` decimal(15,2) NOT NULL,
  `prime_remboursement` decimal(15,2) DEFAULT 0.00,
  `statut` enum('ACTIF','REMBOURSE','PAR_DEFAUT') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EMPRUNTS_OBLIGATAIRES`
--

LOCK TABLES `EMPRUNTS_OBLIGATAIRES` WRITE;
/*!40000 ALTER TABLE `EMPRUNTS_OBLIGATAIRES` DISABLE KEYS */;
/*!40000 ALTER TABLE `EMPRUNTS_OBLIGATAIRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ENGAGEMENTS_HORS_BILAN`
--

DROP TABLE IF EXISTS `ENGAGEMENTS_HORS_BILAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ENGAGEMENTS_HORS_BILAN` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_engagement` date NOT NULL,
  `type` enum('CAUTION','AVAL','GARANTIE','NANTISSEMENT','CREDIT_BAIL','LITIGE') NOT NULL,
  `beneficiaire` varchar(200) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `devise` varchar(3) DEFAULT 'XOF',
  `date_echeance` date DEFAULT NULL,
  `compte_engagement` int(11) NOT NULL,
  `document_reference` varchar(100) DEFAULT NULL,
  `statut` enum('ACTIF','ECHU','LEVE','ANNULE') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_engagement` (`compte_engagement`),
  KEY `idx_engagement_type` (`type`),
  CONSTRAINT `ENGAGEMENTS_HORS_BILAN_ibfk_1` FOREIGN KEY (`compte_engagement`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ENGAGEMENTS_HORS_BILAN`
--

LOCK TABLES `ENGAGEMENTS_HORS_BILAN` WRITE;
/*!40000 ALTER TABLE `ENGAGEMENTS_HORS_BILAN` DISABLE KEYS */;
/*!40000 ALTER TABLE `ENGAGEMENTS_HORS_BILAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVALUATIONS_STOCK`
--

DROP TABLE IF EXISTS `EVALUATIONS_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EVALUATIONS_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `date_evaluation` date NOT NULL,
  `methode` enum('CUMP','FIFO','LIFO') NOT NULL,
  `quantite` int(11) NOT NULL,
  `valeur_totale` decimal(15,2) NOT NULL,
  `valeur_unitaire` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `EVALUATIONS_STOCK_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVALUATIONS_STOCK`
--

LOCK TABLES `EVALUATIONS_STOCK` WRITE;
/*!40000 ALTER TABLE `EVALUATIONS_STOCK` DISABLE KEYS */;
INSERT INTO `EVALUATIONS_STOCK` VALUES
(1,1,'2026-05-24','FIFO',0,0.00,0.00,'2026-05-24 17:05:03'),
(2,3,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 17:05:22'),
(3,1,'2026-05-24','CUMP',-10,-272222.22,0.00,'2026-05-24 20:08:43'),
(4,3,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:10:15'),
(5,3,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:10:34'),
(6,3,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:10:46'),
(7,3,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:11:16'),
(8,2,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:11:45'),
(9,1,'2026-05-24','FIFO',0,0.00,0.00,'2026-05-24 20:12:09'),
(10,1,'2026-05-24','CUMP',-10,-272222.22,0.00,'2026-05-24 20:12:37'),
(11,1,'2026-05-24','CUMP',-10,-272222.22,0.00,'2026-05-24 20:13:34'),
(12,3,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:14:31'),
(13,2,'2026-05-24','CUMP',0,0.00,0.00,'2026-05-24 20:14:39'),
(14,1,'2026-05-24','CUMP',-10,-272222.22,0.00,'2026-05-24 20:14:48'),
(15,1,'2026-05-24','FIFO',0,0.00,0.00,'2026-05-24 20:15:07'),
(16,1,'2026-05-24','LIFO',0,0.00,0.00,'2026-05-24 20:15:26'),
(17,1,'2026-05-24','CUMP',-10,-272222.22,0.00,'2026-05-24 20:15:38');
/*!40000 ALTER TABLE `EVALUATIONS_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVALUATION_ENTREPRISE`
--

DROP TABLE IF EXISTS `EVALUATION_ENTREPRISE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EVALUATION_ENTREPRISE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_evaluation` date NOT NULL,
  `methode` enum('PATRIMONIALE','RENTABILITE','DCF','COMPARABLE','MIXTE') NOT NULL,
  `valeur_nette_comptable` decimal(15,2) DEFAULT NULL,
  `capacite_autofinancement` decimal(15,2) DEFAULT NULL,
  `valeur_actualisee` decimal(15,2) DEFAULT NULL,
  `goodwill` decimal(15,2) DEFAULT NULL,
  `valeur_entreprise` decimal(15,2) DEFAULT NULL,
  `rapport_complet` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVALUATION_ENTREPRISE`
--

LOCK TABLES `EVALUATION_ENTREPRISE` WRITE;
/*!40000 ALTER TABLE `EVALUATION_ENTREPRISE` DISABLE KEYS */;
/*!40000 ALTER TABLE `EVALUATION_ENTREPRISE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVALUATION_ENTREPRISE_COMPLETE`
--

DROP TABLE IF EXISTS `EVALUATION_ENTREPRISE_COMPLETE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EVALUATION_ENTREPRISE_COMPLETE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `methode` enum('ACTUARIELLE','COMPARABLE','PATRIMONIALE','DCF','MIXTE') NOT NULL,
  `valeur_entreprise` decimal(15,2) DEFAULT NULL,
  `valeur_capitaux_propres` decimal(15,2) DEFAULT NULL,
  `multiple_ebitda` decimal(5,2) DEFAULT NULL,
  `multiple_ca` decimal(5,2) DEFAULT NULL,
  `cash_flows_actualises` decimal(15,2) DEFAULT NULL,
  `details_calcul` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVALUATION_ENTREPRISE_COMPLETE`
--

LOCK TABLES `EVALUATION_ENTREPRISE_COMPLETE` WRITE;
/*!40000 ALTER TABLE `EVALUATION_ENTREPRISE_COMPLETE` DISABLE KEYS */;
/*!40000 ALTER TABLE `EVALUATION_ENTREPRISE_COMPLETE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVALUATION_GLOBALE`
--

DROP TABLE IF EXISTS `EVALUATION_GLOBALE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EVALUATION_GLOBALE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `ebitda` decimal(15,2) DEFAULT NULL,
  `flux_libre_tresorerie` decimal(15,2) DEFAULT NULL,
  `taux_actualisation` decimal(5,2) DEFAULT NULL,
  `croissance_perpetuelle` decimal(5,2) DEFAULT NULL,
  `valeur_entreprise` decimal(15,2) DEFAULT NULL,
  `valeur_capitaux_propres` decimal(15,2) DEFAULT NULL,
  `valeur_action` decimal(15,2) DEFAULT NULL,
  `nombre_actions` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVALUATION_GLOBALE`
--

LOCK TABLES `EVALUATION_GLOBALE` WRITE;
/*!40000 ALTER TABLE `EVALUATION_GLOBALE` DISABLE KEYS */;
/*!40000 ALTER TABLE `EVALUATION_GLOBALE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `EVENEMENTS_POSTERIEURS`
--

DROP TABLE IF EXISTS `EVENEMENTS_POSTERIEURS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EVENEMENTS_POSTERIEURS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_vention` date NOT NULL,
  `date_publication_comptes` date DEFAULT NULL,
  `libelle` varchar(255) NOT NULL,
  `type_evenement` enum('ADAPTATIF','NON_ADAPTATIF') NOT NULL,
  `description` text DEFAULT NULL,
  `impact_financier` decimal(15,2) DEFAULT NULL,
  `compte_impacte` int(11) DEFAULT NULL,
  `nature_impact` varchar(100) DEFAULT NULL,
  `ecriture_id` int(11) DEFAULT NULL,
  `a_publier` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_impacte` (`compte_impacte`),
  KEY `ecriture_id` (`ecriture_id`),
  CONSTRAINT `EVENEMENTS_POSTERIEURS_ibfk_1` FOREIGN KEY (`compte_impacte`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `EVENEMENTS_POSTERIEURS_ibfk_2` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `EVENEMENTS_POSTERIEURS`
--

LOCK TABLES `EVENEMENTS_POSTERIEURS` WRITE;
/*!40000 ALTER TABLE `EVENEMENTS_POSTERIEURS` DISABLE KEYS */;
/*!40000 ALTER TABLE `EVENEMENTS_POSTERIEURS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FACTURES`
--

DROP TABLE IF EXISTS `FACTURES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `FACTURES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `date_facture` date NOT NULL,
  `type` enum('VENTE','ACHAT','AVOIR','DEPENSE') NOT NULL,
  `tiers_id` int(11) NOT NULL,
  `montant_ht` decimal(15,2) NOT NULL,
  `tva` decimal(15,2) DEFAULT 0.00,
  `montant_ttc` decimal(15,2) NOT NULL,
  `statut` enum('BROUILLON','EMISE','PAYEE','ECHUE','ANNULEE') DEFAULT 'BROUILLON',
  `date_echeance` date DEFAULT NULL,
  `date_paiement` date DEFAULT NULL,
  `reference_piece` varchar(100) DEFAULT NULL,
  `observation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `tiers_id` (`tiers_id`),
  KEY `idx_factures_numero` (`numero`),
  KEY `idx_factures_date` (`date_facture`),
  CONSTRAINT `FACTURES_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FACTURES`
--

LOCK TABLES `FACTURES` WRITE;
/*!40000 ALTER TABLE `FACTURES` DISABLE KEYS */;
/*!40000 ALTER TABLE `FACTURES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FACTURES_VENTE`
--

DROP TABLE IF EXISTS `FACTURES_VENTE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `FACTURES_VENTE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `date_facture` date NOT NULL,
  `client_id` int(11) NOT NULL,
  `montant_ht` decimal(15,2) NOT NULL,
  `taux_remise` decimal(5,2) DEFAULT 0.00,
  `montant_remise` decimal(15,2) DEFAULT 0.00,
  `montant_net_commercial` decimal(15,2) DEFAULT NULL,
  `frais_port` decimal(15,2) DEFAULT 0.00,
  `frais_emballage` decimal(15,2) DEFAULT 0.00,
  `total_ht` decimal(15,2) DEFAULT NULL,
  `tva` decimal(15,2) DEFAULT NULL,
  `montant_ttc` decimal(15,2) DEFAULT NULL,
  `statut` enum('BROUILLON','EMISE','PAYEE','ANNULEE') DEFAULT 'BROUILLON',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `FACTURES_VENTE_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `TIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FACTURES_VENTE`
--

LOCK TABLES `FACTURES_VENTE` WRITE;
/*!40000 ALTER TABLE `FACTURES_VENTE` DISABLE KEYS */;
/*!40000 ALTER TABLE `FACTURES_VENTE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FLUX_PROJET`
--

DROP TABLE IF EXISTS `FLUX_PROJET`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `FLUX_PROJET` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_id` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `flux_net` decimal(15,2) NOT NULL,
  `type_flux` enum('INVESTISSEMENT','EXPLOITATION','CESSION') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projet_id` (`projet_id`),
  CONSTRAINT `FLUX_PROJET_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `PROJETS_INVESTISSEMENT` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FLUX_PROJET`
--

LOCK TABLES `FLUX_PROJET` WRITE;
/*!40000 ALTER TABLE `FLUX_PROJET` DISABLE KEYS */;
/*!40000 ALTER TABLE `FLUX_PROJET` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FLUX_TRESORERIE_ACTUALISES`
--

DROP TABLE IF EXISTS `FLUX_TRESORERIE_ACTUALISES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `FLUX_TRESORERIE_ACTUALISES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_nom` varchar(255) NOT NULL,
  `annee` int(11) NOT NULL,
  `flux_net` decimal(15,2) NOT NULL,
  `taux_actualisation` decimal(5,2) NOT NULL,
  `coefficient_actualisation` decimal(10,4) DEFAULT NULL,
  `flux_actualise` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FLUX_TRESORERIE_ACTUALISES`
--

LOCK TABLES `FLUX_TRESORERIE_ACTUALISES` WRITE;
/*!40000 ALTER TABLE `FLUX_TRESORERIE_ACTUALISES` DISABLE KEYS */;
/*!40000 ALTER TABLE `FLUX_TRESORERIE_ACTUALISES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FRAIS_ACCESSOIRES`
--

DROP TABLE IF EXISTS `FRAIS_ACCESSOIRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `FRAIS_ACCESSOIRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `type_frais` enum('PORT','EMBALLAGE','ASSURANCE','TRANSPORT','DOUANE') NOT NULL,
  `taux` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FRAIS_ACCESSOIRES`
--

LOCK TABLES `FRAIS_ACCESSOIRES` WRITE;
/*!40000 ALTER TABLE `FRAIS_ACCESSOIRES` DISABLE KEYS */;
INSERT INTO `FRAIS_ACCESSOIRES` VALUES
(1,'Frais de port','PORT',0.00,'2026-05-08 00:59:22'),
(2,'Emballages consignés','EMBALLAGE',0.00,'2026-05-08 00:59:22'),
(3,'Assurance transport','ASSURANCE',1.50,'2026-05-08 00:59:22'),
(4,'Frais de douane','DOUANE',5.00,'2026-05-08 00:59:22'),
(5,'Frais de livraison','TRANSPORT',0.00,'2026-05-08 00:59:22');
/*!40000 ALTER TABLE `FRAIS_ACCESSOIRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `GORDON_SHAPIRO`
--

DROP TABLE IF EXISTS `GORDON_SHAPIRO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `GORDON_SHAPIRO` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `dividende_actuel` decimal(15,2) DEFAULT NULL,
  `taux_croissance` decimal(5,2) DEFAULT NULL,
  `cout_capitaux_propres` decimal(5,2) DEFAULT NULL,
  `valeur_entreprise` decimal(15,2) DEFAULT NULL,
  `valeur_action` decimal(15,2) DEFAULT NULL,
  `nombre_actions` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `GORDON_SHAPIRO`
--

LOCK TABLES `GORDON_SHAPIRO` WRITE;
/*!40000 ALTER TABLE `GORDON_SHAPIRO` DISABLE KEYS */;
/*!40000 ALTER TABLE `GORDON_SHAPIRO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `GROUPES_SOCIETES`
--

DROP TABLE IF EXISTS `GROUPES_SOCIETES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `GROUPES_SOCIETES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `siege_social` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `GROUPES_SOCIETES`
--

LOCK TABLES `GROUPES_SOCIETES` WRITE;
/*!40000 ALTER TABLE `GROUPES_SOCIETES` DISABLE KEYS */;
/*!40000 ALTER TABLE `GROUPES_SOCIETES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `HYPOTHESES_SCENARIO`
--

DROP TABLE IF EXISTS `HYPOTHESES_SCENARIO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `HYPOTHESES_SCENARIO` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scenario_id` int(11) NOT NULL,
  `poste` varchar(50) NOT NULL,
  `variation_pourcent` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `scenario_id` (`scenario_id`),
  CONSTRAINT `HYPOTHESES_SCENARIO_ibfk_1` FOREIGN KEY (`scenario_id`) REFERENCES `SCENARIOS_FINANCIERS` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `HYPOTHESES_SCENARIO`
--

LOCK TABLES `HYPOTHESES_SCENARIO` WRITE;
/*!40000 ALTER TABLE `HYPOTHESES_SCENARIO` DISABLE KEYS */;
/*!40000 ALTER TABLE `HYPOTHESES_SCENARIO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IMPOTS_MENSUELS`
--

DROP TABLE IF EXISTS `IMPOTS_MENSUELS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `IMPOTS_MENSUELS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `type_impot` enum('TVA','IS','IRPP','CSS','IPRES','TAXE_SALAIRE','PATENTE','TAXE_FONCIERE') NOT NULL,
  `base_calcul` decimal(15,2) NOT NULL,
  `taux` decimal(5,2) NOT NULL,
  `montant_du` decimal(15,2) NOT NULL,
  `montant_paye` decimal(15,2) DEFAULT 0.00,
  `date_limite` date NOT NULL,
  `date_paiement` date DEFAULT NULL,
  `statut` enum('DU','PAYE','EN_RETARD') DEFAULT 'DU',
  `penalites` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IMPOTS_MENSUELS`
--

LOCK TABLES `IMPOTS_MENSUELS` WRITE;
/*!40000 ALTER TABLE `IMPOTS_MENSUELS` DISABLE KEYS */;
/*!40000 ALTER TABLE `IMPOTS_MENSUELS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IMPOTS_TAXES`
--

DROP TABLE IF EXISTS `IMPOTS_TAXES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `IMPOTS_TAXES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `trimestre` int(11) DEFAULT NULL,
  `type_impot` enum('IS','IR','TVA','CSS','IPRES','TAXE_SALAIRE','PATENTE') NOT NULL,
  `base_calcul` decimal(15,2) NOT NULL,
  `taux` decimal(5,2) NOT NULL,
  `montant_theorique` decimal(15,2) GENERATED ALWAYS AS (`base_calcul` * `taux` / 100) STORED,
  `montant_paye` decimal(15,2) DEFAULT NULL,
  `date_echeance` date NOT NULL,
  `date_paiement` date DEFAULT NULL,
  `statut` enum('EN_ATTENTE','PAYE','EN_RETARD','CONTROLE') DEFAULT 'EN_ATTENTE',
  `penalites` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_impots_type` (`type_impot`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IMPOTS_TAXES`
--

LOCK TABLES `IMPOTS_TAXES` WRITE;
/*!40000 ALTER TABLE `IMPOTS_TAXES` DISABLE KEYS */;
INSERT INTO `IMPOTS_TAXES` VALUES
(1,2026,1,'IS',4500000.00,25.00,1125000.00,NULL,'2026-04-30',NULL,'PAYE',0.00,'2026-05-07 18:56:27'),
(2,2026,1,'TVA',15000000.00,18.00,2700000.00,NULL,'2026-04-15',NULL,'PAYE',0.00,'2026-05-07 18:56:27'),
(3,2026,1,'CSS',7500000.00,7.00,525000.00,NULL,'2026-04-15',NULL,'PAYE',0.00,'2026-05-07 18:56:27'),
(4,2026,1,'IPRES',7500000.00,16.00,1200000.00,NULL,'2026-04-15',NULL,'PAYE',0.00,'2026-05-07 18:56:27'),
(5,2026,2,'IS',5200000.00,25.00,1300000.00,NULL,'2026-07-31',NULL,'EN_ATTENTE',0.00,'2026-05-07 18:56:27'),
(6,2026,2,'TVA',16500000.00,18.00,2970000.00,NULL,'2026-07-15',NULL,'EN_ATTENTE',0.00,'2026-05-07 18:56:27'),
(7,2026,2,'CSS',8200000.00,7.00,574000.00,NULL,'2026-07-15',NULL,'EN_ATTENTE',0.00,'2026-05-07 18:56:27'),
(8,2026,2,'IPRES',8200000.00,16.00,1312000.00,NULL,'2026-07-15',NULL,'EN_ATTENTE',0.00,'2026-05-07 18:56:27');
/*!40000 ALTER TABLE `IMPOTS_TAXES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `IMPUTATIONS_ANALYTIQUES`
--

DROP TABLE IF EXISTS `IMPUTATIONS_ANALYTIQUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `IMPUTATIONS_ANALYTIQUES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecriture_id` int(11) NOT NULL,
  `centre_id` int(11) NOT NULL,
  `pourcentage` decimal(5,2) NOT NULL,
  `montant_impute` decimal(15,2) NOT NULL,
  `date_imputation` date NOT NULL,
  `statut` enum('PROVISOIRE','DEFINITIF') DEFAULT 'PROVISOIRE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ecriture_id` (`ecriture_id`),
  KEY `centre_id` (`centre_id`),
  CONSTRAINT `IMPUTATIONS_ANALYTIQUES_ibfk_1` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`),
  CONSTRAINT `IMPUTATIONS_ANALYTIQUES_ibfk_2` FOREIGN KEY (`centre_id`) REFERENCES `CENTRES_ANALYTIQUES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `IMPUTATIONS_ANALYTIQUES`
--

LOCK TABLES `IMPUTATIONS_ANALYTIQUES` WRITE;
/*!40000 ALTER TABLE `IMPUTATIONS_ANALYTIQUES` DISABLE KEYS */;
/*!40000 ALTER TABLE `IMPUTATIONS_ANALYTIQUES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `INDICATEURS_FINANCIERS`
--

DROP TABLE IF EXISTS `INDICATEURS_FINANCIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `INDICATEURS_FINANCIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `indicateur` varchar(50) NOT NULL,
  `valeur` decimal(15,2) NOT NULL,
  `interpretation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `INDICATEURS_FINANCIERS`
--

LOCK TABLES `INDICATEURS_FINANCIERS` WRITE;
/*!40000 ALTER TABLE `INDICATEURS_FINANCIERS` DISABLE KEYS */;
/*!40000 ALTER TABLE `INDICATEURS_FINANCIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `INVENTAIRE_PHYSIQUE`
--

DROP TABLE IF EXISTS `INVENTAIRE_PHYSIQUE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `INVENTAIRE_PHYSIQUE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compte_id` int(11) NOT NULL,
  `reference_article` varchar(100) DEFAULT NULL,
  `libelle` varchar(255) NOT NULL,
  `quantite_theorique` decimal(15,2) DEFAULT 0.00,
  `quantite_reelle` decimal(15,2) NOT NULL,
  `ecart_quantite` decimal(15,2) GENERATED ALWAYS AS (`quantite_reelle` - `quantite_theorique`) STORED,
  `prix_unitaire` decimal(15,2) NOT NULL,
  `valeur_theorique` decimal(15,2) DEFAULT NULL,
  `valeur_reelle` decimal(15,2) DEFAULT NULL,
  `ecart_valeur` decimal(15,2) DEFAULT NULL,
  `date_inventaire` date NOT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `statut` enum('BROUILLON','VALIDE','COMPTABLE') DEFAULT 'BROUILLON',
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_id` (`compte_id`),
  KEY `idx_inventaire_date` (`date_inventaire`),
  CONSTRAINT `INVENTAIRE_PHYSIQUE_ibfk_1` FOREIGN KEY (`compte_id`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `INVENTAIRE_PHYSIQUE`
--

LOCK TABLES `INVENTAIRE_PHYSIQUE` WRITE;
/*!40000 ALTER TABLE `INVENTAIRE_PHYSIQUE` DISABLE KEYS */;
/*!40000 ALTER TABLE `INVENTAIRE_PHYSIQUE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `INVENTAIRE_PHYSIQUE_STOCK`
--

DROP TABLE IF EXISTS `INVENTAIRE_PHYSIQUE_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `INVENTAIRE_PHYSIQUE_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `date_inventaire` date NOT NULL,
  `quantite_theorique` int(11) NOT NULL,
  `quantite_reelle` int(11) NOT NULL,
  `ecart_quantite` int(11) GENERATED ALWAYS AS (`quantite_reelle` - `quantite_theorique`) STORED,
  `valeur_ecart` decimal(15,2) DEFAULT NULL,
  `cause_ecart` varchar(255) DEFAULT NULL,
  `statut` enum('BROUILLON','VALIDE','COMPTABLE') DEFAULT 'BROUILLON',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `INVENTAIRE_PHYSIQUE_STOCK_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `INVENTAIRE_PHYSIQUE_STOCK`
--

LOCK TABLES `INVENTAIRE_PHYSIQUE_STOCK` WRITE;
/*!40000 ALTER TABLE `INVENTAIRE_PHYSIQUE_STOCK` DISABLE KEYS */;
/*!40000 ALTER TABLE `INVENTAIRE_PHYSIQUE_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `JOURNAUX`
--

DROP TABLE IF EXISTS `JOURNAUX`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `JOURNAUX` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `type_journal` enum('ACHATS','VENTES','CAISSE','BANQUE','OD','SITUATION') NOT NULL,
  `dernier_numero` int(11) DEFAULT 0,
  `actif` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `JOURNAUX`
--

LOCK TABLES `JOURNAUX` WRITE;
/*!40000 ALTER TABLE `JOURNAUX` DISABLE KEYS */;
INSERT INTO `JOURNAUX` VALUES
(7,'AC','Achats','ACHATS',0,1,'2026-05-11 19:11:44'),
(8,'VE','Ventes','VENTES',0,1,'2026-05-11 19:11:44'),
(9,'BK','Banque','BANQUE',0,1,'2026-05-11 19:11:44'),
(10,'CK','Caisse','CAISSE',0,1,'2026-05-11 19:11:44'),
(11,'OD','Opérations Diverses','OD',0,1,'2026-05-11 19:11:44'),
(12,'SI','Situation','SITUATION',0,1,'2026-05-11 19:11:44');
/*!40000 ALTER TABLE `JOURNAUX` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `KPIS`
--

DROP TABLE IF EXISTS `KPIS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `KPIS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `periode` date NOT NULL,
  `indicateur` varchar(100) NOT NULL,
  `valeur` decimal(15,2) DEFAULT NULL,
  `objectif` decimal(15,2) DEFAULT NULL,
  `unite` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `KPIS`
--

LOCK TABLES `KPIS` WRITE;
/*!40000 ALTER TABLE `KPIS` DISABLE KEYS */;
/*!40000 ALTER TABLE `KPIS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `KPI_ANALYTIQUE`
--

DROP TABLE IF EXISTS `KPI_ANALYTIQUE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `KPI_ANALYTIQUE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL,
  `periode` date NOT NULL,
  `kpi_type` enum('ROI','MARGE','CAF','BFR','TREA','ENDETTEMENT') NOT NULL,
  `valeur` decimal(15,2) NOT NULL,
  `objectif` decimal(15,2) DEFAULT NULL,
  `ecart` decimal(15,2) GENERATED ALWAYS AS (`valeur` - `objectif`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `section_id` (`section_id`),
  CONSTRAINT `KPI_ANALYTIQUE_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `SECTIONS_ANALYTIQUES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `KPI_ANALYTIQUE`
--

LOCK TABLES `KPI_ANALYTIQUE` WRITE;
/*!40000 ALTER TABLE `KPI_ANALYTIQUE` DISABLE KEYS */;
/*!40000 ALTER TABLE `KPI_ANALYTIQUE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `KPI_INDICATEURS`
--

DROP TABLE IF EXISTS `KPI_INDICATEURS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `KPI_INDICATEURS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `indicateur` varchar(100) NOT NULL,
  `valeur` decimal(15,2) NOT NULL,
  `cible` decimal(15,2) DEFAULT NULL,
  `objectif` decimal(15,2) DEFAULT NULL,
  `date_mesure` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kpi_exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `KPI_INDICATEURS`
--

LOCK TABLES `KPI_INDICATEURS` WRITE;
/*!40000 ALTER TABLE `KPI_INDICATEURS` DISABLE KEYS */;
/*!40000 ALTER TABLE `KPI_INDICATEURS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LETTRAGES`
--

DROP TABLE IF EXISTS `LETTRAGES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `LETTRAGES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_lettrage` date NOT NULL,
  `tiers_id` int(11) NOT NULL,
  `type_lettrage` enum('CLIENT','FOURNISSEUR') NOT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `montant_lettre` decimal(15,2) NOT NULL,
  `statut` enum('PARTIEL','TOTAL','EN_ATTENTE') DEFAULT 'EN_ATTENTE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tiers_id` (`tiers_id`),
  CONSTRAINT `LETTRAGES_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LETTRAGES`
--

LOCK TABLES `LETTRAGES` WRITE;
/*!40000 ALTER TABLE `LETTRAGES` DISABLE KEYS */;
/*!40000 ALTER TABLE `LETTRAGES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LETTRAGES_DETAILS`
--

DROP TABLE IF EXISTS `LETTRAGES_DETAILS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `LETTRAGES_DETAILS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lettrage_id` int(11) NOT NULL,
  `ecriture_id` int(11) NOT NULL,
  `type_ecriture` enum('FACTURE','REGLEMENT') NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lettrage_id` (`lettrage_id`),
  KEY `ecriture_id` (`ecriture_id`),
  CONSTRAINT `LETTRAGES_DETAILS_ibfk_1` FOREIGN KEY (`lettrage_id`) REFERENCES `LETTRAGES` (`id`),
  CONSTRAINT `LETTRAGES_DETAILS_ibfk_2` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LETTRAGES_DETAILS`
--

LOCK TABLES `LETTRAGES_DETAILS` WRITE;
/*!40000 ALTER TABLE `LETTRAGES_DETAILS` DISABLE KEYS */;
/*!40000 ALTER TABLE `LETTRAGES_DETAILS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LETTRAGE_BANCAIRE`
--

DROP TABLE IF EXISTS `LETTRAGE_BANCAIRE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `LETTRAGE_BANCAIRE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_operation` date NOT NULL,
  `reference_bancaire` varchar(100) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `sens` enum('DEBIT','CREDIT') NOT NULL,
  `compte_bancaire` int(11) NOT NULL,
  `ecriture_id` int(11) DEFAULT NULL,
  `date_lettrage` date DEFAULT NULL,
  `statut` enum('A_LETTRER','LETTRE','ECART') DEFAULT 'A_LETTRER',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_bancaire` (`compte_bancaire`),
  KEY `ecriture_id` (`ecriture_id`),
  KEY `idx_lettrage_date` (`date_operation`),
  KEY `idx_lettrage_statut` (`statut`),
  CONSTRAINT `LETTRAGE_BANCAIRE_ibfk_1` FOREIGN KEY (`compte_bancaire`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `LETTRAGE_BANCAIRE_ibfk_2` FOREIGN KEY (`ecriture_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LETTRAGE_BANCAIRE`
--

LOCK TABLES `LETTRAGE_BANCAIRE` WRITE;
/*!40000 ALTER TABLE `LETTRAGE_BANCAIRE` DISABLE KEYS */;
/*!40000 ALTER TABLE `LETTRAGE_BANCAIRE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LIASSES_FISCALES`
--

DROP TABLE IF EXISTS `LIASSES_FISCALES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `LIASSES_FISCALES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `type_liasse` enum('IS','TVA','IR','CSS','IPRES','CFE') NOT NULL,
  `date_generation` date NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `fichier_pdf` varchar(500) DEFAULT NULL,
  `fichier_edi` varchar(500) DEFAULT NULL,
  `statut` enum('BROUILLON','GENERE','TRANSMIS','PAYE') DEFAULT 'BROUILLON',
  `date_transmission` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LIASSES_FISCALES`
--

LOCK TABLES `LIASSES_FISCALES` WRITE;
/*!40000 ALTER TABLE `LIASSES_FISCALES` DISABLE KEYS */;
/*!40000 ALTER TABLE `LIASSES_FISCALES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LIGNES_AFFECTATION`
--

DROP TABLE IF EXISTS `LIGNES_AFFECTATION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `LIGNES_AFFECTATION` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affectation_id` int(11) NOT NULL,
  `compte_id` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `affectation_id` (`affectation_id`),
  KEY `compte_id` (`compte_id`),
  CONSTRAINT `LIGNES_AFFECTATION_ibfk_1` FOREIGN KEY (`affectation_id`) REFERENCES `AFFECTATIONS_RESULTAT` (`id`) ON DELETE CASCADE,
  CONSTRAINT `LIGNES_AFFECTATION_ibfk_2` FOREIGN KEY (`compte_id`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LIGNES_AFFECTATION`
--

LOCK TABLES `LIGNES_AFFECTATION` WRITE;
/*!40000 ALTER TABLE `LIGNES_AFFECTATION` DISABLE KEYS */;
/*!40000 ALTER TABLE `LIGNES_AFFECTATION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MODELES_SAISIE`
--

DROP TABLE IF EXISTS `MODELES_SAISIE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `MODELES_SAISIE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `compte_debit` int(11) NOT NULL,
  `compte_credit` int(11) NOT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `tva_incluse` tinyint(4) DEFAULT 0,
  `type_mouvement` enum('ACHAT','VENTE','CAISSE','BANQUE','OD') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `compte_debit` (`compte_debit`),
  KEY `compte_credit` (`compte_credit`),
  CONSTRAINT `MODELES_SAISIE_ibfk_1` FOREIGN KEY (`compte_debit`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `MODELES_SAISIE_ibfk_2` FOREIGN KEY (`compte_credit`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MODELES_SAISIE`
--

LOCK TABLES `MODELES_SAISIE` WRITE;
/*!40000 ALTER TABLE `MODELES_SAISIE` DISABLE KEYS */;
INSERT INTO `MODELES_SAISIE` VALUES
(7,'LOYER','Paiement loyer mensuel','Loyer bureau mensuel',613,521,500000.00,0,'BANQUE','2026-05-11 19:11:44'),
(8,'EAU_ELEC','Facture eau/électricité','Charges mensuelles',628,521,250000.00,0,'BANQUE','2026-05-11 19:11:44'),
(9,'ABONNEMENT','Abonnement logiciel','SaaS mensuel',618,521,150000.00,0,'BANQUE','2026-05-11 19:11:44'),
(10,'FRAIS_BANCAIRES','Frais bancaires','Tenue de compte',671,521,25000.00,0,'BANQUE','2026-05-11 19:11:44'),
(11,'AMORT_INFO','Amortissement informatique','Matériel info 5 ans',681,284,NULL,0,'OD','2026-05-11 19:11:44');
/*!40000 ALTER TABLE `MODELES_SAISIE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MODIGLIANI_MILLER`
--

DROP TABLE IF EXISTS `MODIGLIANI_MILLER`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `MODIGLIANI_MILLER` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `taux_sans_risque` decimal(5,2) DEFAULT NULL,
  `prime_risque_economique` decimal(5,2) DEFAULT NULL,
  `prime_risque_financier` decimal(5,2) DEFAULT NULL,
  `cout_capitaux_propres` decimal(5,2) DEFAULT NULL,
  `cout_dette` decimal(5,2) DEFAULT NULL,
  `taux_is` decimal(5,2) DEFAULT NULL,
  `ratio_endettement` decimal(5,2) DEFAULT NULL,
  `wacc_sans_dette` decimal(5,2) DEFAULT NULL,
  `wacc_avec_dette` decimal(5,2) DEFAULT NULL,
  `economie_fiscale` decimal(15,2) DEFAULT NULL,
  `valeur_entreprise_sans_dette` decimal(15,2) DEFAULT NULL,
  `valeur_entreprise_avec_dette` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MODIGLIANI_MILLER`
--

LOCK TABLES `MODIGLIANI_MILLER` WRITE;
/*!40000 ALTER TABLE `MODIGLIANI_MILLER` DISABLE KEYS */;
INSERT INTO `MODIGLIANI_MILLER` VALUES
(1,2026,3.00,6.00,2.00,11.00,5.00,25.00,0.00,11.00,11.00,12500.00,-92113636.36,-92113636.36,'2026-05-17 22:17:15');
/*!40000 ALTER TABLE `MODIGLIANI_MILLER` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MOUVEMENTS_STOCK`
--

DROP TABLE IF EXISTS `MOUVEMENTS_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `MOUVEMENTS_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `date_mouvement` date NOT NULL,
  `type_mouvement` enum('ENTREE','SORTIE','AJUSTEMENT') NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(15,2) NOT NULL,
  `montant_total` decimal(15,2) GENERATED ALWAYS AS (`quantite` * `prix_unitaire`) STORED,
  `reference_piece` varchar(100) DEFAULT NULL,
  `document_lie` varchar(100) DEFAULT NULL,
  `ecriture_comptable_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `lot_id` varchar(50) DEFAULT NULL,
  `date_peremption` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `ecriture_comptable_id` (`ecriture_comptable_id`),
  CONSTRAINT `MOUVEMENTS_STOCK_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`),
  CONSTRAINT `MOUVEMENTS_STOCK_ibfk_2` FOREIGN KEY (`ecriture_comptable_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MOUVEMENTS_STOCK`
--

LOCK TABLES `MOUVEMENTS_STOCK` WRITE;
/*!40000 ALTER TABLE `MOUVEMENTS_STOCK` DISABLE KEYS */;
INSERT INTO `MOUVEMENTS_STOCK` VALUES
(1,1,'2026-01-10','ENTREE',10,26000.00,260000.00,NULL,NULL,NULL,'2026-05-23 20:38:10',NULL,NULL),
(2,1,'2026-02-15','ENTREE',15,27000.00,405000.00,NULL,NULL,NULL,'2026-05-23 20:38:10',NULL,NULL),
(3,1,'2026-03-20','ENTREE',20,28000.00,560000.00,NULL,NULL,NULL,'2026-05-23 20:38:10',NULL,NULL),
(4,1,'2026-03-25','SORTIE',25,0.00,0.00,NULL,NULL,NULL,'2026-05-23 20:38:10',NULL,NULL),
(5,1,'2026-04-10','SORTIE',30,0.00,0.00,NULL,NULL,NULL,'2026-05-23 20:38:10',NULL,NULL);
/*!40000 ALTER TABLE `MOUVEMENTS_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MOUVEMENTS_TITRES`
--

DROP TABLE IF EXISTS `MOUVEMENTS_TITRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `MOUVEMENTS_TITRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre_id` int(11) NOT NULL,
  `date_mouvement` date NOT NULL,
  `type_mouvement` enum('ACHAT','VENTE','DIVIDENDE','INTERET','REVENU') NOT NULL,
  `quantite` int(11) DEFAULT NULL,
  `prix_unitaire` decimal(15,2) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `ecriture_comptable_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `titre_id` (`titre_id`),
  KEY `ecriture_comptable_id` (`ecriture_comptable_id`),
  CONSTRAINT `MOUVEMENTS_TITRES_ibfk_1` FOREIGN KEY (`titre_id`) REFERENCES `PORTEFEUILLE_TITRES` (`id`),
  CONSTRAINT `MOUVEMENTS_TITRES_ibfk_2` FOREIGN KEY (`ecriture_comptable_id`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MOUVEMENTS_TITRES`
--

LOCK TABLES `MOUVEMENTS_TITRES` WRITE;
/*!40000 ALTER TABLE `MOUVEMENTS_TITRES` DISABLE KEYS */;
/*!40000 ALTER TABLE `MOUVEMENTS_TITRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `OBLIGATIONS_CONVERTIBLES`
--

DROP TABLE IF EXISTS `OBLIGATIONS_CONVERTIBLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `OBLIGATIONS_CONVERTIBLES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_emission` date NOT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `taux_interet` decimal(5,2) NOT NULL,
  `date_echeance` date NOT NULL,
  `type` enum('OCA','OBSA') NOT NULL,
  `nb_actions_par_obligation` int(11) DEFAULT NULL,
  `prix_conversion` decimal(15,2) DEFAULT NULL,
  `statut` enum('ACTIF','CONVERTI','REMBOURSE','ECHU') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `OBLIGATIONS_CONVERTIBLES`
--

LOCK TABLES `OBLIGATIONS_CONVERTIBLES` WRITE;
/*!40000 ALTER TABLE `OBLIGATIONS_CONVERTIBLES` DISABLE KEYS */;
/*!40000 ALTER TABLE `OBLIGATIONS_CONVERTIBLES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `OPERATIONS_CAPITAL`
--

DROP TABLE IF EXISTS `OPERATIONS_CAPITAL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `OPERATIONS_CAPITAL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_operation` date NOT NULL,
  `type_operation` enum('AUGMENTATION','LIBERATION_COMPENSATION','REDUCTION','TRANSFORMATION') NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `nb_actions` int(11) DEFAULT NULL,
  `prix_unitaire` decimal(15,2) DEFAULT NULL,
  `reference_pv` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `OPERATIONS_CAPITAL`
--

LOCK TABLES `OPERATIONS_CAPITAL` WRITE;
/*!40000 ALTER TABLE `OPERATIONS_CAPITAL` DISABLE KEYS */;
/*!40000 ALTER TABLE `OPERATIONS_CAPITAL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `OPERATIONS_ETRANGERES`
--

DROP TABLE IF EXISTS `OPERATIONS_ETRANGERES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `OPERATIONS_ETRANGERES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_operation` enum('IMPORT','EXPORT') NOT NULL,
  `date_operation` date NOT NULL,
  `reference` varchar(50) NOT NULL,
  `tiers_id` int(11) NOT NULL,
  `montant_devise` decimal(15,2) NOT NULL,
  `code_devise` varchar(3) NOT NULL,
  `taux_originel` decimal(15,4) NOT NULL,
  `montant_fcfa_originel` decimal(15,2) GENERATED ALWAYS AS (`montant_devise` * `taux_originel`) STORED,
  `date_reglement` date DEFAULT NULL,
  `taux_reglement` decimal(15,4) DEFAULT NULL,
  `montant_fcfa_reglement` decimal(15,2) DEFAULT NULL,
  `ecart_change` decimal(15,2) DEFAULT NULL,
  `ecriture_vente_id` int(11) DEFAULT NULL,
  `ecriture_reglement_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tiers_id` (`tiers_id`),
  CONSTRAINT `OPERATIONS_ETRANGERES_ibfk_1` FOREIGN KEY (`tiers_id`) REFERENCES `TIERS` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `OPERATIONS_ETRANGERES`
--

LOCK TABLES `OPERATIONS_ETRANGERES` WRITE;
/*!40000 ALTER TABLE `OPERATIONS_ETRANGERES` DISABLE KEYS */;
/*!40000 ALTER TABLE `OPERATIONS_ETRANGERES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PLAN_COMPTABLE_UEMOA`
--

DROP TABLE IF EXISTS `PLAN_COMPTABLE_UEMOA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PLAN_COMPTABLE_UEMOA` (
  `compte_id` int(11) NOT NULL,
  `intitule_compte` varchar(255) NOT NULL,
  PRIMARY KEY (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PLAN_COMPTABLE_UEMOA`
--

LOCK TABLES `PLAN_COMPTABLE_UEMOA` WRITE;
/*!40000 ALTER TABLE `PLAN_COMPTABLE_UEMOA` DISABLE KEYS */;
INSERT INTO `PLAN_COMPTABLE_UEMOA` VALUES
(12,'Résultats en instance d\'affectation'),
(13,'Investissements spéciaux'),
(14,'Financements affectés'),
(15,'Autres capitaux propres'),
(16,'Provisions réglementées'),
(18,'Capitaux propres assimilés'),
(19,'Subventions d\'investissement'),
(20,'Frais d\'établissement'),
(21,'Immobilisations incorporelles'),
(22,'Terrains'),
(23,'Constructions'),
(24,'Matériel et outillage'),
(25,'Autres immobilisations corporelles'),
(26,'Immobilisations en cours'),
(27,'Immobilisations financières'),
(28,'Amortissements des immobilisations'),
(29,'Dépréciations des immobilisations'),
(30,'Stocks de marchandises'),
(31,'Stocks de matières premières'),
(32,'Stocks d\'emballages'),
(33,'Stocks de produits intermédiaires'),
(34,'Stocks de produits finis'),
(35,'Stocks de fournitures'),
(36,'Stocks de travaux en cours'),
(37,'Approvisionnements et marchandises en cours'),
(38,'Écarts sur stocks'),
(39,'Dépréciations des stocks'),
(40,'Fournisseurs'),
(41,'Clients'),
(42,'Personnel et comptes rattachés'),
(43,'Organismes sociaux'),
(44,'État et collectivités publiques'),
(45,'Groupes et associés'),
(46,'Débiteurs et créditeurs divers'),
(47,'Comptes d\'attente'),
(48,'Comptes de régularisation'),
(49,'Dépréciations des comptes clients'),
(50,'Valeurs à encaisser'),
(51,'Banques et établissements financiers'),
(52,'Banques'),
(53,'Chèques postaux'),
(54,'Caisses'),
(55,'Règies d\'avances'),
(56,'Virements internes'),
(57,'Frais d\'encaissement'),
(58,'Comptes de liaison'),
(59,'Dépréciations des comptes de trésorerie'),
(60,'Achats consommés de marchandises'),
(61,'Services extérieurs'),
(62,'Autres services extérieurs'),
(63,'Impôts et taxes'),
(64,'Charges de personnel'),
(65,'Autres charges de gestion'),
(66,'Charges du personnel'),
(67,'Charges financières'),
(68,'Dotations aux amortissements'),
(69,'Impôts sur les bénéfices'),
(70,'Ventes de marchandises'),
(71,'Production stockée'),
(72,'Production immobilisée'),
(73,'Subventions d\'exploitation'),
(74,'Reprises sur amortissements'),
(75,'Autres produits d\'exploitation'),
(76,'Produits financiers'),
(77,'Produits exceptionnels'),
(78,'Transferts de charges'),
(79,'Participations'),
(80,'Engagements financiers'),
(81,'Amortissements et provisions'),
(82,'Engagements reçus'),
(83,'Cautions'),
(84,'Garanties'),
(85,'Crédit-bail'),
(86,'Contrats de location'),
(87,'Avals et cautionnements'),
(88,'Effets escomptés non échus'),
(89,'Autres engagements'),
(101,'Capital social'),
(106,'Écart de réévaluation'),
(108,'Primes liées aux capitaux propres'),
(109,'Subventions d\'investissement'),
(110,'Report à nouveau (bénéfice)'),
(111,'Report à nouveau (perte)'),
(112,'Résultat net de l\'exercice'),
(113,'Report à nouveau (perte)'),
(114,'Réserves légales'),
(115,'Réserves statutaires'),
(118,'Réserves facultatives'),
(120,'Résultat de l\'exercice'),
(131,'Subventions d\'équipement'),
(132,'Subventions d\'exploitation'),
(151,'Provisions pour charges'),
(152,'Provisions réglementées'),
(161,'Provisions pour litiges'),
(162,'Provisions pour créances douteuses'),
(163,'Amortissements dérogatoires'),
(164,'Provisions pour reconstitution gisement'),
(231,'Constructions sur sol propre'),
(232,'Constructions sur sol d\'autrui'),
(233,'Agencements constructions'),
(235,'Agencements et installations'),
(241,'Matériel informatique'),
(242,'Matériel de bureau'),
(243,'Mobilier de bureau'),
(245,'Mobilier, matériel de transport'),
(253,'Véhicules utilitaires'),
(261,'Titres de participation'),
(262,'Titres immobilisés (TIAP)'),
(263,'Autres titres immobilisés'),
(271,'Actions'),
(272,'Obligations'),
(273,'Bons de souscription'),
(274,'Revenus des titres'),
(276,'Plus-values sur cession'),
(277,'Moins-values sur cession'),
(281,'Amortissements constructions'),
(284,'Amortissements matériel info'),
(285,'Amortissements mobilier'),
(286,'Amortissements véhicules'),
(391,'Dépréciation des stocks de matières premières'),
(392,'Dépréciation des stocks de marchandises'),
(393,'Dépréciation des stocks de produits finis'),
(401,'Fournisseurs d\'immobilisations'),
(404,'Fournisseurs - Effets à payer'),
(408,'Fournisseurs - Factures non parvenues'),
(411,'Clients - Comptes ordinaires'),
(416,'Clients - Effets à recevoir'),
(418,'Clients - Factures à établir'),
(421,'Personnel - Rémunérations dues'),
(428,'Personnel - Charges à payer'),
(431,'Sécurité sociale (IPRES)'),
(432,'Mutualité sociale (CSS)'),
(433,'CSS - Dettes'),
(441,'État - TVA due'),
(442,'État - Impôt sur sociétés'),
(443,'État - Impôts et taxes'),
(457,'Dividendes à payer'),
(466,'Associés - Comptes courants'),
(471,'Comptes d\'attente - Débit'),
(472,'Comptes d\'attente - Crédit'),
(473,'Comptes de régularisation'),
(474,'Comptes transitoires'),
(476,'Gain de change'),
(478,'Comptes de régularisation - produits à recevoir'),
(481,'Charges constatées d\'avance'),
(482,'Produits constatés d\'avance'),
(483,'Charges à payer'),
(484,'Produits à recevoir'),
(485,'Factures non parvenues'),
(486,'Factures à établir'),
(487,'Régularisations diverses'),
(488,'Comptes de contrepassation'),
(491,'Dépréciation des comptes clients'),
(521,'Banques locales'),
(581,'Virements internes'),
(585,'Comptes de régularisation'),
(601,'Achats de marchandises'),
(602,'Achats non stockés de matières'),
(603,'Variations de stocks'),
(604,'Achats d\'études'),
(605,'Achats de fournitures'),
(606,'Fournitures de bureau'),
(608,'Rabais, remises et ristournes obtenus'),
(611,'Sous-traitance générale'),
(613,'Locations'),
(614,'Redevances crédit-bail'),
(615,'Entretien et réparations'),
(616,'Primes d\'assurance'),
(618,'Documentation générale'),
(621,'Personnel extérieur'),
(622,'Honoraires'),
(623,'Rémunérations intermédiaires'),
(624,'Publicité'),
(625,'Déplacements et missions'),
(628,'Divers'),
(631,'Droits d\'enregistrement'),
(635,'Droit d\'immatriculation'),
(637,'Patentes et licences'),
(641,'Rémunérations du personnel'),
(642,'Indemnités'),
(643,'Avantages en nature'),
(644,'Compléments de salaire'),
(645,'Heures supplémentaires'),
(646,'Indemnités diverses'),
(647,'Congés payés'),
(648,'Participation des employés'),
(649,'Charges sociales légalement obligatoires'),
(651,'CNSS - Part employeur'),
(652,'IPRES - Part employeur'),
(653,'CSS - Part employeur'),
(661,'Salaires et traitements'),
(665,'Charges sociales patronales'),
(666,'Perte de change (autre)'),
(671,'Intérêts bancaires'),
(676,'Perte de change'),
(681,'Dotations aux amortissements'),
(695,'Impôt sur les sociétés'),
(701,'Ventes de marchandises'),
(702,'Ventes de produits intermédiaires'),
(703,'Prestations de services'),
(706,'Rabais et remises'),
(766,'Gain de change (autre)'),
(781,'Reprises sur provisions'),
(911,'Intérêts des emprunts'),
(912,'Agios bancaires'),
(913,'Commissions bancaires'),
(914,'Escomptes obtenus'),
(915,'Escomptes accordés'),
(916,'Gains de change'),
(917,'Pertes de change'),
(921,'Écart sur budget ventes'),
(922,'Écart sur budget achats'),
(923,'Écart sur budget trésorerie'),
(924,'Écart sur charges'),
(931,'Prévision ventes'),
(932,'Prévision achats'),
(933,'Prévision trésorerie'),
(934,'Prévision résultats'),
(1011,'Capital souscrit appelé non versé'),
(1012,'Capital souscrit appelé versé'),
(1061,'Écart de réévaluation'),
(1062,'Primes d\'émission'),
(1063,'Primes de fusion'),
(1064,'Primes d\'apport'),
(1065,'Primes de conversion'),
(1181,'Réserve légale'),
(1182,'Réserve statutaire'),
(1183,'Autres réserves'),
(1184,'Report à nouveau créditeur'),
(1185,'Report à nouveau débiteur'),
(1611,'Provision pour dépréciation immobilisations'),
(1612,'Provision pour risques et charges'),
(2100,'Clients'),
(2200,'Banque'),
(2210,'Caisse'),
(2811,'Amortissements terrains'),
(2812,'Amortissements constructions'),
(2813,'Amortissements agencements'),
(2821,'Amortissements matériel outillage'),
(2822,'Amortissements matériel informatique'),
(2823,'Amortissements mobilier'),
(2824,'Amortissements véhicules'),
(4011,'Fournisseurs - Effets à payer'),
(4111,'Clients - Effets à recevoir'),
(4441,'IS - Impôt sur les Sociétés'),
(4442,'IR - Impôt sur le Revenu'),
(4443,'TVA due'),
(4444,'Patente'),
(4445,'Taxe professionnelle'),
(4446,'Contribution CSS'),
(4447,'Cotisations IPRES'),
(4451,'TVA collectée (18%)'),
(4452,'TVA collectée (10%)'),
(4453,'TVA collectée (5%)'),
(4454,'TVA déductible (18%)'),
(4455,'TVA déductible (10%)'),
(4456,'TVA déductible (5%)'),
(4457,'TVA à payer'),
(4458,'Crédit de TVA'),
(4459,'TVA sur immobilisations'),
(4811,'Charges constatées d\'avance - Exploitation'),
(4812,'Charges constatées d\'avance - Financières'),
(4821,'Produits constatés d\'avance - Exploitation'),
(4822,'Produits constatés d\'avance - Financières'),
(4831,'Charges à payer - Fournisseurs'),
(4832,'Charges à payer - Personnel'),
(4841,'Produits à recevoir - Clients'),
(4842,'Produits à recevoir - Divers'),
(5211,'Banque - Effets à l\'escompte'),
(6281,'Frais de port sur ventes'),
(6282,'Emballages perdus'),
(6283,'Frais d\'assurance'),
(6311,'Agios bancaires'),
(6312,'Commissions bancaires'),
(6811,'Dotations aux provisions pour risques'),
(6812,'Dotations aux provisions pour dépréciation'),
(6813,'Dotations aux provisions réglementées');
/*!40000 ALTER TABLE `PLAN_COMPTABLE_UEMOA` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PLAN_FINANCEMENT`
--

DROP TABLE IF EXISTS `PLAN_FINANCEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PLAN_FINANCEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `rubrique` varchar(100) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `type_mouvement` enum('RESSOURCE','EMPLOI') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PLAN_FINANCEMENT`
--

LOCK TABLES `PLAN_FINANCEMENT` WRITE;
/*!40000 ALTER TABLE `PLAN_FINANCEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `PLAN_FINANCEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PORTEFEUILLE_TITRES`
--

DROP TABLE IF EXISTS `PORTEFEUILLE_TITRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PORTEFEUILLE_TITRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_titre` varchar(50) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `type_titre` enum('PARTICIPATION','TIAP','AUTRE_IMMOBILISE','VMP') NOT NULL,
  `date_acquisition` date NOT NULL,
  `valeur_acquisition` decimal(15,2) NOT NULL,
  `quantite` int(11) NOT NULL,
  `valeur_unitaire` decimal(15,2) GENERATED ALWAYS AS (`valeur_acquisition` / `quantite`) STORED,
  `devise` varchar(3) DEFAULT 'XOF',
  `frais_acquisition` decimal(15,2) DEFAULT 0.00,
  `valeur_comptable` decimal(15,2) GENERATED ALWAYS AS (`valeur_acquisition` + `frais_acquisition`) STORED,
  `statut` enum('ACTIF','CESSED','DEPRECIE') DEFAULT 'ACTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_titre` (`code_titre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PORTEFEUILLE_TITRES`
--

LOCK TABLES `PORTEFEUILLE_TITRES` WRITE;
/*!40000 ALTER TABLE `PORTEFEUILLE_TITRES` DISABLE KEYS */;
INSERT INTO `PORTEFEUILLE_TITRES` VALUES
(1,'ACT-001','Actions ORANGE Sénégal','VMP','2026-01-15',5000000.00,1000,5000.00,'XOF',25000.00,5025000.00,'ACTIF','2026-05-12 11:10:04'),
(2,'ACT-002','Actions SONATEL','PARTICIPATION','2025-06-10',15000000.00,500,30000.00,'XOF',150000.00,15150000.00,'ACTIF','2026-05-12 11:10:04'),
(3,'OBL-001','Obligations État Sénégal','TIAP','2026-02-20',10000000.00,100,100000.00,'XOF',50000.00,10050000.00,'ACTIF','2026-05-12 11:10:04'),
(4,'ACT-003','Actions BOA Sénégal','VMP','2026-03-05',3000000.00,300,10000.00,'XOF',15000.00,3015000.00,'ACTIF','2026-05-12 11:10:04');
/*!40000 ALTER TABLE `PORTEFEUILLE_TITRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PREVISIONS_ALEATOIRES`
--

DROP TABLE IF EXISTS `PREVISIONS_ALEATOIRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PREVISIONS_ALEATOIRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `scenario` enum('OPTIMISTE','PESSIMISTE','REALISTE') NOT NULL,
  `probabilite` decimal(3,0) NOT NULL,
  `chiffre_affaires` decimal(15,2) NOT NULL,
  `cout_variable` decimal(15,2) NOT NULL,
  `cout_fixe` decimal(15,2) NOT NULL,
  `resultat` decimal(15,2) GENERATED ALWAYS AS (`chiffre_affaires` - `cout_variable` - `cout_fixe`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PREVISIONS_ALEATOIRES`
--

LOCK TABLES `PREVISIONS_ALEATOIRES` WRITE;
/*!40000 ALTER TABLE `PREVISIONS_ALEATOIRES` DISABLE KEYS */;
/*!40000 ALTER TABLE `PREVISIONS_ALEATOIRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PREVISIONS_BFR`
--

DROP TABLE IF EXISTS `PREVISIONS_BFR`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PREVISIONS_BFR` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `chiffre_affaires` decimal(15,2) NOT NULL,
  `delai_creances` int(11) NOT NULL,
  `delai_dettes` int(11) NOT NULL,
  `rotation_stocks` int(11) NOT NULL,
  `bfr_previsionnel` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PREVISIONS_BFR`
--

LOCK TABLES `PREVISIONS_BFR` WRITE;
/*!40000 ALTER TABLE `PREVISIONS_BFR` DISABLE KEYS */;
/*!40000 ALTER TABLE `PREVISIONS_BFR` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PREVISIONS_BFR_DETAIL`
--

DROP TABLE IF EXISTS `PREVISIONS_BFR_DETAIL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PREVISIONS_BFR_DETAIL` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `chiffre_affaires` decimal(15,2) NOT NULL,
  `delai_creances` int(11) NOT NULL,
  `delai_dettes` int(11) NOT NULL,
  `rotation_stocks` int(11) NOT NULL,
  `bfre_previsionnel` decimal(15,2) DEFAULT NULL,
  `bfre_fixe` decimal(15,2) DEFAULT NULL,
  `bfre_variable` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PREVISIONS_BFR_DETAIL`
--

LOCK TABLES `PREVISIONS_BFR_DETAIL` WRITE;
/*!40000 ALTER TABLE `PREVISIONS_BFR_DETAIL` DISABLE KEYS */;
/*!40000 ALTER TABLE `PREVISIONS_BFR_DETAIL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PREVISIONS_TRESORERIE`
--

DROP TABLE IF EXISTS `PREVISIONS_TRESORERIE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PREVISIONS_TRESORERIE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_prevision` date NOT NULL,
  `type_flux` enum('ENCAISSEMENT','DECAISSEMENT','SOLDE') NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `probabilite` decimal(3,0) DEFAULT 100,
  `ecriture_liee` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PREVISIONS_TRESORERIE`
--

LOCK TABLES `PREVISIONS_TRESORERIE` WRITE;
/*!40000 ALTER TABLE `PREVISIONS_TRESORERIE` DISABLE KEYS */;
/*!40000 ALTER TABLE `PREVISIONS_TRESORERIE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PREVISIONS_TRESORERIE_GLOBALE`
--

DROP TABLE IF EXISTS `PREVISIONS_TRESORERIE_GLOBALE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PREVISIONS_TRESORERIE_GLOBALE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `encaissements_prevu` decimal(15,2) DEFAULT NULL,
  `encaissements_reel` decimal(15,2) DEFAULT NULL,
  `decaissements_prevu` decimal(15,2) DEFAULT NULL,
  `decaissements_reel` decimal(15,2) DEFAULT NULL,
  `solde_debut` decimal(15,2) DEFAULT NULL,
  `solde_fin` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_prevision` (`exercice`,`mois`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PREVISIONS_TRESORERIE_GLOBALE`
--

LOCK TABLES `PREVISIONS_TRESORERIE_GLOBALE` WRITE;
/*!40000 ALTER TABLE `PREVISIONS_TRESORERIE_GLOBALE` DISABLE KEYS */;
/*!40000 ALTER TABLE `PREVISIONS_TRESORERIE_GLOBALE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PREVISIONS_VENTES`
--

DROP TABLE IF EXISTS `PREVISIONS_VENTES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PREVISIONS_VENTES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_prevision` date NOT NULL,
  `mois` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `quantite_prevue` int(11) DEFAULT NULL,
  `ca_prevu` decimal(15,2) DEFAULT NULL,
  `methode` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PREVISIONS_VENTES`
--

LOCK TABLES `PREVISIONS_VENTES` WRITE;
/*!40000 ALTER TABLE `PREVISIONS_VENTES` DISABLE KEYS */;
/*!40000 ALTER TABLE `PREVISIONS_VENTES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PRODUITS_CAE`
--

DROP TABLE IF EXISTS `PRODUITS_CAE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PRODUITS_CAE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `quantite_produite` int(11) DEFAULT NULL,
  `prix_vente` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PRODUITS_CAE`
--

LOCK TABLES `PRODUITS_CAE` WRITE;
/*!40000 ALTER TABLE `PRODUITS_CAE` DISABLE KEYS */;
INSERT INTO `PRODUITS_CAE` VALUES
(1,'P001','Produit Premium',1000,25000.00,'2026-05-16 19:03:35'),
(2,'P002','Produit Standard',2000,15000.00,'2026-05-16 19:03:35'),
(3,'P003','Produit Économique',3000,10000.00,'2026-05-16 19:03:35');
/*!40000 ALTER TABLE `PRODUITS_CAE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROJETS_INVESTISSEMENT`
--

DROP TABLE IF EXISTS `PROJETS_INVESTISSEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PROJETS_INVESTISSEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `duree_vie` int(11) NOT NULL,
  `taux_actualisation` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROJETS_INVESTISSEMENT`
--

LOCK TABLES `PROJETS_INVESTISSEMENT` WRITE;
/*!40000 ALTER TABLE `PROJETS_INVESTISSEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `PROJETS_INVESTISSEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROVISIONS`
--

DROP TABLE IF EXISTS `PROVISIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PROVISIONS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_provision` date NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `compte_dotations` int(11) NOT NULL,
  `compte_provisions` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `type_provision` enum('CREANCES_DOUTEUSES','LITIGES','AMORTISSEMENTS_DEROGATOIRES','RECONSTITUTION_GISEMENT') NOT NULL,
  `duree_previsionnelle` int(11) DEFAULT NULL,
  `reprise` tinyint(4) DEFAULT 0,
  `date_reprise` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROVISIONS`
--

LOCK TABLES `PROVISIONS` WRITE;
/*!40000 ALTER TABLE `PROVISIONS` DISABLE KEYS */;
/*!40000 ALTER TABLE `PROVISIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PROVISIONS_DEPRECIATIONS`
--

DROP TABLE IF EXISTS `PROVISIONS_DEPRECIATIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PROVISIONS_DEPRECIATIONS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_constitution` date NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `type_provision` enum('RISQUES','CHARGES','DEPRECIATION_ACTIF','REGLEMENTEE') NOT NULL,
  `sous_type` varchar(100) DEFAULT NULL,
  `compte_dotation` int(11) NOT NULL,
  `compte_provision` int(11) NOT NULL,
  `montant_initial` decimal(15,2) NOT NULL,
  `montant_actuel` decimal(15,2) NOT NULL,
  `date_ajustement` date DEFAULT NULL,
  `montant_ajustement` decimal(15,2) DEFAULT NULL,
  `date_reprise` date DEFAULT NULL,
  `montant_reprise` decimal(15,2) DEFAULT NULL,
  `justificatif` text DEFAULT NULL,
  `statut` enum('ACTIVE','AJUSTEE','REPRISE','ANNULEE') DEFAULT 'ACTIVE',
  `ecriture_creation_id` int(11) DEFAULT NULL,
  `ecriture_ajustement_id` int(11) DEFAULT NULL,
  `ecriture_reprise_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_dotation` (`compte_dotation`),
  KEY `compte_provision` (`compte_provision`),
  KEY `idx_provisions_type` (`type_provision`),
  KEY `idx_provisions_statut` (`statut`),
  CONSTRAINT `PROVISIONS_DEPRECIATIONS_ibfk_1` FOREIGN KEY (`compte_dotation`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`),
  CONSTRAINT `PROVISIONS_DEPRECIATIONS_ibfk_2` FOREIGN KEY (`compte_provision`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PROVISIONS_DEPRECIATIONS`
--

LOCK TABLES `PROVISIONS_DEPRECIATIONS` WRITE;
/*!40000 ALTER TABLE `PROVISIONS_DEPRECIATIONS` DISABLE KEYS */;
/*!40000 ALTER TABLE `PROVISIONS_DEPRECIATIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RATIOS_FINANCIERS`
--

DROP TABLE IF EXISTS `RATIOS_FINANCIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `RATIOS_FINANCIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `date_calcul` date NOT NULL,
  `ratio_liquidite_generale` decimal(10,2) DEFAULT NULL,
  `ratio_liquidite_reduite` decimal(10,2) DEFAULT NULL,
  `ratio_liquidite_immediate` decimal(10,2) DEFAULT NULL,
  `ratio_dettes_capitaux` decimal(10,2) DEFAULT NULL,
  `ratio_autonomie_financiere` decimal(10,2) DEFAULT NULL,
  `ratio_financement_actif` decimal(10,2) DEFAULT NULL,
  `rentabilite_economique` decimal(10,2) DEFAULT NULL,
  `rentabilite_financiere` decimal(10,2) DEFAULT NULL,
  `rentabilite_commerciale` decimal(10,2) DEFAULT NULL,
  `rotation_stocks` decimal(10,2) DEFAULT NULL,
  `delai_paiement_fournisseurs` decimal(10,2) DEFAULT NULL,
  `delai_encaissement_clients` decimal(10,2) DEFAULT NULL,
  `besoin_fonds_roulement` decimal(15,2) DEFAULT NULL,
  `fonds_roulement` decimal(15,2) DEFAULT NULL,
  `tresorerie_nette` decimal(15,2) DEFAULT NULL,
  `capacite_autofinancement` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `exercice` (`exercice`),
  KEY `idx_ratios_exercice` (`exercice`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RATIOS_FINANCIERS`
--

LOCK TABLES `RATIOS_FINANCIERS` WRITE;
/*!40000 ALTER TABLE `RATIOS_FINANCIERS` DISABLE KEYS */;
INSERT INTO `RATIOS_FINANCIERS` VALUES
(1,2026,'2026-05-26',0.00,0.00,0.00,0.00,0.00,0.00,0.00,-3.93,-13.60,NULL,NULL,NULL,0.00,50000000.00,50000000.00,11670000.00,'2026-05-06 18:59:51','2026-05-26 21:18:52');
/*!40000 ALTER TABLE `RATIOS_FINANCIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RATIOS_LIQUIDITE`
--

DROP TABLE IF EXISTS `RATIOS_LIQUIDITE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `RATIOS_LIQUIDITE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `ratio_liquidite_generale` decimal(10,2) DEFAULT NULL,
  `ratio_liquidite_reduite` decimal(10,2) DEFAULT NULL,
  `ratio_liquidite_immediate` decimal(10,2) DEFAULT NULL,
  `ratio_endettement` decimal(10,2) DEFAULT NULL,
  `ratio_autonomie_financiere` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RATIOS_LIQUIDITE`
--

LOCK TABLES `RATIOS_LIQUIDITE` WRITE;
/*!40000 ALTER TABLE `RATIOS_LIQUIDITE` DISABLE KEYS */;
/*!40000 ALTER TABLE `RATIOS_LIQUIDITE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REGULARISATIONS`
--

DROP TABLE IF EXISTS `REGULARISATIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `REGULARISATIONS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_regul` date NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `compte_charge_id` int(11) DEFAULT NULL,
  `compte_produit_id` int(11) DEFAULT NULL,
  `compte_tiers_id` int(11) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `type_regul` enum('CHARGE_CONSTATE_AVANCE','PRODUIT_CONSTATE_AVANCE','CHARGES_A_PAYER','PRODUITS_A_RECEVOIR','FACTURE_NON_PARVENUE') NOT NULL,
  `exercice` int(11) NOT NULL,
  `contrepassation` tinyint(4) DEFAULT 0,
  `date_contrepassation` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_regul_exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REGULARISATIONS`
--

LOCK TABLES `REGULARISATIONS` WRITE;
/*!40000 ALTER TABLE `REGULARISATIONS` DISABLE KEYS */;
/*!40000 ALTER TABLE `REGULARISATIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REGULARISATIONS_CONTRATS`
--

DROP TABLE IF EXISTS `REGULARISATIONS_CONTRATS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `REGULARISATIONS_CONTRATS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contrat_id` int(11) NOT NULL,
  `date_regul` date NOT NULL,
  `type_regul` enum('FACTURE','AVOIR','NOTE_CREDIT','PAIEMENT','REGULARISATION') NOT NULL,
  `reference_piece` varchar(50) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `ecriture_liee` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `contrat_id` (`contrat_id`),
  KEY `ecriture_liee` (`ecriture_liee`),
  CONSTRAINT `REGULARISATIONS_CONTRATS_ibfk_1` FOREIGN KEY (`contrat_id`) REFERENCES `CONTRATS` (`id`) ON DELETE CASCADE,
  CONSTRAINT `REGULARISATIONS_CONTRATS_ibfk_2` FOREIGN KEY (`ecriture_liee`) REFERENCES `ECRITURES_COMPTABLES` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REGULARISATIONS_CONTRATS`
--

LOCK TABLES `REGULARISATIONS_CONTRATS` WRITE;
/*!40000 ALTER TABLE `REGULARISATIONS_CONTRATS` DISABLE KEYS */;
/*!40000 ALTER TABLE `REGULARISATIONS_CONTRATS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REPORT_NOUVEAU`
--

DROP TABLE IF EXISTS `REPORT_NOUVEAU`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `REPORT_NOUVEAU` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `type_compte` enum('BENEFICE','PERTE','RESERVE') NOT NULL,
  `compte_id` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `affectation` enum('RESERVE','DISTRIBUTION','REPORT') DEFAULT 'REPORT',
  `date_report` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_report_exercice` (`exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REPORT_NOUVEAU`
--

LOCK TABLES `REPORT_NOUVEAU` WRITE;
/*!40000 ALTER TABLE `REPORT_NOUVEAU` DISABLE KEYS */;
/*!40000 ALTER TABLE `REPORT_NOUVEAU` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `REPORT_NOUVEAU_DETAILLE`
--

DROP TABLE IF EXISTS `REPORT_NOUVEAU_DETAILLE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `REPORT_NOUVEAU_DETAILLE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `type_mouvement` enum('BENEFICE','PERTE','DISTRIBUTION','RESERVE','IMPOSITION') NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `compte_impacte` int(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `date_operation` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_impacte` (`compte_impacte`),
  KEY `idx_report_exercice` (`exercice`),
  CONSTRAINT `REPORT_NOUVEAU_DETAILLE_ibfk_1` FOREIGN KEY (`compte_impacte`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `REPORT_NOUVEAU_DETAILLE`
--

LOCK TABLES `REPORT_NOUVEAU_DETAILLE` WRITE;
/*!40000 ALTER TABLE `REPORT_NOUVEAU_DETAILLE` DISABLE KEYS */;
/*!40000 ALTER TABLE `REPORT_NOUVEAU_DETAILLE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RESULTATS_ANALYSE`
--

DROP TABLE IF EXISTS `RESULTATS_ANALYSE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `RESULTATS_ANALYSE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `seuil_rentabilite` decimal(15,2) DEFAULT NULL,
  `marge_securite` decimal(15,2) DEFAULT NULL,
  `indice_securite` decimal(5,2) DEFAULT NULL,
  `levier_operationnel` decimal(5,2) DEFAULT NULL,
  `ebe` decimal(15,2) DEFAULT NULL,
  `caf` decimal(15,2) DEFAULT NULL,
  `date_calcul` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RESULTATS_ANALYSE`
--

LOCK TABLES `RESULTATS_ANALYSE` WRITE;
/*!40000 ALTER TABLE `RESULTATS_ANALYSE` DISABLE KEYS */;
INSERT INTO `RESULTATS_ANALYSE` VALUES
(1,2026,36991438.36,20008561.64,35.10,2.85,11400000.00,11350000.00,'2026-05-14','2026-05-14 13:56:18');
/*!40000 ALTER TABLE `RESULTATS_ANALYSE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `RETRAITEMENTS_BILAN`
--

DROP TABLE IF EXISTS `RETRAITEMENTS_BILAN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `RETRAITEMENTS_BILAN` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `type_retraitement` enum('ACTIF','PASSIF') NOT NULL,
  `poste_original` varchar(100) NOT NULL,
  `poste_retraite` varchar(100) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `RETRAITEMENTS_BILAN`
--

LOCK TABLES `RETRAITEMENTS_BILAN` WRITE;
/*!40000 ALTER TABLE `RETRAITEMENTS_BILAN` DISABLE KEYS */;
/*!40000 ALTER TABLE `RETRAITEMENTS_BILAN` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SALARIES`
--

DROP TABLE IF EXISTS `SALARIES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SALARIES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricule` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `fonction` varchar(100) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `date_embauche` date NOT NULL,
  `date_sortie` date DEFAULT NULL,
  `statut` enum('ACTIF','SORTI','CONGE') DEFAULT 'ACTIF',
  `situation_familiale` enum('CELIBATAIRE','MARIE','DIVORCE','VEUF') DEFAULT 'CELIBATAIRE',
  `nombre_enfants` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SALARIES`
--

LOCK TABLES `SALARIES` WRITE;
/*!40000 ALTER TABLE `SALARIES` DISABLE KEYS */;
/*!40000 ALTER TABLE `SALARIES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SCENARIOS_FINANCIERS`
--

DROP TABLE IF EXISTS `SCENARIOS_FINANCIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SCENARIOS_FINANCIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(100) NOT NULL,
  `date_creation` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SCENARIOS_FINANCIERS`
--

LOCK TABLES `SCENARIOS_FINANCIERS` WRITE;
/*!40000 ALTER TABLE `SCENARIOS_FINANCIERS` DISABLE KEYS */;
/*!40000 ALTER TABLE `SCENARIOS_FINANCIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SCENARIOS_INVESTISSEMENT`
--

DROP TABLE IF EXISTS `SCENARIOS_INVESTISSEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SCENARIOS_INVESTISSEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_id` int(11) NOT NULL,
  `scenario` enum('OPTIMISTE','REALISTE','PESSIMISTE') NOT NULL,
  `probabilite` decimal(5,2) NOT NULL,
  `flux_annee1` decimal(15,2) DEFAULT NULL,
  `flux_annee2` decimal(15,2) DEFAULT NULL,
  `flux_annee3` decimal(15,2) DEFAULT NULL,
  `flux_annee4` decimal(15,2) DEFAULT NULL,
  `flux_annee5` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projet_id` (`projet_id`),
  CONSTRAINT `SCENARIOS_INVESTISSEMENT_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `PROJETS_INVESTISSEMENT` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SCENARIOS_INVESTISSEMENT`
--

LOCK TABLES `SCENARIOS_INVESTISSEMENT` WRITE;
/*!40000 ALTER TABLE `SCENARIOS_INVESTISSEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `SCENARIOS_INVESTISSEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SCORES_FINANCIERS`
--

DROP TABLE IF EXISTS `SCORES_FINANCIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SCORES_FINANCIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `score_z_altman` decimal(10,4) DEFAULT NULL,
  `score_conjoncture` decimal(10,4) DEFAULT NULL,
  `interpretation` varchar(255) DEFAULT NULL,
  `risque` enum('FAIBLE','MOYEN','ELEVE','CRITIQUE') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SCORES_FINANCIERS`
--

LOCK TABLES `SCORES_FINANCIERS` WRITE;
/*!40000 ALTER TABLE `SCORES_FINANCIERS` DISABLE KEYS */;
INSERT INTO `SCORES_FINANCIERS` VALUES
(1,2026,-0.4435,NULL,'RISQUE DE FAILLITE TRÈS ÉLEVÉ (ZONE CRITIQUE)','CRITIQUE','2026-05-27 07:31:54');
/*!40000 ALTER TABLE `SCORES_FINANCIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SECTIONS_ANALYTIQUES`
--

DROP TABLE IF EXISTS `SECTIONS_ANALYTIQUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SECTIONS_ANALYTIQUES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `type_section` enum('PROJET','DEPARTEMENT','PRODUIT','REGION','SERVICE') NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `actif` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `SECTIONS_ANALYTIQUES_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `SECTIONS_ANALYTIQUES` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SECTIONS_ANALYTIQUES`
--

LOCK TABLES `SECTIONS_ANALYTIQUES` WRITE;
/*!40000 ALTER TABLE `SECTIONS_ANALYTIQUES` DISABLE KEYS */;
INSERT INTO `SECTIONS_ANALYTIQUES` VALUES
(11,'PROJ01','Projet Construction R+3','PROJET',NULL,1,'2026-05-11 19:11:44'),
(12,'PROJ02','Projet Rénovation','PROJET',NULL,1,'2026-05-11 19:11:44'),
(13,'DEP01','Direction Commerciale','DEPARTEMENT',NULL,1,'2026-05-11 19:11:44'),
(14,'DEP02','Direction Technique','DEPARTEMENT',NULL,1,'2026-05-11 19:11:44'),
(15,'PROD01','Produit Premium','PRODUIT',NULL,1,'2026-05-11 19:11:44'),
(16,'PROD02','Produit Standard','PRODUIT',NULL,1,'2026-05-11 19:11:44'),
(17,'REG_DK','Région Dakar','REGION',NULL,1,'2026-05-11 19:11:44'),
(18,'REG_TL','Région Thiès','REGION',NULL,1,'2026-05-11 19:11:44');
/*!40000 ALTER TABLE `SECTIONS_ANALYTIQUES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SEUILS_RENTABILITE`
--

DROP TABLE IF EXISTS `SEUILS_RENTABILITE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SEUILS_RENTABILITE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `chiffre_affaires` decimal(15,2) DEFAULT NULL,
  `charges_variables` decimal(15,2) DEFAULT NULL,
  `charges_fixes` decimal(15,2) DEFAULT NULL,
  `marge_sur_cout_variable` decimal(15,2) DEFAULT NULL,
  `seuil_rentabilite` decimal(15,2) DEFAULT NULL,
  `point_mort_jours` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SEUILS_RENTABILITE`
--

LOCK TABLES `SEUILS_RENTABILITE` WRITE;
/*!40000 ALTER TABLE `SEUILS_RENTABILITE` DISABLE KEYS */;
/*!40000 ALTER TABLE `SEUILS_RENTABILITE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SIMULATIONS_MONTE_CARLO`
--

DROP TABLE IF EXISTS `SIMULATIONS_MONTE_CARLO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SIMULATIONS_MONTE_CARLO` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projet_id` int(11) NOT NULL,
  `iteration` int(11) NOT NULL,
  `van_simulee` decimal(15,2) DEFAULT NULL,
  `tri_simule` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `projet_id` (`projet_id`),
  CONSTRAINT `SIMULATIONS_MONTE_CARLO_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `PROJETS_INVESTISSEMENT` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SIMULATIONS_MONTE_CARLO`
--

LOCK TABLES `SIMULATIONS_MONTE_CARLO` WRITE;
/*!40000 ALTER TABLE `SIMULATIONS_MONTE_CARLO` DISABLE KEYS */;
/*!40000 ALTER TABLE `SIMULATIONS_MONTE_CARLO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SOCIETES`
--

DROP TABLE IF EXISTS `SOCIETES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SOCIETES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `raison_sociale` varchar(200) NOT NULL,
  `base_donnee` varchar(100) DEFAULT NULL,
  `pourcentage_controle` decimal(5,2) DEFAULT NULL,
  `pourcentage_interet` decimal(5,2) DEFAULT NULL,
  `date_integration` date DEFAULT NULL,
  `methode_consolidation` enum('INTEGRATION_GLOBALE','INTEGRATION_PROPORTIONNELLE','MISE_EQUIVALENCE') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `groupe_id` (`groupe_id`),
  CONSTRAINT `SOCIETES_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `GROUPES_SOCIETES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SOCIETES`
--

LOCK TABLES `SOCIETES` WRITE;
/*!40000 ALTER TABLE `SOCIETES` DISABLE KEYS */;
/*!40000 ALTER TABLE `SOCIETES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `STOCK_DEPOT`
--

DROP TABLE IF EXISTS `STOCK_DEPOT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `STOCK_DEPOT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `depot_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 0,
  `valeur_unitaire` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `depot_id` (`depot_id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `STOCK_DEPOT_ibfk_1` FOREIGN KEY (`depot_id`) REFERENCES `DEPOTS_STOCK` (`id`),
  CONSTRAINT `STOCK_DEPOT_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `STOCK_DEPOT`
--

LOCK TABLES `STOCK_DEPOT` WRITE;
/*!40000 ALTER TABLE `STOCK_DEPOT` DISABLE KEYS */;
/*!40000 ALTER TABLE `STOCK_DEPOT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `STOCK_OPTIONS`
--

DROP TABLE IF EXISTS `STOCK_OPTIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `STOCK_OPTIONS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_attribution` date NOT NULL,
  `date_levier` date DEFAULT NULL,
  `nb_options` int(11) NOT NULL,
  `prix_exercice` decimal(15,2) NOT NULL,
  `valeur_action_levier` decimal(15,2) DEFAULT NULL,
  `gain_acquisition` decimal(15,2) DEFAULT NULL,
  `beneficiaire` varchar(100) DEFAULT NULL,
  `statut` enum('ATTRIBUE','ACQUIS','LEVE','EXPIRE','ANNULE') DEFAULT 'ATTRIBUE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `STOCK_OPTIONS`
--

LOCK TABLES `STOCK_OPTIONS` WRITE;
/*!40000 ALTER TABLE `STOCK_OPTIONS` DISABLE KEYS */;
/*!40000 ALTER TABLE `STOCK_OPTIONS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SYNTHESES_BALANCE`
--

DROP TABLE IF EXISTS `SYNTHESES_BALANCE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `SYNTHESES_BALANCE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compte_id` int(11) DEFAULT NULL,
  `mouvement_debit` decimal(15,2) DEFAULT 0.00,
  `mouvement_credit` decimal(15,2) DEFAULT 0.00,
  `solde_debiteur` decimal(15,2) DEFAULT 0.00,
  `solde_crediteur` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SYNTHESES_BALANCE`
--

LOCK TABLES `SYNTHESES_BALANCE` WRITE;
/*!40000 ALTER TABLE `SYNTHESES_BALANCE` DISABLE KEYS */;
/*!40000 ALTER TABLE `SYNTHESES_BALANCE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TABLEAUX_DE_BORD`
--

DROP TABLE IF EXISTS `TABLEAUX_DE_BORD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `TABLEAUX_DE_BORD` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_tableau` date NOT NULL,
  `type_tableau` enum('TRESORERIE','GESTION','FINANCIER','BUDGETAIRE') NOT NULL,
  `indicateur` varchar(100) NOT NULL,
  `valeur` decimal(15,2) NOT NULL,
  `objectif` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TABLEAUX_DE_BORD`
--

LOCK TABLES `TABLEAUX_DE_BORD` WRITE;
/*!40000 ALTER TABLE `TABLEAUX_DE_BORD` DISABLE KEYS */;
/*!40000 ALTER TABLE `TABLEAUX_DE_BORD` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TABLEAU_FINANCEMENT`
--

DROP TABLE IF EXISTS `TABLEAU_FINANCEMENT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `TABLEAU_FINANCEMENT` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `rubrique` varchar(100) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `type_mouvement` enum('RESSOURCE','EMPLOI') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TABLEAU_FINANCEMENT`
--

LOCK TABLES `TABLEAU_FINANCEMENT` WRITE;
/*!40000 ALTER TABLE `TABLEAU_FINANCEMENT` DISABLE KEYS */;
/*!40000 ALTER TABLE `TABLEAU_FINANCEMENT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TAUX_ACTUALISATION`
--

DROP TABLE IF EXISTS `TAUX_ACTUALISATION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `TAUX_ACTUALISATION` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `taux_sans_risque` decimal(5,2) DEFAULT NULL,
  `prime_risque_pays` decimal(5,2) DEFAULT NULL,
  `prime_risque_secteur` decimal(5,2) DEFAULT NULL,
  `prime_taille_entreprise` decimal(5,2) DEFAULT NULL,
  `beta_entreprise` decimal(5,2) DEFAULT NULL,
  `taux_actualisation` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TAUX_ACTUALISATION`
--

LOCK TABLES `TAUX_ACTUALISATION` WRITE;
/*!40000 ALTER TABLE `TAUX_ACTUALISATION` DISABLE KEYS */;
/*!40000 ALTER TABLE `TAUX_ACTUALISATION` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TIERS`
--

DROP TABLE IF EXISTS `TIERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `TIERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `type` enum('CLIENT','FOURNISSEUR','BANQUE','AUTRE') NOT NULL,
  `raison_sociale` varchar(200) NOT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `numero_compte` int(11) NOT NULL,
  `compte_contrepartie` int(11) DEFAULT NULL,
  `identifiant_fiscal` varchar(50) DEFAULT NULL,
  `registre_commerce` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `numero_compte` (`numero_compte`),
  KEY `idx_tiers_code` (`code`),
  CONSTRAINT `TIERS_ibfk_1` FOREIGN KEY (`numero_compte`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TIERS`
--

LOCK TABLES `TIERS` WRITE;
/*!40000 ALTER TABLE `TIERS` DISABLE KEYS */;
INSERT INTO `TIERS` VALUES
(5,'Cl01','CLIENT','Omega informatique','Dakar','776542803','sibymohamed24@gmail.com',411,NULL,'Bif','Er122','2026-05-08 01:44:29','2026-05-08 01:44:29'),
(6,'CLI001','CLIENT','Client Alpha','Dakar','77 111 11 11','alpha@mail.com',411,NULL,NULL,NULL,'2026-05-19 15:09:10','2026-05-19 15:09:10'),
(7,'CLI002','CLIENT','Client Bêta','Thiès','77 222 22 22','beta@mail.com',411,NULL,NULL,NULL,'2026-05-19 15:09:10','2026-05-19 15:09:10'),
(8,'CLI003','CLIENT','Client Gamma','Saint-Louis','77 333 33 33','gamma@mail.com',411,NULL,NULL,NULL,'2026-05-19 15:09:10','2026-05-19 15:09:10'),
(9,'FOUR001','FOURNISSEUR','Fournisseur X','Dakar','78 111 11 11','fourX@mail.com',401,NULL,NULL,NULL,'2026-05-19 15:09:10','2026-05-19 15:09:10'),
(10,'FOUR002','FOURNISSEUR','Fournisseur Y','Thiès','78 222 22 22','fourY@mail.com',401,NULL,NULL,NULL,'2026-05-19 15:09:10','2026-05-19 15:09:10');
/*!40000 ALTER TABLE `TIERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `TRESORERIE_PREVISIONNELLE`
--

DROP TABLE IF EXISTS `TRESORERIE_PREVISIONNELLE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `TRESORERIE_PREVISIONNELLE` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_prevision` date NOT NULL,
  `type_flux` enum('ENCAISSEMENT','DECAISSEMENT') NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `probabilite` decimal(3,0) DEFAULT 100,
  `date_effet` date NOT NULL,
  `recurring` enum('UNIQUE','MENSUEL','TRIMESTRIEL','ANNUEL') DEFAULT 'UNIQUE',
  `recurring_end` date DEFAULT NULL,
  `ecriture_liee` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ecriture_liee` (`ecriture_liee`),
  KEY `idx_treso_date` (`date_prevision`),
  CONSTRAINT `TRESORERIE_PREVISIONNELLE_ibfk_1` FOREIGN KEY (`ecriture_liee`) REFERENCES `ECRITURES_COMPTABLES` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `TRESORERIE_PREVISIONNELLE`
--

LOCK TABLES `TRESORERIE_PREVISIONNELLE` WRITE;
/*!40000 ALTER TABLE `TRESORERIE_PREVISIONNELLE` DISABLE KEYS */;
/*!40000 ALTER TABLE `TRESORERIE_PREVISIONNELLE` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `USERS`
--

DROP TABLE IF EXISTS `USERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `USERS` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ADMIN','COMPTABLE','LECTEUR') DEFAULT 'LECTEUR',
  `nom` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `USERS`
--

LOCK TABLES `USERS` WRITE;
/*!40000 ALTER TABLE `USERS` DISABLE KEYS */;
INSERT INTO `USERS` VALUES
(5,'admin@synthesepro.com','$2y$10$7KHU1Qf6XOfAbc8UnirmIeLrI5tD5Ji2DvTsnHj5jmAm8ppQ89lpq','ADMIN','Administrateur','2026-05-05 12:18:25',NULL),
(6,'comptable@omega-ci.com','$2y$10$7KHU1Qf6XOfAbc8UnirmIeLrI5tD5Ji2DvTsnHj5jmAm8ppQ89lpq','COMPTABLE','Expert Comptable','2026-05-05 12:18:25',NULL),
(7,'lecteur@omega-ci.com','$2y$10$7KHU1Qf6XOfAbc8UnirmIeLrI5tD5Ji2DvTsnHj5jmAm8ppQ89lpq','LECTEUR','Consultant','2026-05-05 12:18:25',NULL);
/*!40000 ALTER TABLE `USERS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VALEURS_CUMP`
--

DROP TABLE IF EXISTS `VALEURS_CUMP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `VALEURS_CUMP` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `date_calcul` date NOT NULL,
  `quantite_totale` int(11) NOT NULL,
  `valeur_totale` decimal(15,2) NOT NULL,
  `cump_unitaire` decimal(15,2) GENERATED ALWAYS AS (`valeur_totale` / `quantite_totale`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `VALEURS_CUMP_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VALEURS_CUMP`
--

LOCK TABLES `VALEURS_CUMP` WRITE;
/*!40000 ALTER TABLE `VALEURS_CUMP` DISABLE KEYS */;
/*!40000 ALTER TABLE `VALEURS_CUMP` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VARIATIONS_STOCK`
--

DROP TABLE IF EXISTS `VARIATIONS_STOCK`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `VARIATIONS_STOCK` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `date_variation` date NOT NULL,
  `stock_debut` int(11) NOT NULL,
  `entrees` int(11) NOT NULL,
  `sorties` int(11) NOT NULL,
  `stock_fin` int(11) NOT NULL,
  `valeur_variation` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `VARIATIONS_STOCK_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `ARTICLES_STOCK` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VARIATIONS_STOCK`
--

LOCK TABLES `VARIATIONS_STOCK` WRITE;
/*!40000 ALTER TABLE `VARIATIONS_STOCK` DISABLE KEYS */;
/*!40000 ALTER TABLE `VARIATIONS_STOCK` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VARIATION_CAPITAUX_PROPRES`
--

DROP TABLE IF EXISTS `VARIATION_CAPITAUX_PROPRES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `VARIATION_CAPITAUX_PROPRES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `compte_id` int(11) NOT NULL,
  `type_variation` enum('AUGMENTATION','DIMINUTION') NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `origine` enum('RESULTAT','APPORT','RESERVE','DISTRIBUTION','AUTRE') NOT NULL,
  `date_operation` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `compte_id` (`compte_id`),
  KEY `idx_capitaux_exercice` (`exercice`),
  CONSTRAINT `VARIATION_CAPITAUX_PROPRES_ibfk_1` FOREIGN KEY (`compte_id`) REFERENCES `PLAN_COMPTABLE_UEMOA` (`compte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VARIATION_CAPITAUX_PROPRES`
--

LOCK TABLES `VARIATION_CAPITAUX_PROPRES` WRITE;
/*!40000 ALTER TABLE `VARIATION_CAPITAUX_PROPRES` DISABLE KEYS */;
/*!40000 ALTER TABLE `VARIATION_CAPITAUX_PROPRES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `VENTES_HISTORIQUES`
--

DROP TABLE IF EXISTS `VENTES_HISTORIQUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `VENTES_HISTORIQUES` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_vente` date NOT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `chiffre_affaires` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VENTES_HISTORIQUES`
--

LOCK TABLES `VENTES_HISTORIQUES` WRITE;
/*!40000 ALTER TABLE `VENTES_HISTORIQUES` DISABLE KEYS */;
INSERT INTO `VENTES_HISTORIQUES` VALUES
(1,'2025-06-01',NULL,120,1200000.00,'2026-05-20 17:00:50'),
(2,'2025-07-01',NULL,130,1300000.00,'2026-05-20 17:00:50'),
(3,'2025-08-01',NULL,125,1250000.00,'2026-05-20 17:00:50'),
(4,'2025-09-01',NULL,140,1400000.00,'2026-05-20 17:00:50'),
(5,'2025-10-01',NULL,150,1500000.00,'2026-05-20 17:00:50'),
(6,'2025-11-01',NULL,160,1600000.00,'2026-05-20 17:00:50'),
(7,'2025-12-01',NULL,170,1700000.00,'2026-05-20 17:00:50'),
(8,'2026-01-01',NULL,110,1100000.00,'2026-05-20 17:00:50'),
(9,'2026-02-01',NULL,105,1050000.00,'2026-05-20 17:00:50'),
(10,'2026-03-01',NULL,115,1150000.00,'2026-05-20 17:00:50'),
(11,'2026-04-01',NULL,125,1250000.00,'2026-05-20 17:00:50'),
(12,'2026-05-01',NULL,135,1350000.00,'2026-05-20 17:00:50');
/*!40000 ALTER TABLE `VENTES_HISTORIQUES` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `WACC_CALCULS`
--

DROP TABLE IF EXISTS `WACC_CALCULS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `WACC_CALCULS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) NOT NULL,
  `capitaux_propres` decimal(15,2) DEFAULT NULL,
  `dettes_financieres` decimal(15,2) DEFAULT NULL,
  `total_financement` decimal(15,2) DEFAULT NULL,
  `cout_capitaux_propres` decimal(5,2) DEFAULT NULL,
  `cout_dette_avant_is` decimal(5,2) DEFAULT NULL,
  `taux_is` decimal(5,2) DEFAULT NULL,
  `cout_dette_apres_is` decimal(5,2) DEFAULT NULL,
  `wacc` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `WACC_CALCULS`
--

LOCK TABLES `WACC_CALCULS` WRITE;
/*!40000 ALTER TABLE `WACC_CALCULS` DISABLE KEYS */;
INSERT INTO `WACC_CALCULS` VALUES
(1,2026,74520000.00,4900000.00,79420000.00,15.00,6.00,25.00,4.50,14.35,'2026-05-28 18:23:47');
/*!40000 ALTER TABLE `WACC_CALCULS` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `covenants`
--

DROP TABLE IF EXISTS `covenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `covenants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `simulation_id` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `ratio_levier` decimal(10,4) NOT NULL,
  `seuil_covenant` decimal(10,4) NOT NULL,
  `alerte` tinyint(1) GENERATED ALWAYS AS (`ratio_levier` > `seuil_covenant`) STORED,
  PRIMARY KEY (`id`),
  KEY `simulation_id` (`simulation_id`),
  CONSTRAINT `covenants_ibfk_1` FOREIGN KEY (`simulation_id`) REFERENCES `simulation_lbo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `covenants`
--

LOCK TABLES `covenants` WRITE;
/*!40000 ALTER TABLE `covenants` DISABLE KEYS */;
/*!40000 ALTER TABLE `covenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dettes_corporate`
--

DROP TABLE IF EXISTS `dettes_corporate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dettes_corporate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `capital_initial` decimal(15,2) NOT NULL,
  `capital_restant` decimal(15,2) NOT NULL,
  `taux_interet` decimal(5,4) NOT NULL,
  `annee` int(11) NOT NULL,
  `simulation_id` int(11) NOT NULL,
  `hierarchie` enum('Senior','Mezzanine','Subordonnée') DEFAULT 'Senior',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dettes_corporate`
--

LOCK TABLES `dettes_corporate` WRITE;
/*!40000 ALTER TABLE `dettes_corporate` DISABLE KEYS */;
/*!40000 ALTER TABLE `dettes_corporate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flux_tresorerie_corporate`
--

DROP TABLE IF EXISTS `flux_tresorerie_corporate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `flux_tresorerie_corporate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `annee` int(11) NOT NULL,
  `ebitda` decimal(15,2) NOT NULL,
  `capex` decimal(15,2) NOT NULL,
  `impots` decimal(15,2) NOT NULL,
  `cash_flow_disponible` decimal(15,2) GENERATED ALWAYS AS (`ebitda` - `capex` - `impots`) STORED,
  `simulation_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flux_tresorerie_corporate`
--

LOCK TABLES `flux_tresorerie_corporate` WRITE;
/*!40000 ALTER TABLE `flux_tresorerie_corporate` DISABLE KEYS */;
/*!40000 ALTER TABLE `flux_tresorerie_corporate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hedge_effectiveness_tests`
--

DROP TABLE IF EXISTS `hedge_effectiveness_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hedge_effectiveness_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hedge_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `test_type` enum('Prospective','Retrospective') NOT NULL,
  `effectiveness_score` decimal(10,4) NOT NULL,
  `is_effective` tinyint(1) NOT NULL,
  `model_version` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hedge_id` (`hedge_id`),
  CONSTRAINT `hedge_effectiveness_tests_ibfk_1` FOREIGN KEY (`hedge_id`) REFERENCES `hedge_relations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hedge_effectiveness_tests`
--

LOCK TABLES `hedge_effectiveness_tests` WRITE;
/*!40000 ALTER TABLE `hedge_effectiveness_tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `hedge_effectiveness_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hedge_inefficiency`
--

DROP TABLE IF EXISTS `hedge_inefficiency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hedge_inefficiency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hedge_id` int(11) NOT NULL,
  `period` date NOT NULL,
  `real_fv_change` decimal(15,4) DEFAULT NULL,
  `hypothetical_fv_change` decimal(15,4) DEFAULT NULL,
  `inefficiency_amount` decimal(15,4) DEFAULT NULL,
  `accounting_treatment` enum('P&L','OCI') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hedge_id` (`hedge_id`),
  CONSTRAINT `hedge_inefficiency_ibfk_1` FOREIGN KEY (`hedge_id`) REFERENCES `hedge_relations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hedge_inefficiency`
--

LOCK TABLES `hedge_inefficiency` WRITE;
/*!40000 ALTER TABLE `hedge_inefficiency` DISABLE KEYS */;
/*!40000 ALTER TABLE `hedge_inefficiency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hedge_relations`
--

DROP TABLE IF EXISTS `hedge_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hedge_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hedge_id` varchar(50) NOT NULL,
  `hedge_type` enum('Fair Value','Cash Flow') NOT NULL,
  `hedged_item` varchar(255) NOT NULL,
  `hedging_instrument` varchar(255) NOT NULL,
  `inception_date` date NOT NULL,
  `maturity_date` date NOT NULL,
  `hedge_ratio` decimal(10,4) NOT NULL,
  `status` enum('Active','Discontinued','Expired') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hedge_relations`
--

LOCK TABLES `hedge_relations` WRITE;
/*!40000 ALTER TABLE `hedge_relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `hedge_relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portefeuille_actions`
--

DROP TABLE IF EXISTS `portefeuille_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `portefeuille_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `symbole` varchar(10) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_achat` decimal(10,2) NOT NULL,
  `prix_actuel` decimal(10,2) DEFAULT NULL,
  `date_achat` date NOT NULL,
  `secteur` varchar(50) DEFAULT NULL,
  `beta` decimal(5,2) DEFAULT 1.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portefeuille_actions`
--

LOCK TABLES `portefeuille_actions` WRITE;
/*!40000 ALTER TABLE `portefeuille_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `portefeuille_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portefeuille_simulations`
--

DROP TABLE IF EXISTS `portefeuille_simulations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `portefeuille_simulations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_simulation` varchar(100) NOT NULL,
  `date_simulation` date NOT NULL,
  `capital_initial` decimal(15,2) NOT NULL,
  `valeur_actuelle` decimal(15,2) DEFAULT NULL,
  `rendement_total` decimal(10,4) DEFAULT NULL,
  `volatilite` decimal(10,4) DEFAULT NULL,
  `sharpe_ratio` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portefeuille_simulations`
--

LOCK TABLES `portefeuille_simulations` WRITE;
/*!40000 ALTER TABLE `portefeuille_simulations` DISABLE KEYS */;
/*!40000 ALTER TABLE `portefeuille_simulations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sig_results`
--

DROP TABLE IF EXISTS `sig_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sig_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercice` int(11) DEFAULT NULL,
  `ca` decimal(15,2) DEFAULT NULL,
  `charges` decimal(15,2) DEFAULT NULL,
  `marge_brute` decimal(15,2) DEFAULT NULL,
  `ebe` decimal(15,2) DEFAULT NULL,
  `resultat_net` decimal(15,2) DEFAULT NULL,
  `date_calc` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sig_results`
--

LOCK TABLES `sig_results` WRITE;
/*!40000 ALTER TABLE `sig_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `sig_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simulation_lbo`
--

DROP TABLE IF EXISTS `simulation_lbo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simulation_lbo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_simulation` varchar(100) NOT NULL,
  `date_simulation` date NOT NULL,
  `prix_acquisition` decimal(15,2) NOT NULL,
  `multiple_ebitda_entree` decimal(5,2) NOT NULL,
  `multiple_ebitda_sortie` decimal(5,2) NOT NULL,
  `croissance_ebitda` decimal(5,4) NOT NULL,
  `tri_resultat` decimal(5,4) DEFAULT NULL,
  `multiple_sortie` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simulation_lbo`
--

LOCK TABLES `simulation_lbo` WRITE;
/*!40000 ALTER TABLE `simulation_lbo` DISABLE KEYS */;
/*!40000 ALTER TABLE `simulation_lbo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions_portefeuille`
--

DROP TABLE IF EXISTS `transactions_portefeuille`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions_portefeuille` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `simulation_id` int(11) NOT NULL,
  `symbole` varchar(10) NOT NULL,
  `type_transaction` enum('Achat','Vente') NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `date_transaction` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `simulation_id` (`simulation_id`),
  CONSTRAINT `transactions_portefeuille_ibfk_1` FOREIGN KEY (`simulation_id`) REFERENCES `portefeuille_simulations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions_portefeuille`
--

LOCK TABLES `transactions_portefeuille` WRITE;
/*!40000 ALTER TABLE `transactions_portefeuille` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions_portefeuille` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-29 21:05:02
