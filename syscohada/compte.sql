-- Ce script recrée les tables manquantes dans la base de données 'ohada'
-- en se basant sur l'extrait de dump fourni par l'utilisateur.

USE ohada;

-- =========================================================
-- TABLE DE SUPPORT POUR CLÉ ÉTRANGÈRE (EXERCICE)
-- Création minimale de la table 'exercice' nécessaire pour la clé étrangère de 'bilan_ouverture'.
-- Cette table est souvent liée à la gestion des périodes comptables.
-- =========================================================
DROP TABLE IF EXISTS exercice;
CREATE TABLE exercice (
id int NOT NULL AUTO_INCREMENT,
-- Ajoutez ici les colonnes réelles (ex: 'annee', 'date_debut', 'date_fin') si vous les connaissez
PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLE balance
-- =========================================================
DROP TABLE IF EXISTS balance;
CREATE TABLE balance (
id int NOT NULL AUTO_INCREMENT,
date_operation date NOT NULL,
numero_compte varchar(20) NOT NULL,
description varchar(255) DEFAULT NULL,
montant decimal(15,2) NOT NULL DEFAULT '0.00',
PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insertion des données
LOCK TABLES balance WRITE;
INSERT INTO balance VALUES (1,'2024-10-06','707','Vente matériel informatique',69000.00),(2,'2024-10-06','512','Vente matériel informatique',-69000.00);
UNLOCK TABLES;

-- =========================================================
-- TABLE bilan_ouverture
-- =========================================================
DROP TABLE IF EXISTS bilan_ouverture;
CREATE TABLE bilan_ouverture (
id int NOT NULL AUTO_INCREMENT,
id_exercice int DEFAULT NULL,
libelle varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
montant decimal(15,2) NOT NULL,
type_element enum('actif','passif') COLLATE utf8mb4_unicode_ci NOT NULL,
sous_type enum('immobilisations','circulant','capitaux_propres','dettes') COLLATE utf8mb4_unicode_ci NOT NULL,
date_enregistrement date NOT NULL,
PRIMARY KEY (id),
KEY id_exercice (id_exercice),
CONSTRAINT bilan_ouverture_ibfk_1 FOREIGN KEY (id_exercice) REFERENCES exercice (id)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pas de données d'insertion fournies pour cette table.
LOCK TABLES bilan_ouverture WRITE;
UNLOCK TABLES;

-- =========================================================
-- TABLE bilans
-- =========================================================
DROP TABLE IF EXISTS bilans;
CREATE TABLE bilans (
id int NOT NULL AUTO_INCREMENT,
date_bilan date NOT NULL,
total_actif decimal(10,2) DEFAULT '0.00',
total_passif decimal(10,2) DEFAULT '0.00',
created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Pas de données d'insertion fournies pour cette table.
LOCK TABLES bilans WRITE;
UNLOCK TABLES;

-- =========================================================
-- TABLE classes_ohada
-- =========================================================
DROP TABLE IF EXISTS classes_ohada;
CREATE TABLE classes_ohada (
id int NOT NULL AUTO_INCREMENT,
num_classe varchar(10) NOT NULL,
intitule_classe varchar(255) NOT NULL,
statut enum('actif','passif') NOT NULL,
PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insertion des données
LOCK TABLES classes_ohada WRITE;
INSERT INTO classes_ohada VALUES (1,'Classe 1','Comptes de capitaux','passif'),(2,'Classe 2','Comptes d?immobilisations','actif'),(3,'Classe 3','Comptes de stocks','actif'),(4,'Classe 4','Comptes de tiers','actif'),(5,'Classe 5','Comptes de trésorerie','actif'),(6,'Classe 6','Comptes de charges','actif'),(7,'Classe 7','Comptes de produits','actif'),(8,'Classe 8','Comptes des engagements hors bilan','passif');
UNLOCK TABLES;

-- =========================================================
-- TABLE comptes (Attention: "compte" existe déjà, ceci est "comptes")
-- =========================================================
DROP TABLE IF EXISTS comptes;
CREATE TABLE comptes (
id int NOT NULL AUTO_INCREMENT,
numero_compte varchar(20) NOT NULL,
intitule varchar(255) NOT NULL,
type_compte enum('Actif','Passif','Produit','Charge') NOT NULL,
PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insertion des données
LOCK TABLES comptes WRITE;
INSERT INTO comptes VALUES (1,'1000','Caisse','Actif'),(2,'1010','Banque','Actif'),(3,'2000','Fournisseurs','Passif'),(4,'2100','Banque','Passif');
UNLOCK TABLES;

-- =========================================================
-- TABLE comptes_bilan
-- =========================================================
DROP TABLE IF EXISTS comptes_bilan;
CREATE TABLE comptes_bilan (
id int NOT NULL AUTO_INCREMENT,
numero_compte varchar(20) NOT NULL,
intitule varchar(255) NOT NULL,
type_compte enum('Actif','Passif','Produit','Charge') NOT NULL,
PRIMARY KEY (id),
KEY numero_compte (numero_compte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Pas de données d'insertion fournies pour cette table.
LOCK TABLES comptes_bilan WRITE;
UNLOCK TABLES;

-- =========================================================
-- TABLE comptes_comptables
-- =========================================================
DROP TABLE IF EXISTS comptes_comptables;
CREATE TABLE comptes_comptables (
id int NOT NULL AUTO_INCREMENT,
code_compte varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
libelle varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
montant decimal(15,2) NOT NULL,
PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des données
LOCK TABLES comptes_comptables WRITE;
INSERT INTO comptes_comptables VALUES (1,'701','Ventes de marchandises',500000.00),(2,'603','Variation de stocks',20000.00),(3,'631','Impôts et taxes',15000.00),(4,'641','Charges de personnel',100000.00),(5,'791','Reprises sur provisions',5000.00),(6,'681','Dotations aux amortissements',20000.00),(7,'755','Produits financiers',10000.00),(8,'661','Charges financières',5000.00);
UNLOCK TABLES;

-- =========================================================
-- TABLE comptes_ohada
-- =========================================================
DROP TABLE IF EXISTS comptes_ohada;
CREATE TABLE comptes_ohada (
id int NOT NULL AUTO_INCREMENT,
num_compte varchar(20) NOT NULL,
intitule varchar(255) NOT NULL,
sous_classe_id int DEFAULT NULL,
description text,
PRIMARY KEY (id),
KEY sous_classe_id (sous_classe_id)
) ENGINE=MyISAM AUTO_INCREMENT=1045 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Pas de données d'insertion fournies pour cette table.
