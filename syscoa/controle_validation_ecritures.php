<?php
/**
 * Contrôle et Validation des Écritures
 * Module SYSCOHADA - Contrôles automatiques de cohérence
 */

class ControleValidationEcritures {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function controlerEquilibreExercice($exercice_id) {
        $sql = "SELECT 
                    SUM(debit) as total_debit,
                    SUM(credit) as total_credit,
                    ABS(SUM(debit) - SUM(credit)) as ecart
                FROM ecritures 
                WHERE id_exercice = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'conforme' => abs($resultat['ecart']) < 0.01,
            'total_debit' => $resultat['total_debit'],
            'total_credit' => $resultat['total_credit'],
            'ecart' => $resultat['ecart']
        ];
    }
}
?>