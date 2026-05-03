<?php
/**
 * Gestion des Pièces Comptables
 * Module SYSCOHADA - Gestion complète des pièces justificatives
 */

class GestionPiecesComptables {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function saisirPiece($data) {
        $sql = "INSERT INTO pieces_comptables (numero_piece, type_piece, date_piece, montant_total, tiers_id, reference) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['numero'], $data['type'], $data['date'], 
            $data['montant'], $data['tiers_id'], $data['reference']
        ]);
    }
    
    public function listerPieces($statut = null) {
        $sql = "SELECT p.*, t.nom_raison_sociale 
                FROM pieces_comptables p 
                LEFT JOIN nouveaux_tiers t ON p.tiers_id = t.id_tiers";
        
        if ($statut) {
            $sql .= " WHERE p.statut = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$statut]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>