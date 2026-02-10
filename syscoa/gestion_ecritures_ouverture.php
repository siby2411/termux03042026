<?php
/**
 * gestion_ecritures_ouverture.php
 * Gestion des écritures de réouverture d'exercice
 */

class GestionEcrituresOuverture {
    private $db;
    
    public function genererEcrituresOuverture($nouvel_exercice_id, $exercice_precedent_id) {
        // Récupérer les soldes de clôture de l'exercice précédent
        $soldes_cloture = $this->getSoldesCloture($exercice_precedent_id);
        
        // Générer les écritures de réouverture
        foreach ($soldes_cloture as $solde) {
            if (abs($solde['solde']) > 0.01) {
                $this->creerEcritureOuverture($nouvel_exercice_id, $solde);
            }
        }
        
        return "Écritures de réouverture générées avec succès";
    }
    
    private function getSoldesCloture($exercice_id) {
        $sql = "SELECT 
                    c.id_compte,
                    c.numero_compte,
                    c.libelle_compte,
                    c.type_compte,
                    SUM(e.debit - e.credit) as solde
                FROM comptes_ohada c
                LEFT JOIN ecritures e ON c.id_compte = e.compte_id
                WHERE e.exercice_id = ?
                AND c.type_compte IN ('actif', 'passif') -- Comptes de bilan seulement
                GROUP BY c.id_compte, c.numero_compte, c.libelle_compte, c.type_compte
                HAVING ABS(SUM(e.debit - e.credit)) > 0.01";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
