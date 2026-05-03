#!/bin/bash
# Script d'installation complète du Centre de Santé Mamadou Diop

set -e  # Arrêt en cas d'erreur

echo "====================================================="
echo "INSTALLATION DU CENTRE DE SANTÉ MAMADOU DIOP"
echo "====================================================="

# Demander le mot de passe MySQL
read -sp "Mot de passe MySQL (root): " MYSQL_PWD
echo ""

# 1. NETTOYAGE DES ANCIENNES TABLES
echo -e "\n1. NETTOYAGE DE LA BASE DE DONNÉES..."
mariadb -u root -p$MYSQL_PWD centrediop << 'SQL'
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS paies;
DROP TABLE IF EXISTS stats_journalieres;
DROP TABLE IF EXISTS consultation_actes;
DROP TABLE IF EXISTS rendez_vous;
DROP TABLE IF EXISTS paiements;
DROP TABLE IF EXISTS file_attente;
DROP TABLE IF EXISTS dossiers_medicaux;
DROP TABLE IF EXISTS consultations;
DROP TABLE IF EXISTS actes_medicaux;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS services;
SET FOREIGN_KEY_CHECKS = 1;
SHOW TABLES;
SQL
echo "✅ Nettoyage terminé"

# 2. CRÉATION DES NOUVELLES TABLES
echo -e "\n2. CRÉATION DES TABLES OPTIMISÉES..."
mariadb -u root -p$MYSQL_PWD centrediop << 'SQL'
-- Table des services/départements
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    couleur VARCHAR(20) DEFAULT '#3498db',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des patients
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_patient VARCHAR(20) UNIQUE,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    lieu_naissance VARCHAR(100),
    sexe ENUM('M','F') NOT NULL,
    groupe_sanguin VARCHAR(5),
    allergie TEXT,
    antecedent_medicaux TEXT,
    traitement_en_cours TEXT,
    telephone VARCHAR(20),
    adresse TEXT,
    email VARCHAR(100),
    personne_contact VARCHAR(100),
    telephone_contact VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_numero (numero_patient),
    INDEX idx_nom (nom)
);

-- Table des utilisateurs (personnel)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'medecin', 'sagefemme', 'caissier', 'pharmacien') NOT NULL,
    service_id INT,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    email VARCHAR(100),
    specialite VARCHAR(100),
    numero_ordre VARCHAR(50),
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_role (role),
    INDEX idx_service (service_id)
);

-- Table des consultations médicales
CREATE TABLE IF NOT EXISTS consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_consultation VARCHAR(20) UNIQUE,
    patient_id INT NOT NULL,
    medecin_id INT NOT NULL,
    service_id INT NOT NULL,
    date_consultation DATETIME NOT NULL,
    motif_consultation TEXT,
    diagnostic TEXT,
    traitement_prescrit TEXT,
    observations TEXT,
    type_consultation ENUM('normale', 'urgence', 'controle') DEFAULT 'normale',
    statut ENUM('planifiee', 'en_cours', 'terminee', 'annulee') DEFAULT 'planifiee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (medecin_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_patient (patient_id),
    INDEX idx_medecin (medecin_id),
    INDEX idx_date (date_consultation),
    INDEX idx_statut (statut)
);

-- Table des traitements/actes médicaux
CREATE TABLE IF NOT EXISTS actes_medicaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_acte VARCHAR(20) UNIQUE NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    description TEXT,
    categorie ENUM('consultation', 'soin', 'examen', 'vaccination', 'chirurgie') NOT NULL,
    prix_consultation DECIMAL(10,2) DEFAULT 0,
    prix_traitement DECIMAL(10,2) DEFAULT 0,
    duree_estimee INT COMMENT 'Durée en minutes',
    service_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Table des consultations_actes (liaison)
CREATE TABLE IF NOT EXISTS consultation_actes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultation_id INT NOT NULL,
    acte_id INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_applique DECIMAL(10,2) NOT NULL,
    observations TEXT,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (acte_id) REFERENCES actes_medicaux(id)
);

