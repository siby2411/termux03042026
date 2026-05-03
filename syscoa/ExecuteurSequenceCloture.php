<?php
/**
 * Exécuteur séquentiel des travaux de clôture
 * Orchestre l'exécution dans l'ordre chronologique
 */

require_once 'ModuleTravauxCloture.php';

class ExecuteurSequenceCloture {
    private $moduleCloture;
    
    public function __construct($exercice_id = 1) {
        $this->moduleCloture = new ModuleTravauxCloture($exercice_id);
    }
    
    /**
     * Exécute la séquence complète des travaux de clôture
     */
    public function executerSequenceComplete() {
        $resultats = [];
        
        $sequence = [
            '21-25 Décembre' => 'Calcul des amortissements',
            '26-29 Décembre' => 'Constatations des provisions', 
            '30-31 Décembre' => 'Régularisations et arrêtés'
        ];
        
        echo "🚀 DÉMARRAGE DE LA SÉQUENCE DE CLÔTURE\n";
        echo "========================================\n";
        
        foreach ($sequence as $periode => $tache) {
            echo "\n📅 Période: $periode\n";
            echo "📋 Tâche: $tache\n";
            
            $resultat = $this->moduleCloture->executerTache($tache);
            $resultats[$tache] = $resultat;
            
            if ($resultat['success']) {
                echo "✅ SUCCÈS: " . $resultat['message'] . "\n";
                if (isset($resultat['nb_lignes'])) {
                    echo "📊 Lignes traitées: " . $resultat['nb_lignes'] . "\n";
                }
            } else {
                echo "❌ ÉCHEC: " . $resultat['message'] . "\n";
            }
            
            echo "---\n";
        }
        
        // Affichage du rapport final
        $this->genererRapportFinal($resultats);
        
        return $resultats;
    }
    
    /**
     * Génère un rapport détaillé de l'exécution
     */
    private function genererRapportFinal($resultats) {
        echo "\n\n📊 RAPPORT FINAL DE CLÔTURE\n";
        echo "==========================\n";
        
        $succes = 0;
        $echecs = 0;
        
        foreach ($resultats as $tache => $resultat) {
            if ($resultat['success']) {
                $succes++;
                echo "✅ $tache: SUCCÈS\n";
            } else {
                $echecs++;
                echo "❌ $tache: ÉCHEC - " . $resultat['message'] . "\n";
            }
        }
        
        echo "\n📈 STATISTIQUES:\n";
        echo "Tâches réussies: $succes\n";
        echo "Tâches échouées: $echecs\n";
        echo "Taux de succès: " . round(($succes / count($resultats)) * 100, 2) . "%\n";
        
        // Tableau de bord final
        $tableau_bord = $this->moduleCloture->getTableauBord();
        $this->afficherTableauBord($tableau_bord);
    }
    
    /**
     * Affiche le tableau de bord des travaux
     */
    private function afficherTableauBord($tableau_bord) {
        echo "\n📋 TABLEAU DE BORD DES TRAVAUX\n";
        echo "=============================\n";
        
        foreach ($tableau_bord as $ligne) {
            $statut_emoji = $this->getEmojiStatut($ligne['statut']);
            echo "{$statut_emoji} {$ligne['tache']} ({$ligne['periode_debut']} à {$ligne['periode_fin']})\n";
            echo "   Statut: {$ligne['statut']}";
            if ($ligne['date_realisation']) {
                echo " - Terminé le: {$ligne['date_realisation']}";
            }
            echo "\n";
            
            if ($ligne['nb_amortissements'] > 0) {
                echo "   📊 Amortissements: {$ligne['nb_amortissements']} calculs\n";
            }
            if ($ligne['nb_provisions'] > 0) {
                echo "   📊 Provisions: {$ligne['nb_provisions']} constatations\n";
            }
            if ($ligne['nb_regularisations'] > 0) {
                echo "   📊 Régularisations: {$ligne['nb_regularisations']} opérations\n";
            }
            echo "\n";
        }
    }
    
    private function getEmojiStatut($statut) {
        switch($statut) {
            case 'termine': return '✅';
            case 'en_cours': return '🟡';
            case 'en_attente': return '⏳';
            default: return '❓';
        }
    }
}

// Exécution automatique si le fichier est appelé directement
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $executeur = new ExecuteurSequenceCloture(1);
    $resultats = $executeur->executerSequenceComplete();
}
?>
