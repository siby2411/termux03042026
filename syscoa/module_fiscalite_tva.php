<?php
/**
 * Module Fiscalité TVA
 * Gestion complète de la TVA conforme UEMOA
 */

class ModuleFiscaliteTVA {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Calcul automatique de la TVA sur une écriture
     */
    public function calculerTVASurEcriture($ecriture_id, $type_operation, $base_ht, $taux_tva = 18.0) {
        $montant_tva = $base_ht * ($taux_tva / 100);
        
        $sql = "INSERT INTO operations_tva 
                (ecriture_id, type_operation, base_ht, taux_tva, montant_tva, tva_deductible, date_exigibilite)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
        
        $tva_deductible = ($type_operation == "achat" || $type_operation == "import") ? 1 : 0;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ecriture_id, $type_operation, $base_ht, $taux_tva, $montant_tva, $tva_deductible]);
    }
    
    /**
     * Génération de la déclaration TVA mensuelle
     */
    public function genererDeclarationTVA($mois, $annee) {
        $periode = $annee . "-" . str_pad($mois, 2, "0", STR_PAD_LEFT) . "-01";
        
        $sql = "SELECT 
                    SUM(CASE WHEN type_operation = 'vente' THEN montant_tva ELSE 0 END) as tva_collectee,
                    SUM(CASE WHEN type_operation = 'achat' AND tva_deductible = 1 THEN montant_tva ELSE 0 END) as tva_deductible
                FROM operations_tva 
                WHERE MONTH(date_exigibilite) = ? AND YEAR(date_exigibilite) = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mois, $annee]);
        $tva = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $tva_nette = $tva["tva_collectee"] - $tva["tva_deductible"];
        $credit_tva = ($tva_nette < 0) ? abs($tva_nette) : 0;
        $tva_a_payer = ($tva_nette > 0) ? $tva_nette : 0;
        
        $sql_insert = "INSERT INTO declarations_tva 
                      (periode, tva_collectee, tva_deductible, credit_tva, tva_nette)
                      VALUES (?, ?, ?, ?, ?)";
        
        $stmt_insert = $this->db->prepare($sql_insert);
        return $stmt_insert->execute([$periode, $tva["tva_collectee"], $tva["tva_deductible"], $credit_tva, $tva_a_payer]);
    }
    
    /**
     * État récapitulatif TVA pour vérification
     */
    public function getEtatRecapitulatifTVA($mois, $annee) {
        $sql = "SELECT 
                    ot.type_operation,
                    c.numero_compte,
                    c.libelle_compte,
                    SUM(ot.base_ht) as total_base_ht,
                    SUM(ot.montant_tva) as total_tva,
                    COUNT(*) as nombre_operations
                FROM operations_tva ot
                JOIN ecritures e ON ot.ecriture_id = e.ecriture_id
                JOIN comptes_ohada c ON e.compte_num = c.numero_compte
                WHERE MONTH(ot.date_exigibilite) = ? AND YEAR(ot.date_exigibilite) = ?
                GROUP BY ot.type_operation, c.numero_compte, c.libelle_compte
                ORDER BY ot.type_operation, c.numero_compte";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mois, $annee]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function testerModule() {
        return "✅ Module Fiscalité TVA fonctionnel";
    }
}
?>