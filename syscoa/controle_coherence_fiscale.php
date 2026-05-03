<?php
/**
 * controle_coherence_fiscale.php
 * Contrôles de cohérence fiscale UEMOA
 */

class ControleCoherenceFiscale {
    private $db;
    
    public function executerControlesFiscaux($exercice_id) {
        return [
            'tva' => $this->controlerTVA($exercice_id),
            'impots' => $this->controlerImpots($exercice_id),
            'declarations' => $this->controlerDeclarations($exercice_id),
            'retour_impots' => $this->controlerRetoursImpots($exercice_id)
        ];
    }
    
    private function controlerTVA($exercice_id) {
        $sql = "SELECT 
                    -- TVA collectée
                    (SELECT COALESCE(SUM(credit - debit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte LIKE '443%' 
                     AND e.exercice_id = ?) as tva_collectee,
                    
                    -- TVA déductible
                    (SELECT COALESCE(SUM(debit - credit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte LIKE '445%' 
                     AND e.exercice_id = ?) as tva_deductible,
                    
                    -- TVA à payer
                    (SELECT COALESCE(SUM(debit - credit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.numero_compte = '4441' 
                     AND e.exercice_id = ?) as tva_a_payer";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $tva = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $tva_calculee = $tva['tva_collectee'] - $tva['tva_deductible'];
        $ecart = abs($tva_calculee - $tva['tva_a_payer']);
        
        return [
            'conforme' => $ecart < 0.01,
            'tva_collectee' => $tva['tva_collectee'],
            'tva_deductible' => $tva['tva_deductible'],
            'tva_calculee' => $tva_calculee,
            'tva_comptable' => $tva['tva_a_payer'],
            'ecart' => $ecart
        ];
    }
}
?>
