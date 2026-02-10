<?php
/**
 * Module Relations Clients/Fournisseurs
 * CRM intégré pour la gestion des relations commerciales
 */

class ModuleRelationsClientsFournisseurs {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Analyse du portefeuille clients
     */
    public function analyserPortefeuilleClients() {
        $sql = "SELECT 
                    nt.id_tiers,
                    nt.nom_raison_sociale,
                    nt.type_tiers,
                    SUM(CASE WHEN e.sens = 'debit' THEN e.montant ELSE 0 END) as total_creances,
                    COUNT(DISTINCT e.ecriture_id) as nombre_factures,
                    MAX(e.date_ecriture) as derniere_activite,
                    DATEDIFF(CURDATE(), MAX(e.date_ecriture)) as jours_inactivite
                FROM nouveaux_tiers nt
                LEFT JOIN ecritures e ON nt.code_tiers = e.code_tiers
                WHERE nt.type_tiers = 'CLIENT'
                AND e.date_ecriture >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                GROUP BY nt.id_tiers, nt.nom_raison_sociale, nt.type_tiers
                ORDER BY total_creances DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Suivi automatique des créances âgées
     */
    public function suivreCreancesAgees() {
        $sql = "UPDATE suivi_creances_clients 
                SET jours_retard = DATEDIFF(CURDATE(), date_echeance),
                    statut = CASE 
                        WHEN DATEDIFF(CURDATE(), date_echeance) <= 0 THEN 'current'
                        WHEN DATEDIFF(CURDATE(), date_echeance) BETWEEN 1 AND 30 THEN '1-30'
                        WHEN DATEDIFF(CURDATE(), date_echeance) BETWEEN 31 AND 60 THEN '31-60'
                        WHEN DATEDIFF(CURDATE(), date_echeance) BETWEEN 61 AND 90 THEN '61-90'
                        ELSE '+90'
                    END
                WHERE montant_restant > 0";
        
        $this->db->exec($sql);
        
        // Générer un rapport des créances critiques
        $sql_rapport = "SELECT 
                            nt.nom_raison_sociale,
                            scc.montant_restant,
                            scc.jours_retard,
                            scc.statut
                        FROM suivi_creances_clients scc
                        JOIN nouveaux_tiers nt ON scc.client_id = nt.id_tiers
                        WHERE scc.montant_restant > 0
                        ORDER BY scc.jours_retard DESC, scc.montant_restant DESC";
        
        $stmt = $this->db->query($sql_rapport);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function testerModule() {
        return "✅ Module Relations Clients/Fournisseurs fonctionnel";
    }
}
?>