-- Table de la file d'attente (Temps réel)
CREATE TABLE IF NOT EXISTS file_attente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    service_id INT NOT NULL,
    priorite ENUM('normal', 'senior', 'urgence') DEFAULT 'normal',
    statut ENUM('en_attente', 'appele', 'en_consultation', 'termine') DEFAULT 'en_attente',
    cree_a TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    appele_a DATETIME,
    commence_a DATETIME,
    termine_a DATETIME,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_service (service_id),
    INDEX idx_statut (statut)
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_facture VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    consultation_id INT,
    caissier_id INT NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    montant_paye DECIMAL(10,2) NOT NULL,
    montant_restant DECIMAL(10,2) DEFAULT 0,
    mode_paiement ENUM('especes', 'carte', 'cheque', 'mobile_money', 'assurance') DEFAULT 'especes',
    statut ENUM('paye', 'partiel', 'impaye') DEFAULT 'impaye',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observations TEXT,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (caissier_id) REFERENCES users(id),
    INDEX idx_patient (patient_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_paiement)
);

-- Table des rendez-vous
CREATE TABLE IF NOT EXISTS rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    service_id INT NOT NULL,
    medecin_id INT,
    date_rdv DATE NOT NULL,
    heure_rdv TIME NOT NULL,
    motif VARCHAR(255),
    statut ENUM('programme', 'confirme', 'honore', 'annule', 'reporte') DEFAULT 'programme',
    notes TEXT,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (medecin_id) REFERENCES users(id),
    UNIQUE KEY unique_rdv (medecin_id, date_rdv, heure_rdv),
    INDEX idx_date (date_rdv),
    INDEX idx_statut (statut)
);

-- Table des dossiers médicaux (suivi)
CREATE TABLE IF NOT EXISTS dossiers_medicaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL UNIQUE,
    antecedents_familiaux TEXT,
    habitudes_vie TEXT,
    vaccinations TEXT,
    allergies_connues TEXT,
    traitement_long_cours TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Table des paies du personnel
CREATE TABLE IF NOT EXISTS paies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mois INT NOT NULL,
    annee INT NOT NULL,
    salaire_base DECIMAL(10,2) NOT NULL,
    nb_consultations INT DEFAULT 0,
    nb_actes INT DEFAULT 0,
    prime_consultation DECIMAL(10,2) DEFAULT 0,
    prime_acte DECIMAL(10,2) DEFAULT 0,
    total_brut DECIMAL(10,2) NOT NULL,
    cotisations DECIMAL(10,2) DEFAULT 0,
    total_net DECIMAL(10,2) NOT NULL,
    statut ENUM('calcule', 'valide', 'paye') DEFAULT 'calcule',
    date_paiement DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_paie (user_id, mois, annee)
);

-- Table des statistiques journalières
CREATE TABLE IF NOT EXISTS stats_journalieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_stats DATE NOT NULL,
    service_id INT,
    nb_consultations INT DEFAULT 0,
    nb_patients INT DEFAULT 0,
    nb_urgences INT DEFAULT 0,
    nb_rdv INT DEFAULT 0,
    recettes DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    UNIQUE KEY unique_stats (date_stats, service_id)
);

-- Insertion des services de base
INSERT INTO services (name, description, couleur) VALUES
('Accueil/Triage', 'Service d\'accueil et orientation', '#3498db'),
('Caisse', 'Service de paiement', '#2ecc71'),
('Pédiatrie', 'Soins pour enfants', '#e74c3c'),
('Odontologie', 'Soins dentaires', '#f39c12'),
('Gynécologie', 'Santé de la femme', '#9b59b6'),
('Médecine générale', 'Consultations générales', '#1abc9c'),
('Pharmacie', 'Dispensation médicaments', '#e67e22');

