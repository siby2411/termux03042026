<?php
/**
 * gestion_fiscale_uemoa.php
 * Conformité fiscale UEMOA
 */

class GestionFiscaleUEMOA {
    private $db;
    
    public function calculerImpotSocietes($exercice_id) {
        // Calcul du résultat fiscal
        $resultat_fiscal = $this->calculerResultatFiscal($exercice_id);
        
        // Application du barème UEMOA
        $impot = $this->appliquerBaremeUEMOA($resultat_fiscal);
        
        // Déclaration automatique
        $declaration = $this->genererDeclarationFiscale($exercice_id, $impot);
        
        return [
            'resultat_fiscal' => $resultat_fiscal,
            'impot_calcule' => $impot,
            'declaration' => $declaration
        ];
    }
    
    private function calculerResultatFiscal($exercice_id) {
        // Recalcul du résultat avec réintégrations et déductions fiscales
        $sql = "SELECT 
                    -- Produits fiscalement imposables
                    (SELECT COALESCE(SUM(e.credit - e.debit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.classe = '7' 
                     AND e.exercice_id = :exercice_id) as produits_imposables,
                    
                    -- Charges fiscalement déductibles  
                    (SELECT COALESCE(SUM(e.debit - e.credit), 0)
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_id = c.id_compte 
                     WHERE c.classe = '6' 
                     AND e.exercice_id = :exercice_id) as charges_deductibles,
                    
                    -- Réintégrations fiscales
                    (SELECT COALESCE(SUM(montant), 0)
                     FROM reintegrations_fiscales
                     WHERE exercice_id = :exercice_id) as reintegrations,
                    
                    -- Déductions fiscales
                    (SELECT COALESCE(SUM(montant), 0)
                     FROM deductions_fiscales  
                     WHERE exercice_id = :exercice_id) as deductions";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['exercice_id' => $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($data['produits_imposables'] - $data['charges_deductibles']) 
               + $data['reintegrations'] - $data['deductions'];
    }
}
?>
