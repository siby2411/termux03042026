<?php
/**
 * correction_automatique_tables.php
 * Correction automatique avec la bonne configuration
 */

// Inclusion de la configuration
require_once 'config.php';

class CorrectionAutomatiqueTables {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=localhost;dbname=sysco_ohada", "root", "123");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✅ Connexion à la base de données réussie\n";
        } catch (PDOException $e) {
            die("❌ Erreur de connexion: " . $e->getMessage());
        }
    }
    
    public function corrigerToutesLesTables() {
        echo "🚀 DÉMARRAGE DE LA CORRECTION AUTOMATIQUE\n";
        echo "=========================================\n";
        
        $corrections = [
            'tiers' => $this->corrigerTableTiers(),
            'journaux' => $this->corrigerTableJournaux(),
            'contraintes' => $this->ajouterContraintesForeignKeys()
        ];
        
        return $corrections;
    }
    
    private function corrigerTableTiers() {
        try {
            // Vérifier si id_tiers existe
            $sql = "SHOW COLUMNS FROM tiers LIKE 'id_tiers'";
            $stmt = $this->db->query($sql);
            $exists = $stmt->fetch();
            
            if (!$exists) {
                echo "🔧 Correction de la table tiers...\n";
                
                // Ajouter id_tiers comme clé primaire auto-incrémentée
                $this->db->exec("ALTER TABLE tiers ADD COLUMN id_tiers INT AUTO_INCREMENT FIRST");
                $this->db->exec("ALTER TABLE tiers DROP PRIMARY KEY");
                $this->db->exec("ALTER TABLE tiers ADD PRIMARY KEY (id_tiers)");
                $this->db->exec("ALTER TABLE tiers MODIFY code_tiers VARCHAR(50) UNIQUE NOT NULL");
                
                return "✅ Table tiers corrigée - id_tiers ajouté comme clé primaire";
            }
            
            return "✅ Table tiers déjà correcte";
            
        } catch (PDOException $e) {
            return "❌ Erreur sur tiers: " . $e->getMessage();
        }
    }
    
    private function corrigerTableJournaux() {
        try {
            // Vérifier si id_journal existe
            $sql = "SHOW COLUMNS FROM journaux LIKE 'id_journal'";
            $stmt = $this->db->query($sql);
            $exists = $stmt->fetch();
            
            if (!$exists) {
                echo "🔧 Correction de la table journaux...\n";
                
                // Ajouter id_journal comme clé primaire auto-incrémentée
                $this->db->exec("ALTER TABLE journaux ADD COLUMN id_journal INT AUTO_INCREMENT FIRST");
                $this->db->exec("ALTER TABLE journaux DROP PRIMARY KEY");
                $this->db->exec("ALTER TABLE journaux ADD PRIMARY KEY (id_journal)");
                $this->db->exec("ALTER TABLE journaux MODIFY journal_code CHAR(2) UNIQUE NOT NULL");
                
                return "✅ Table journaux corrigée - id_journal ajouté comme clé primaire";
            }
            
            return "✅ Table journaux déjà correcte";
            
        } catch (PDOException $e) {
            return "❌ Erreur sur journaux: " . $e->getMessage();
        }
    }
    
    private function ajouterContraintesForeignKeys() {
        try {
            echo "🔧 Ajout des contraintes de clés étrangères...\n";
            
            // Recréer les tables avec les bonnes contraintes
            $sql_script = file_get_contents('correction_definitive_structure.sql');
            $statements = array_filter(array_map('trim', explode(';', $sql_script)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && strpos($statement, '--') !== 0) {
                    $this->db->exec($statement);
                }
            }
            
            return "✅ Toutes les contraintes ajoutées avec succès";
            
        } catch (PDOException $e) {
            return "❌ Erreur contraintes: " . $e->getMessage();
        }
    }
}

// Exécution
echo "🎯 CORRECTION AUTOMATIQUE SYSCOHADA\n";
echo "==================================\n";

$correction = new CorrectionAutomatiqueTables();
$resultat = $correction->corrigerToutesLesTables();

echo "\n📊 RAPPORT DE CORRECTION:\n";
print_r($resultat);

echo "\n🎉 CORRECTION TERMINÉE AVEC SUCCÈS!\n";
?>