-- Insertion des actes médicaux standards
INSERT INTO actes_medicaux (code_acte, libelle, categorie, prix_consultation, prix_traitement) VALUES
('CONS-GEN', 'Consultation générale', 'consultation', 5000, 5000),
('CONS-PED', 'Consultation pédiatrique', 'consultation', 6000, 6000),
('CONS-DENT', 'Consultation dentaire', 'consultation', 7000, 7000),
('CONS-GYN', 'Consultation gynécologique', 'consultation', 8000, 8000),
('SOIN-PAN', 'Pansement simple', 'soin', 0, 3000),
('SOIN-COMP', 'Pansement complexe', 'soin', 0, 8000),
('VAC-ROU', 'Vaccin rougeole', 'vaccination', 0, 5000),
('VAC-DTP', 'Vaccin DTP', 'vaccination', 0, 6000),
('EXAM-ECHO', 'Échographie', 'examen', 0, 15000),
('EXAM-RADIO', 'Radiographie', 'examen', 0, 10000);
SQL
echo "✅ Tables créées avec succès"

# 3. CRÉATION DES UTILISATEURS DE BASE
echo -e "\n3. CRÉATION DES UTILISATEURS..."
php -r "
require_once 'config/database.php';
\$pdo = getPDO();

// Récupérer les services
\$services = \$pdo->query(\"SELECT id, name FROM services\")->fetchAll();
\$service_map = [];
foreach (\$services as \$s) {
    \$service_map[\$s['name']] = \$s['id'];
}

// Créer l'admin
\$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
\$stmt = \$pdo->prepare(\"INSERT IGNORE INTO users (username, password, role, prenom, nom, service_id) VALUES (?, ?, ?, ?, ?, ?)\");
\$stmt->execute(['admin', \$admin_password, 'admin', 'Admin', 'System', \$service_map['Accueil/Triage'] ?? 1]);
echo \"✅ Admin créé\\n\";

// Créer caissier
\$stmt->execute(['caissier1', password_hash('caissier123', PASSWORD_DEFAULT), 'caissier', 'Oumar', 'Sow', \$service_map['Caisse'] ?? 1]);
echo \"✅ Caissier créé\\n\";

// Créer sage-femme
\$stmt->execute(['sagefemme1', password_hash('sagefemme123', PASSWORD_DEFAULT), 'sagefemme', 'Fatou', 'Ndiaye', \$service_map['Gynécologie'] ?? 1]);
echo \"✅ Sage-femme créée\\n\";

// Créer médecins
\$stmt->execute(['dr.fall', password_hash('pediatre123', PASSWORD_DEFAULT), 'medecin', 'Aminata', 'Fall', \$service_map['Pédiatrie'] ?? 1]);
\$stmt->execute(['dr.diop', password_hash('medecin123', PASSWORD_DEFAULT), 'medecin', 'Moussa', 'Diop', \$service_map['Odontologie'] ?? 1]);
echo \"✅ Médecins créés\\n\";

echo \"\\n🔑 Informations de connexion:\\n\";
echo \"  Admin: admin / admin123\\n\";
echo \"  Caissier: caissier1 / caissier123\\n\";
echo \"  Sage-femme: sagefemme1 / sagefemme123\\n\";
echo \"  Dr. Fall: dr.fall / pediatre123\\n\";
echo \"  Dr. Diop: dr.diop / medecin123\\n\";
"

# 4. CRÉATION DES DONNÉES DE TEST
echo -e "\n4. CRÉATION DES DONNÉES DE TEST..."
php -r "
require_once 'config/database.php';
\$pdo = getPDO();

// Récupérer les services
\$services = \$pdo->query(\"SELECT id, name FROM services\")->fetchAll(PDO::FETCH_KEY_PAIR);

// Créer des patients de test
\$patients = [
    ['Awa', 'Ndiaye', '2010-05-15', 'F', '781234567', 'Dakar'],
    ['Omar', 'Diallo', '1990-08-20', 'M', '782345678', 'Pikine'],
    ['Fatoumata', 'Sow', '1985-11-10', 'F', '783456789', 'Guediawaye'],
    ['Ibrahima', 'Ba', '2005-03-25', 'M', '784567890', 'Rufisque'],
    ['Khadija', 'Fall', '1978-07-18', 'F', '785678901', 'Thiès'],
];

\$stmt = \$pdo->prepare(\"
    INSERT INTO patients (numero_patient, prenom, nom, date_naissance, sexe, telephone, adresse)
    VALUES (?, ?, ?, ?, ?, ?, ?)
\");

foreach (\$patients as \$i => \$p) {
    \$numero = 'PAT-' . date('Ymd') . '-' . str_pad(\$i+1, 4, '0', STR_PAD_LEFT);
    \$stmt->execute([\$numero, \$p[0], \$p[1], \$p[2], \$p[3], \$p[4], \$p[5]]);
    \$patient_id = \$pdo->lastInsertId();
    
    // Créer dossier médical
    \$pdo->prepare(\"INSERT INTO dossiers_medicaux (patient_id) VALUES (?)\")->execute([\$patient_id]);
    echo \"  ✅ Patient créé: {\$p[0]} {\$p[1]}\\n\";
}

// Créer des tokens de test
\$patients = \$pdo->query(\"SELECT id FROM patients\")->fetchAll();
\$services_list = array_values(\$services);

for (\$i=0; \$i<5; \$i++) {
    \$patient = \$patients[\$i];
    \$service_id = \$services_list[array_rand(\$services_list)];
    \$priority = (\$i % 2 == 0) ? 'senior' : 'normal';
    \$token = 'TKN' . date('His') . str_pad(\$i+1, 2, '0', STR_PAD_LEFT);
    
    \$pdo->prepare(\"
        INSERT INTO file_attente (token, patient_id, service_id, priorite, statut)
        VALUES (?, ?, ?, ?, 'en_attente')
    \")->execute([\$token, \$patient['id'], \$service_id, \$priority]);
}
echo \"  ✅ Tokens de test créés\\n\";

// Créer quelques rendez-vous
\$medecins = \$pdo->query(\"SELECT id FROM users WHERE role='medecin'\")->fetchAll();
for (\$i=0; \$i<3; \$i++) {
    \$patient = \$patients[\$i];
    \$medecin = \$medecins[array_rand(\$medecins)];
    \$service_id = \$services_list[array_rand(\$services_list)];
    \$date_rdv = date('Y-m-d', strtotime('+' . (\$i+1) . ' days'));
    \$heure_rdv = sprintf('%02d:00', 9 + \$i);
    
    \$pdo->prepare(\"
        INSERT INTO rendez_vous (patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, statut)
        VALUES (?, ?, ?, ?, ?, ?, 'programme')
    \")->execute([\$patient['id'], \$service_id, \$medecin['id'], \$date_rdv, \$heure_rdv, 'Consultation de routine']);
}
echo \"  ✅ Rendez-vous de test créés\\n\";

echo \"\\n✅ Données de test créées avec succès !\\n\";
"

echo -e "\n====================================================="
echo "✅ INSTALLATION TERMINÉE AVEC SUCCÈS !"
echo "====================================================="
echo ""
echo "📊 RÉCAPITULATIF :"
echo "  - 7 services créés"
echo "  - 5 patients enregistrés"
echo "  - 5 utilisateurs créés"
echo "  - 5 tokens en file d'attente"
echo "  - 3 rendez-vous programmés"
echo ""
echo "🔑 IDENTIFIANTS DE CONNEXION :"
echo "  Admin      : admin / admin123"
echo "  Caissier   : caissier1 / caissier123"
echo "  Sage-femme : sagefemme1 / sagefemme123"
echo "  Dr. Fall   : dr.fall / pediatre123"
echo "  Dr. Diop   : dr.diop / medecin123"
echo ""
echo "🚀 POUR LANCER L'APPLICATION :"
echo "  php -S 192.168.1.3:8000"
echo ""
echo "🌐 ACCÈS WEB :"
echo "  http://192.168.1.3:8000"
