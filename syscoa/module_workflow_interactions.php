<?php
/**
 * Module Workflow et Interactions
 * Gestion des workflows et interactions entre agents
 */

class ModuleWorkflowInteractions {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Initialisation d'un workflow de validation
     */
    public function initierWorkflowValidation($document_id, $type_document, $montant) {
        // Déterminer le workflow applicable
        $workflow = $this->getWorkflowApplicable($type_document, $montant);
        
        if (!$workflow) {
            return false; // Aucun workflow applicable
        }
        
        // Première étape du workflow
        $premiere_etape = $this->getPremiereEtape($workflow["id_workflow"]);
        
        $sql = "INSERT INTO validations_cours 
                (document_id, type_document, etape_actuelle, date_demande)
                VALUES (?, ?, ?, CURDATE())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$document_id, $type_document, $premiere_etape["id_etape"]]);
        
        $validation_id = $this->db->lastInsertId();
        
        return $validation_id;
    }
    
    /**
     * Traitement d'une validation
     */
    public function traiterValidation($validation_id, $decision, $motif = "") {
        $sql = "UPDATE validations_cours 
                SET statut = ?, date_validation = CURDATE(), motif_rejet = ?
                WHERE id_validation = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$decision, $motif, $validation_id]);
        
        return true;
    }
    
    /**
     * Envoi d'une interaction entre agents
     */
    public function envoyerInteraction($emetteur, $destinataire, $type, $objet, $message, $urgence = "normale", $reference = "") {
        $sql = "INSERT INTO interactions_agents 
                (agent_emetteur, agent_destinataire, type_interaction, objet, message, urgence, document_reference)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$emetteur, $destinataire, $type, $objet, $message, $urgence, $reference]);
    }
    
    private function getWorkflowApplicable($type_document, $montant) {
        $sql = "SELECT * FROM workflows_validation 
                WHERE type_document = ? AND seuil_validation <= ? AND active = TRUE
                ORDER BY seuil_validation DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type_document, $montant]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getPremiereEtape($workflow_id) {
        $sql = "SELECT * FROM etapes_validation 
                WHERE workflow_id = ? 
                ORDER BY ordre_etape ASC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workflow_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function testerModule() {
        return "✅ Module Workflow et Interactions fonctionnel";
    }
}
?>