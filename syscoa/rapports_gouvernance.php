<?php
/**
 * rapports_gouvernance.php
 * Rapports pour le conseil d'administration et actionnaires
 */

class RapportsGouvernance {
    private $db;
    
    public function genererRapportGouvernance($exercice_id) {
        return [
            'synthese_performance' => $this->genererSynthesePerformance($exercice_id),
            'analyse_risques' => $this->analyserRisques($exercice_id),
            'perspectives' => $this->genererPerspectives($exercice_id),
            'recommandations' => $this->genererRecommandations($exercice_id)
        ];
    }
    
    private function genererSynthesePerformance($exercice_id) {
        $sql = "SELECT 
                    -- Chiffre d'affaires
                    (SELECT COALESCE(SUM(credit - debit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte LIKE '70%' 
                     AND e.exercice_id = ?) as chiffre_affaires,
                    
                    -- Résultat d'exploitation
                    (SELECT COALESCE(SUM(credit - debit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte BETWEEN '70' AND '75' 
                     AND e.exercice_id = ?) 
                    - 
                    (SELECT COALESCE(SUM(debit - credit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte BETWEEN '60' AND '65' 
                     AND e.exercice_id = ?) as resultat_exploitation,
                    
                    -- Rentabilité
                    (SELECT COALESCE(SUM(debit - credit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte LIKE '2%' 
                     AND e.exercice_id = ?) as actif_immobilise";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id, $exercice_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
