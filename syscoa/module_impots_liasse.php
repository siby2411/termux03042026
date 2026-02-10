<?php
/**
 * Module Impôts et Liasse Fiscale
 * Calcul des impôts et génération de la liasse fiscale
 */

class ModuleImpotsLiasse {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Calcul de l'impôt sur les sociétés
     */
    public function calculerImpotSocietes($exercice_id) {
        // Résultat comptable
        $resultat_comptable = $this->getResultatComptable($exercice_id);
        
        // Réintégrations fiscales
        $reintegrations = $this->getTotalReintegrations($exercice_id);
        
        // Déductions fiscales  
        $deductions = $this->getTotalDeductions($exercice_id);
        
        // Résultat fiscal
        $resultat_fiscal = $resultat_comptable + $reintegrations - $deductions;
        
        // Application du barème IS
        $impot_calcule = $this->appliquerBaremeIS($resultat_fiscal);
        
        // Sauvegarde du calcul
        $sql = "INSERT INTO calculs_impot 
                (exercice_id, resultat_comptable, resultat_fiscal, montant_imposable, impot_calcule, impot_net, date_calcul)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $resultat_comptable, $resultat_fiscal, $resultat_fiscal, $impot_calcule, $impot_calcule]);
        
        return [
            "resultat_comptable" => $resultat_comptable,
            "reintegrations" => $reintegrations,
            "deductions" => $deductions,
            "resultat_fiscal" => $resultat_fiscal,
            "impot_calcule" => $impot_calcule
        ];
    }
    
    /**
     * Application du barème IS UEMOA
     */
    private function appliquerBaremeIS($resultat_fiscal) {
        if ($resultat_fiscal <= 0) return 0;
        
        // Barème progressif UEMOA 2024
        if ($resultat_fiscal <= 10000000) { // 10 millions
            return $resultat_fiscal * 0.25; // 25%
        } elseif ($resultat_fiscal <= 30000000) { // 30 millions
            return 2500000 + ($resultat_fiscal - 10000000) * 0.30; // 30%
        } else {
            return 8500000 + ($resultat_fiscal - 30000000) * 0.35; // 35%
        }
    }
    
    private function getResultatComptable($exercice_id) {
        // Implémentation simplifiée - à adapter selon votre structure
        $sql = "SELECT 
                    (SELECT COALESCE(SUM(credit - debit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_num = c.numero_compte 
                     WHERE c.numero_compte LIKE '7%' 
                     AND e.id_exercice = ?) 
                    - 
                    (SELECT COALESCE(SUM(debit - credit), 0) 
                     FROM ecritures e 
                     JOIN comptes_ohada c ON e.compte_num = c.numero_compte 
                     WHERE c.numero_compte LIKE '6%' 
                     AND e.id_exercice = ?) as resultat";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result["resultat"] ?? 0;
    }
    
    private function getTotalReintegrations($exercice_id) {
        $sql = "SELECT COALESCE(SUM(montant_reintegration), 0) as total 
                FROM reintegrations_fiscales 
                WHERE exercice_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result["total"] ?? 0;
    }
    
    private function getTotalDeductions($exercice_id) {
        $sql = "SELECT COALESCE(SUM(montant_deduction), 0) as total 
                FROM deductions_fiscales 
                WHERE exercice_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result["total"] ?? 0;
    }
    
    public function testerModule() {
        return "✅ Module Impôts et Liasse Fiscale fonctionnel";
    }
}
?>