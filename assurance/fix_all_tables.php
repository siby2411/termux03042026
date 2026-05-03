<?php
require_once 'config/db.php';

$db = getDB();

echo "=== CORRECTION STRUCTURE DES TABLES ===\n\n";

// Supprimer et recréer les tables dans le bon ordre
$db->exec("SET FOREIGN_KEY_CHECKS=0");

// Supprimer les tables existantes
$tables = ['paiements', 'sinistres', 'contrat_garanties', 'contrats', 'vehicules', 'clients', 'garanties', 'produits_assurance', 'utilisateurs'];
foreach($tables as $table) {
    try {
        $db->exec("DROP TABLE IF EXISTS $table");
        echo "✓ Table $table supprimée\n";
    } catch(PDOException $e) {}
}

// Recréer les tables avec structure correcte
$sql = "
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_client ENUM('particulier', 'entreprise') DEFAULT 'particulier',
    numero_client VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    raison_sociale VARCHAR(200),
    email VARCHAR(100),
    telephone VARCHAR(20) NOT NULL,
    adresse TEXT,
    ville VARCHAR(100),
    date_naissance DATE,
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    immatriculation VARCHAR(20) UNIQUE NOT NULL,
    marque VARCHAR(50) NOT NULL,
    modele VARCHAR(50) NOT NULL,
    annee_fabrication INT,
    valeur_venale DECIMAL(15,2),
    proprietaire_id INT,
    statut ENUM('actif', 'assure', 'sinistre') DEFAULT 'actif',
    FOREIGN KEY (proprietaire_id) REFERENCES clients(id) ON DELETE SET NULL
);

CREATE TABLE contrats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_contrat VARCHAR(50) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    vehicule_id INT,
    formule ENUM('Tiers', 'Tiers_Plus', 'Tous_Risques', 'Premium') DEFAULT 'Tiers',
    prime_nette DECIMAL(15,2) NOT NULL,
    taxe DECIMAL(15,2) DEFAULT 0,
    prime_ttc DECIMAL(15,2) NOT NULL,
    mode_paiement ENUM('Annuel', 'Semestriel', 'Trimestriel', 'Mensuel') DEFAULT 'Annuel',
    date_effet DATE NOT NULL,
    date_echeance DATE NOT NULL,
    statut ENUM('actif', 'expire', 'resilie') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (vehicule_id) REFERENCES vehicules(id)
);

CREATE TABLE sinistres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_sinistre VARCHAR(50) UNIQUE NOT NULL,
    contrat_id INT NOT NULL,
    date_survenance DATE NOT NULL,
    date_declaration DATE NOT NULL,
    type_sinistre ENUM('Accident', 'Vol', 'Incendie', 'Bris_de_glace') DEFAULT 'Accident',
    montant_estime DECIMAL(15,2),
    montant_indemnise DECIMAL(15,2),
    statut ENUM('declare', 'expertise', 'indemnise', 'cloture') DEFAULT 'declare',
    FOREIGN KEY (contrat_id) REFERENCES contrats(id)
);

CREATE TABLE paiements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contrat_id INT NOT NULL,
    numero_quittance VARCHAR(50) UNIQUE NOT NULL,
    date_paiement DATE NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    mode_reglement ENUM('Especes', 'Virement', 'Cheque', 'Orange_Money', 'Wave') DEFAULT 'Especes',
    statut ENUM('valide', 'annule') DEFAULT 'valide',
    FOREIGN KEY (contrat_id) REFERENCES contrats(id)
);

CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_utilisateur VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    role ENUM('admin', 'agent', 'comptable') DEFAULT 'agent',
    actif BOOLEAN DEFAULT TRUE
);
";

// Exécuter chaque requête séparément
$queries = explode(';', $sql);
foreach($queries as $query) {
    if(trim($query)) {
        try {
            $db->exec($query);
        } catch(PDOException $e) {
            echo "Erreur: " . $e->getMessage() . "\n";
        }
    }
}

// Créer l'utilisateur admin
$password = password_hash('admin123', PASSWORD_DEFAULT);
$db->exec("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, nom, prenom, role, actif) 
           VALUES ('admin', '$password', 'Administrateur', 'Système', 'admin', 1)");

$db->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n✅ Structure recréée avec succès !\n";
?>

