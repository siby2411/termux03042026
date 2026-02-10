<?php
/**
 * CRÉATION DE LA STRUCTURE COMPLÈTE POUR L'ANALYSE FINANCIÈRE
 * Système SYSCOHADA
 */

class CreationStructureComplete {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function creerStructuresManquantes() {
        echo "🏗️  CRÉATION DES STRUCTURES MANQUANTES\n";
        echo "=====================================\n";
        
        $this->creerTableSoldesComptes();
        $this->creerTableRatiosFinanciers();
        $this->creerTablesAnalyses();
        $this->peuplerDonneesTest();
        
        echo "✅ Structures créées avec succès\n";
    }
    
    private function creerTableSoldesComptes() {
        echo "📊 Création de la table soldes_comptes... ";
        
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS soldes_comptes (
                id_solde INT PRIMARY KEY AUTO_INCREMENT,
                exercice_id INT NOT NULL,
                numero_compte VARCHAR(10) NOT NULL,
                libelle_compte VARCHAR(255) NOT NULL,
                solde DECIMAL(15,2) NOT NULL DEFAULT 0,
                type_solde ENUM('debit', 'credit') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice),
                INDEX idx_compte (numero_compte),
                INDEX idx_exercice (exercice_id),
                INDEX idx_compte_exercice (numero_compte, exercice_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        echo "✅\n";
    }
    
    private function creerTableRatiosFinanciers() {
        echo "📈 Création de la table ratios_financiers... ";
        
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS ratios_financiers (
                id_ratio INT PRIMARY KEY AUTO_INCREMENT,
                exercice_id INT NOT NULL,
                categorie ENUM('liquidite', 'solvabilite', 'rentabilite', 'rotation', 'endettement', 'equilibre') NOT NULL,
                nom_ratio VARCHAR(100) NOT NULL,
                formule_calcul TEXT NOT NULL,
                valeur_calculee DECIMAL(10,4) NOT NULL,
                valeur_reference DECIMAL(10,4),
                interpretation ENUM('excellent', 'bon', 'moyen', 'faible', 'critique') DEFAULT 'moyen',
                seuil_alerte_min DECIMAL(10,4),
                seuil_alerte_max DECIMAL(10,4),
                date_calcul DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice),
                INDEX idx_categorie (categorie),
                INDEX idx_exercice (exercice_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        echo "✅\n";
    }
    
    private function creerTablesAnalyses() {
        $tables = [
            'analyses_sectorielles' => "
                CREATE TABLE IF NOT EXISTS analyses_sectorielles (
                    id_analyse INT PRIMARY KEY AUTO_INCREMENT,
                    exercice_id INT NOT NULL,
                    secteur_activite VARCHAR(100) NOT NULL,
                    ratio_nom VARCHAR(100) NOT NULL,
                    moyenne_sectorielle DECIMAL(10,4) NOT NULL,
                    valeur_entreprise DECIMAL(10,4) NOT NULL,
                    ecart_sectoriel DECIMAL(10,4) NOT NULL,
                    positionnement ENUM('leader', 'bon', 'moyen', 'faible') DEFAULT 'moyen',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            
            'historique_ratios' => "
                CREATE TABLE IF NOT EXISTS historique_ratios (
                    id_historique INT PRIMARY KEY AUTO_INCREMENT,
                    exercice_id INT NOT NULL,
                    ratio_nom VARCHAR(100) NOT NULL,
                    valeur_n DECIMAL(10,4),
                    valeur_n1 DECIMAL(10,4),
                    valeur_n2 DECIMAL(10,4),
                    tendance ENUM('amelioration', 'stabilite', 'deterioration') DEFAULT 'stabilite',
                    amplitude_variation DECIMAL(10,4),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            
            'bilans' => "
                CREATE TABLE IF NOT EXISTS bilans (
                    id_bilan INT PRIMARY KEY AUTO_INCREMENT,
                    exercice_id INT NOT NULL,
                    type_bilan ENUM('actif', 'passif') NOT NULL,
                    poste_comptable VARCHAR(10) NOT NULL,
                    libelle_poste VARCHAR(255) NOT NULL,
                    montant DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ",
            
            'comptes_resultat' => "
                CREATE TABLE IF NOT EXISTS comptes_resultat (
                    id_cr INT PRIMARY KEY AUTO_INCREMENT,
                    exercice_id INT NOT NULL,
                    type_poste ENUM('produit', 'charge') NOT NULL,
                    poste_comptable VARCHAR(10) NOT NULL,
                    libelle_poste VARCHAR(255) NOT NULL,
                    montant DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            "
        ];
        
        foreach ($tables as $nom_table => $sql) {
            echo "📋 Création de la table $nom_table... ";
            $this->db->exec($sql);
            echo "✅\n";
        }
    }
    
    private function peuplerDonneesTest() {
        echo "🧪 Peuplement des données de test... ";
        
        // Vérifier s'il existe des exercices
        $sql = "SELECT id_exercice FROM exercices_comptables LIMIT 1";
        $stmt = $this->db->query($sql);
        $exercice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exercice) {
            echo "❌ Aucun exercice trouvé - création d'un exercice test\n";
            $this->creerExerciceTest();
            $exercice = ['id_exercice' => $this->db->lastInsertId()];
        }
        
        $exercice_id = $exercice['id_exercice'];
        
        // Données de test pour un bilan type PME
        $donnees_test = [
            // CAPITAUX PROPRES (Classe 1)
            ['101', 'Capital social', 500000.00, 'credit'],
            ['106', 'Réserves', 150000.00, 'credit'],
            ['120', 'Résultat de l\'exercice', 75000.00, 'credit'],
            
            // IMMOBILISATIONS (Classe 2)
            ['211', 'Terrains', 300000.00, 'debit'],
            ['215', 'Constructions', 400000.00, 'debit'],
            ['218', 'Matériel industriel', 200000.00, 'debit'],
            ['280', 'Amortissements', -150000.00, 'credit'],
            
            // STOCKS (Classe 3)
            ['311', 'Matières premières', 80000.00, 'debit'],
            ['312', 'Produits finis', 120000.00, 'debit'],
            
            // CRÉANCES (Classe 4)
            ['411', 'Clients', 180000.00, 'debit'],
            ['416', 'Clients douteux', -15000.00, 'credit'],
            
            // TRÉSORERIE (Classe 5)
            ['512', 'Banque', 95000.00, 'debit'],
            ['53', 'Caisse', 5000.00, 'debit'],
            
            // DETTES (Classe 4 - Passif)
            ['401', 'Fournisseurs', 120000.00, 'credit'],
            ['421', 'Personnel', 45000.00, 'credit'],
            ['443', 'État impôts', 35000.00, 'credit'],
            ['168', 'Emprunts long terme', 200000.00, 'credit']
        ];
        
        foreach ($donnees_test as $donnee) {
            $sql = "INSERT IGNORE INTO soldes_comptes 
                    (exercice_id, numero_compte, libelle_compte, solde, type_solde)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$exercice_id, $donnee[0], $donnee[1], $donnee[2], $donnee[3]]);
        }
        
        echo "✅\n";
    }
    
    private function creerExerciceTest() {
        $sql = "INSERT INTO exercices_comptables 
                (libelle, date_debut, date_fin, statut)
                VALUES ('Exercice Test 2024', '2024-01-01', '2024-12-31', 'actif')";
        $this->db->exec($sql);
    }
}

// EXÉCUTION
try {
    $host = 'localhost';
    $dbname = 'sysco_ohada';
    $username = 'root';
    $password = '123';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 CRÉATION DE LA STRUCTURE COMPLÈTE\n";
    echo "====================================\n";
    
    $creation = new CreationStructureComplete($db);
    $creation->creerStructuresManquantes();
    
    echo "\n✅ STRUCTURE CRÉÉE AVEC SUCCÈS!\n";
    
    // Vérification
    $tables = ['soldes_comptes', 'ratios_financiers', 'analyses_sectorielles', 'historique_ratios'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch() ? '✅' : '❌';
        echo "Table $table: $exists\n";
    }
    
} catch (PDOException $e) {
    die("❌ Erreur: " . $e->getMessage());
}
