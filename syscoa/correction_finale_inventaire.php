<?php
/**
 * correction_finale_inventaire.php
 * Correction finale des tables d'inventaire avec bonnes références
 */

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

class CorrectionFinaleInventaire {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function corrigerInventaireComplet() {
        echo "🔧 CORRECTION FINALE INVENTAIRE\n";
        echo "===============================\n";
        
        $etapes = [
            'diagnostic_structure' => $this->diagnosticStructure(),
            'recreation_tables' => $this->recreerTablesAvecBonnesReferences(),
            'verification_contraintes' => $this->verifierContraintes(),
            'test_fonctionnel' => $this->testerFonctionnalites()
        ];
        
        return $etapes;
    }
    
    private function diagnosticStructure() {
        echo "🔍 Diagnostic de la structure...\n";
        
        $tables = ['articles_stock', 'inventaires_physiques', 'depots_stockage'];
        $resultat = [];
        
        foreach ($tables as $table) {
            try {
                // Vérifier si la table existe
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $existe = $stmt->fetch();
                
                if ($existe) {
                    // Obtenir la structure
                    $stmt_desc = $this->db->query("DESCRIBE $table");
                    $colonnes = $stmt_desc->fetchAll(PDO::FETCH_ASSOC);
                    
                    $cles_primaires = [];
                    foreach ($colonnes as $colonne) {
                        if ($colonne['Key'] == 'PRI') {
                            $cles_primaires[] = $colonne['Field'];
                        }
                    }
                    
                    $resultat[$table] = [
                        'existe' => '✅',
                        'cles_primaires' => implode(', ', $cles_primaires),
                        'nombre_colonnes' => count($colonnes)
                    ];
                } else {
                    $resultat[$table] = [
                        'existe' => '❌',
                        'cles_primaires' => 'N/A',
                        'nombre_colonnes' => 0
                    ];
                }
            } catch (PDOException $e) {
                $resultat[$table] = [
                    'existe' => '❌ ERREUR',
                    'cles_primaires' => 'N/A',
                    'nombre_colonnes' => 0
                ];
            }
        }
        
        return $resultat;
    }
    
