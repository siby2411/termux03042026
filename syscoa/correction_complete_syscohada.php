<?php
/**
 * correction_complete_syscohada.php
 * Correction complète et définitive des tables SYSCOHADA
 */

// Configuration
$host = 'localhost';
$dbname = 'sysco_ohada';
$username = 'root';
$password = '123';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion à la base de données réussie\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage());
}

class CorrectionCompleteSyscohada {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function executerCorrectionComplete() {
        echo "🚀 DÉMARRAGE DE LA CORRECTION COMPLÈTE SYSCOHADA\n";
        echo "================================================\n";
        
        $etapes = [
            '1. Pré-vérification' => $this->preVerification(),
            '2. Correction tables principales' => $this->corrigerTablesPrincipales(),
            '3. Création nouvelles tables' => $this->creerNouvellesTables(),
            '4. Migration données' => $this->migrerDonnees(),
            '5. Vérification finale' => $this->verifierCorrection()
        ];
        
        return $etapes;
    }
    
    private function preVerification() {
        echo "🔍 Pré-vérification des tables existantes...\n";
        
        $tables = ['tiers', 'journaux', 'pieces_comptables', 'controles_pieces'];
        $resultat = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $resultat[$table] = $stmt->fetch() ? 'EXISTE' : 'NEXISTE_PAS';
            } catch (PDOException $e) {
                $resultat[$table] = 'ERREUR: ' . $e->getMessage();
            }
        }
        
        return $resultat;
    }
    
    private function corrigerTablesPrincipales() {
        echo "🔧 Correction des tables principales...\n";
        
        try {
            // Exécuter le script SQL de correction
            $sql_script = file_get_contents('correction_complete_tables.sql');
            if (!$sql_script) {
                // Si le fichier n'existe pas, exécuter les commandes directement
                return $this->executerCommandesDirectes();
            }
            
            $statements = array_filter(array_map('trim', explode(';', $sql_script)));
            foreach ($statements as $statement) {
                if (!empty($statement) && strpos($statement, '--') !== 0) {
                    $this->db->exec($statement);
                }
            }
            
            return "✅ Tables principales corrigées";
            
        } catch (PDOException $e) {
            return "❌ Erreur: " . $e->getMessage();
        }
    }
    
    private function executerCommandesDirectes() {
        // Commande par commande pour plus de contrôle
        $commandes = [
            "DROP TABLE IF EXISTS controles_pieces",
            "DROP TABLE IF EXISTS pieces_comptables", 
            "DROP TABLE IF EXISTS lettrage_automatique",
            "DROP TABLE IF EXISTS centralisation_journaux",
            "CREATE TABLE IF NOT EXISTS nouveaux_tiers (
                id_tiers INT PRIMARY KEY AUTO_INCREMENT,
                code_tiers VARCHAR(50) UNIQUE NOT NULL,
                nom_raison_sociale VARCHAR(255) NOT NULL,
                type_tiers ENUM('CLIENT','FOURNISSEUR','AUTRE') NOT NULL,
                contact_personne VARCHAR(100),
                telephone VARCHAR(30),
                email VARCHAR(100),
                adresse TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS nouveaux_journaux (
                id_journal INT PRIMARY KEY AUTO_INCREMENT,
                journal_code CHAR(2) UNIQUE NOT NULL,
                intitule VARCHAR(100) NOT NULL,
                type_journal ENUM('ACHAT','VENTE','TRESORERIE','OD') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($commandes as $i => $commande) {
            try {
                $this->db->exec($commande);
                echo "  ✅ Commande " . ($i+1) . "/" . count($commandes) . " exécutée\n";
            } catch (PDOException $e) {
                echo "  ⚠️  Commande " . ($i+1) . " échouée: " . $e->getMessage() . "\n";
            }
        }
        
        return "✅ Commandes directes exécutées";
    }
    
    private function creerNouvellesTables() {
        echo "📦 Création des nouvelles tables...\n";
        
        $tables = [
            'pieces_comptables' => "
                CREATE TABLE pieces_comptables (
                    id_piece INT PRIMARY KEY AUTO_INCREMENT,
                    numero_piece VARCHAR(50) UNIQUE NOT NULL,
                    type_piece ENUM('facture', 'bon_commande', 'bon_livraison', 'bon_caisse', 'releve_bancaire', 'note_frais') NOT NULL,
                    date_piece DATE NOT NULL,
                    montant_total DECIMAL(15,2) NOT NULL,
                    tiers_id INT,
                    reference VARCHAR(100),
                    fichier_joint VARCHAR(255),
                    statut ENUM('saisi', 'controle', 'comptabilise', 'rejete') DEFAULT 'saisi',
                    motif_rejet TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            
            'controles_pieces' => "
                CREATE TABLE controles_pieces (
                    id_controle INT PRIMARY KEY AUTO_INCREMENT,
                    piece_id INT NOT NULL,
                    type_controle ENUM('formalite', 'arithmetique', 'comptable', 'legal') NOT NULL,
                    resultat ENUM('conforme', 'non_conforme', 'a_corriger') NOT NULL,
                    observations TEXT,
                    controleur_id INT,
                    date_controle DATE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            
            'lettrage_automatique' => "
                CREATE TABLE lettrage_automatique (
                    id_lettrage INT PRIMARY KEY AUTO_INCREMENT,
                    compte_id INT,
                    tiers_id INT,
                    date_lettrage DATE NOT NULL,
                    montant_lettre DECIMAL(15,2) NOT NULL,
                    sens ENUM('debit', 'credit') NOT NULL,
                    ecritures_lettrees TEXT,
                    statut ENUM('partiel', 'total') NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            
            'centralisation_journaux' => "
                CREATE TABLE centralisation_journaux (
                    id_centralisation INT PRIMARY KEY AUTO_INCREMENT,
                    journal_id INT,
                    exercice_id INT NOT NULL,
                    periode DATE NOT NULL,
                    total_debit DECIMAL(15,2) NOT NULL,
                    total_credit DECIMAL(15,2) NOT NULL,
                    solde_periode DECIMAL(15,2) NOT NULL,
                    nombre_ecritures INT NOT NULL,
                    date_centralisation DATE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            "
        ];
        
        $resultat = [];
        foreach ($tables as $nom => $sql) {
            try {
                $this->db->exec($sql);
                $resultat[$nom] = "✅ Créée";
            } catch (PDOException $e) {
                $resultat[$nom] = "❌ Erreur: " . $e->getMessage();
            }
        }
        
        return $resultat;
    }
    
    private function migrerDonnees() {
        echo "📊 Migration des données...\n";
        
        try {
            // Migrer les tiers
            $stmt = $this->db->query("SELECT COUNT(*) FROM nouveaux_tiers");
            $count_nouveaux_tiers = $stmt->fetchColumn();
            
            if ($count_nouveaux_tiers == 0) {
                $this->db->exec("
                    INSERT INTO nouveaux_tiers (code_tiers, nom_raison_sociale, type_tiers, contact_personne, telephone, email, adresse)
                    SELECT code_tiers, nom_raison_sociale, type_tiers, contact_personne, telephone, email, adresse 
                    FROM tiers
                ");
            }
            
            // Migrer les journaux
            $stmt = $this->db->query("SELECT COUNT(*) FROM nouveaux_journaux");
            $count_nouveaux_journaux = $stmt->fetchColumn();
            
            if ($count_nouveaux_journaux == 0) {
                $this->db->exec("
                    INSERT INTO nouveaux_journaux (journal_code, intitule, type_journal)
                    SELECT journal_code, intitule, type_journal 
                    FROM journaux
                ");
            }
            
            return "✅ Données migrées avec succès";
            
        } catch (PDOException $e) {
            return "❌ Erreur migration: " . $e->getMessage();
        }
    }
    
    private function verifierCorrection() {
        echo "🔍 Vérification finale...\n";
        
        $tables_verifier = [
            'nouveaux_tiers', 'nouveaux_journaux', 'pieces_comptables', 
            'controles_pieces', 'lettrage_automatique', 'centralisation_journaux'
        ];
        
        $resultat = [];
        foreach ($tables_verifier as $table) {
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $resultat[$table] = $stmt->fetch() ? '✅ EXISTE' : '❌ MANQUANTE';
            } catch (PDOException $e) {
                $resultat[$table] = '❌ ERREUR';
            }
        }
        
        return $resultat;
    }
}

// EXÉCUTION
echo "🎯 CORRECTION COMPLÈTE SYSCOHADA\n";
echo "===============================\n";

$correction = new CorrectionCompleteSyscohada($db);
$resultat = $correction->executerCorrectionComplete();

echo "\n📊 RAPPORT FINAL:\n";
foreach ($resultat as $etape => $details) {
    echo "\n$etape:\n";
    if (is_array($details)) {
        foreach ($details as $key => $value) {
            echo "  $key: $value\n";
        }
    } else {
        echo "  $details\n";
    }
}

echo "\n";
echo "========================================\n";
echo "🎉 CORRECTION TERMINÉE!\n";
echo "========================================\n";

// Vérification si tout est OK
$toutes_ok = true;
foreach ($resultat as $etape => $details) {
    if (is_array($details)) {
        foreach ($details as $value) {
            if (strpos($value, '❌') !== false) {
                $toutes_ok = false;
                break 2;
            }
        }
    } elseif (strpos($details, '❌') !== false) {
        $toutes_ok = false;
        break;
    }
}

if ($toutes_ok) {
    echo "✅ TOUT EST CORRECT! Le système SYSCOHADA est prêt.\n";
} else {
    echo "⚠️  Certains problèmes persistent. Consultez le rapport ci-dessus.\n";
}
?>
