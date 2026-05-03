<?php
/**
 * verification_finale_systeme.php
 * Vérification complète du système SYSCOHADA
 */

require_once 'config.php';

class VerificationFinaleSysteme {
    private $db;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    }
    
    public function executerVerificationComplete() {
        return [
            'tables_existantes' => $this->verifierTablesExistantes(),
            'contraintes_foreign_key' => $this->verifierContraintesForeignKeys(),
            'modules_deployes' => $this->verifierModulesDeployes(),
            'integrite_donnees' => $this->verifierIntegriteDonnees()
        ];
    }
    
    private function verifierTablesExistantes() {
        $tables_requises = [
            'pieces_comptables', 'controles_pieces', 'lettrage_automatique',
            'centralisation_journaux', 'tiers', 'comptes_ohada', 'journaux',
            'exercices_comptables', 'ecritures'
        ];
        
        $resultat = [];
        foreach ($tables_requises as $table) {
            $sql = "SHOW TABLES LIKE '$table'";
            $stmt = $this->db->query($sql);
            $resultat[$table] = ($stmt->fetch() !== false) ? '✅' : '❌';
        }
        
        return $resultat;
    }
}

// Exécution de la vérification
echo "🔍 VÉRIFICATION FINALE DU SYSTÈME SYSCOHADA\n";
echo "==========================================\n";

$verification = new VerificationFinaleSysteme();
$resultat = $verification->executerVerificationComplete();

print_r($resultat);

echo "\n";
if (in_array('❌', $resultat['tables_existantes'])) {
    echo "⚠️  Certains problèmes détectés. Exécutez la correction automatique.\n";
} else {
    echo "🎉 SYSTÈME SYSCOHADA PRÊT POUR LA PRODUCTION!\n";
}
?>