    private function recreerTablesAvecBonnesReferences() {
        echo "🔧 Recréation des tables avec bonnes références...\n";
        
        try {
            // Supprimer les tables problématiques si elles existent
            $this->db->exec("DROP TABLE IF EXISTS series_lots");
            $this->db->exec("DROP TABLE IF EXISTS lignes_inventaire");
            
            // Créer lignes_inventaire avec référence correcte à article_code
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS lignes_inventaire (
                    id_ligne_inventaire INT PRIMARY KEY AUTO_INCREMENT,
                    inventaire_id INT NOT NULL,
                    article_id VARCHAR(50) NOT NULL,
                    quantite_theorique DECIMAL(10,2) NOT NULL,
                    quantite_reelle DECIMAL(10,2),
                    ecart_quantite DECIMAL(10,2),
                    valeur_ecart DECIMAL(15,2),
                    motif_ecart TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (inventaire_id) REFERENCES inventaires_physiques(id_inventaire) ON DELETE CASCADE,
                    FOREIGN KEY (article_id) REFERENCES articles_stock(article_code) ON DELETE CASCADE
                )
            ");
            
            // Créer series_lots avec référence correcte à article_code
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS series_lots (
                    id_serie INT PRIMARY KEY AUTO_INCREMENT,
                    article_id VARCHAR(50) NOT NULL,
                    numero_lot VARCHAR(50) NOT NULL,
                    date_fabrication DATE,
                    date_peremption DATE,
                    quantite_initial DECIMAL(10,2) NOT NULL,
                    quantite_restante DECIMAL(10,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (article_id) REFERENCES articles_stock(article_code) ON DELETE CASCADE
                )
            ");
            
            return "✅ Tables recréées avec succès";
            
        } catch (PDOException $e) {
            return "❌ Erreur: " . $e->getMessage();
        }
    }
    
    private function verifierContraintes() {
        echo "🔍 Vérification des contraintes...\n";
        
        $tables = ['lignes_inventaire', 'series_lots'];
        $resultat = [];
        
        foreach ($tables as $table) {
            try {
                // Vérifier si la table existe
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $existe = $stmt->fetch();
                
                if ($existe) {
                    // Vérifier les contraintes foreign key
                    $sql = "SELECT 
                                TABLE_NAME, 
                                COLUMN_NAME, 
                                CONSTRAINT_NAME, 
                                REFERENCED_TABLE_NAME, 
                                REFERENCED_COLUMN_NAME
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = 'sysco_ohada' 
                            AND TABLE_NAME = '$table'
                            AND REFERENCED_TABLE_NAME IS NOT NULL";
                    
                    $stmt_fk = $this->db->query($sql);
                    $contraintes = $stmt_fk->fetchAll(PDO::FETCH_ASSOC);
                    
                    $resultat[$table] = [
                        'existe' => '✅',
                        'contraintes_fk' => count($contraintes),
                        'details' => $contraintes
                    ];
                } else {
                    $resultat[$table] = [
                        'existe' => '❌',
                        'contraintes_fk' => 0,
                        'details' => []
                    ];
                }
            } catch (PDOException $e) {
                $resultat[$table] = [
                    'existe' => '❌ ERREUR',
                    'contraintes_fk' => 0,
                    'details' => []
                ];
            }
        }
        
        return $resultat;
    }
    
    private function testerFonctionnalites() {
        echo "🧪 Test des fonctionnalités...\n";
        
        $tests = [];
        
        try {
            // Test 1: Insertion d'un dépôt de test
            $this->db->exec("INSERT IGNORE INTO depots_stockage (code_depot, libelle_depot) VALUES ('DEP-TEST', 'Dépôt Test')");
            $tests['creation_depot'] = '✅';
            
            // Test 2: Insertion d'un article de test
            $this->db->exec("INSERT IGNORE INTO articles_stock (article_code, designation, compte_stock, unite_mesure) VALUES ('ART-TEST', 'Article Test', '31110000', 'UNITE')");
            $tests['creation_article'] = '✅';
            
            // Test 3: Insertion d'un inventaire de test
            $this->db->exec("INSERT IGNORE INTO inventaires_physiques (code_inventaire, depot_id, date_inventaire, responsable) VALUES ('INV-TEST', 1, CURDATE(), 'Testeur')");
            $tests['creation_inventaire'] = '✅';
            
            // Test 4: Insertion d'une ligne d'inventaire
            $this->db->exec("INSERT IGNORE INTO lignes_inventaire (inventaire_id, article_id, quantite_theorique) VALUES (1, 'ART-TEST', 100.00)");
            $tests['creation_ligne_inventaire'] = '✅';
            
            // Test 5: Insertion d'une série/lot
            $this->db->exec("INSERT IGNORE INTO series_lots (article_id, numero_lot, quantite_initial, quantite_restante) VALUES ('ART-TEST', 'LOT-TEST', 50.00, 50.00)");
            $tests['creation_serie_lot'] = '✅';
            
        } catch (PDOException $e) {
            $tests['erreur_tests'] = "❌ " . $e->getMessage();
        }
        
        return $tests;
    }
}

// EXÉCUTION
echo "🎯 CORRECTION FINALE DES TABLES D'INVENTAIRE\n";
echo "============================================\n";

$correction = new CorrectionFinaleInventaire($db);
$resultat = $correction->corrigerInventaireComplet();

echo "\n📊 RAPPORT DE CORRECTION:\n";
foreach ($resultat as $etape => $details) {
    echo "\n$etape:\n";
    if (is_array($details)) {
        foreach ($details as $key => $value) {
            if (is_array($value)) {
                echo "  $key:\n";
                foreach ($value as $sous_key => $sous_value) {
                    if (is_array($sous_value)) {
                        echo "    $sous_key: " . json_encode($sous_value) . "\n";
                    } else {
                        echo "    $sous_key: $sous_value\n";
                    }
                }
            } else {
                echo "  $key: $value\n";
            }
        }
    } else {
        echo "  $details\n";
    }
}

echo "\n";
echo "========================================\n";

// Vérification finale du succès
$succes = true;
foreach ($resultat['verification_contraintes'] as $table => $info) {
    if ($info['existe'] !== '✅' || $info['contraintes_fk'] == 0) {
        $succes = false;
        break;
    }
}

if ($succes && !isset($resultat['test_fonctionnel']['erreur_tests'])) {
    echo "🎉 CORRECTION RÉUSSIE! Toutes les tables sont fonctionnelles.\n";
} else {
    echo "⚠️  Certains problèmes persistent. Voir le rapport ci-dessus.\n";
}
?>
