<?php
/**
 * SCRIPT D'EXÉCUTION AUTOMATIQUE DES CALCULS DE RATIOS
 * À exécuter via cron ou manuellement
 */

require_once 'implementation_analyse_financiere_complete.php';

class ExecutionAutomatiqueRatios {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * EXÉCUTION COMPLÈTE TOUS LES CALCULS
     */
    public function executerCalculsComplets() {
        echo "🎯 EXÉCUTION AUTOMATIQUE DES CALCULS FINANCIERS\n";
        echo "==============================================\n";
        
        // Récupérer tous les exercices actifs
        $sql = "SELECT id_exercice, libelle FROM exercices_comptables WHERE statut = 'actif'";
        $stmt = $this->db->query($sql);
        $exercices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultats = [];
        
        foreach ($exercices as $exercice) {
            echo "\n📊 Calcul pour l'exercice: {$exercice['libelle']}\n";
            
            // Ratios de Liquidité
            $ratios_liquidite = new RatiosLiquidite($this->db);
            $resultats_liquidite = $ratios_liquidite->calculerTousRatiosLiquidite($exercice['id_exercice']);
            
            // Ratios de Solvabilité  
            $ratios_solvabilite = new RatiosSolvabilite($this->db);
            $resultats_solvabilite = $ratios_solvabilite->calculerTousRatiosSolvabilite($exercice['id_exercice']);
            
            // Ratios de Rentabilité (à implémenter)
            // Ratios de Rotation (à implémenter)
            // Ratios d'Endettement (à implémenter)
            
            $resultats[$exercice['libelle']] = [
                'liquidite' => $resultats_liquidite,
                'solvabilite' => $resultats_solvabilite
            ];
            
            echo "✅ Calculs terminés pour {$exercice['libelle']}\n";
        }
        
        $this->genererRapportSynthese($resultats);
        
        return $resultats;
    }
    
    /**
     * GÉNÉRATION DE RAPPORT DE SYNTHÈSE
     */
    private function genererRapportSynthese($resultats) {
        $rapport = "📈 RAPPORT DE SYNTHÈSE DES RATIOS FINANCIERS\n";
        $rapport .= "Généré le: " . date('d/m/Y H:i:s') . "\n\n";
        
        foreach ($resultats as $exercice => $categories) {
            $rapport .= "EXERCICE: $exercice\n";
            $rapport .= str_repeat("-", 50) . "\n";
            
            foreach ($categories as $categorie => $ratios) {
                $rapport .= strtoupper($categorie) . ":\n";
                
                foreach ($ratios as $nom_ratio => $details) {
                    $rapport .= sprintf("  %-25s: %8.4f (%s)\n", 
                        $nom_ratio, 
                        $details['valeur'], 
                        $details['interpretation']
                    );
                }
                $rapport .= "\n";
            }
        }
        
        // Sauvegarde du rapport
        $filename = "rapport_ratios_" . date('Y-m-d_H-i-s') . ".txt";
        file_put_contents($filename, $rapport);
        
        echo "📄 Rapport généré: $filename\n";
        
        return $rapport;
    }
}

// EXÉCUTION DU SCRIPT
try {
    $db = new PDO("mysql:host=127.0.0.1;dbname=sysco_ohada", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $execution = new ExecutionAutomatiqueRatios($db);
    $resultats = $execution->executerCalculsComplets();
    
    echo "\n🎉 TOUS LES CALCULS ONT ÉTÉ EXÉCUTÉS AVEC SUCCÈS!\n";
    
} catch (PDOException $e) {
    die("❌ Erreur: " . $e->getMessage());
}
