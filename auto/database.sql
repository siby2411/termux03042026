-- Fichier: database.sql

-- Utilisation de la base de données 'auto'
USE auto;

-- Table pour les utilisateurs (admins, etc.)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL, -- Stockera le hachage du mot de passe
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'partenaire', 'client') NOT NULL DEFAULT 'client',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les partenaires
CREATE TABLE IF NOT EXISTS partenaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) UNIQUE NOT NULL,
    contact_telephone VARCHAR(20),
    adresse TEXT,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les clients
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    permis_conduire_num VARCHAR(50) UNIQUE,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les voitures
CREATE TABLE IF NOT EXISTS voitures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    annee INT,
    prix_journalier DECIMAL(10, 2) NOT NULL,
    statut ENUM('disponible', 'louee', 'en_maintenance') NOT NULL DEFAULT 'disponible',
    partenaire_id INT,
    description TEXT,
    image_url VARCHAR(255),
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partenaire_id) REFERENCES partenaires(id) ON DELETE SET NULL
);

-- Table pour les locations
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    voiture_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    cout_total DECIMAL(10, 2) NOT NULL,
    statut ENUM('en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_cours',
    date_location TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (voiture_id) REFERENCES voitures(id) ON DELETE CASCADE
);

-- Table pour les paiements
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    methode_paiement VARCHAR(50),
    statut ENUM('en_attente', 'paye', 'echoue') NOT NULL DEFAULT 'en_attente',
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);

-- Ajout d'un utilisateur administrateur par défaut (mot de passe 'admin123' haché)
-- Mot de passe 'admin123' haché avec PASSWORD() pour MariaDB/MySQL
-- En PHP, vous utiliserez password_hash()
INSERT INTO users (nom_utilisateur, mot_de_passe, email, role) VALUES
('admin', PASSWORD('admin123'), 'admin@auto.com', 'admin')
ON DUPLICATE KEY UPDATE nom_utilisateur=nom_utilisateur; -- Empêche l'insertion si déjà existant
