<?php
/**
 * États Financiers Complets
 * Module SYSCOHADA - Génération des états financiers
 */

class EtatsFinanciersComplets {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function genererBilan($exercice_id) {
        $sql = "SELECT 
                    c.classe,
                    c.numero_compte,
                    c.libelle_compte,
                    SUM(e.debit - e.credit) as solde
                FROM comptes_ohada c
                LEFT JOIN ecritures e ON c.id_compte = e.compte_id
                WHERE e.id_exercice = ?
                GROUP BY c.id_compte, c.classe, c.numero_compte, c.libelle_compte
                HAVING ABS(SUM(e.debit - e.credit)) > 0.01
                ORDER BY c.numero_compte";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>