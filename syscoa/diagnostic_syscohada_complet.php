<?php
/**
 * diagnostic_syscohada_complet.php
 * Audit complet de la conformité SYSCOHADA
 */

class DiagnosticSyscohada {
    private $db;
    
    public function __construct() {
        require_once 'config.php';
        $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    }
    
    public function executerDiagnosticComplet() {
        $resultats = [
            'modules_existants' => $this->analyserModulesExistants(),
            'comptes_ohada' => $this->verifierPlanComptable(),
            'états_financiers' => $this->verifierEtatsFinanciers(),
            'controles_conformite' => $this->verifierControlesConformite(),
            'chainons_manquants' => $this->identifierChainonsManquants()
        ];
        
        return $resultats;
    }
    
    private function identifierChainonsManquants() {
        $chainons = [];
        
        // Vérification de la chaîne comptable complète
        $etapes_chaines = [
            'Saisie des pièces justificatives' => $this->verifierTable('pieces_comptables'),
            'Contrôle des pièces' => $this->verifierTable('controles_pieces'),
            'Numérotation des écritures' => $this->verifierChamp('ecritures', 'numero_piece'),
            'Lettrage automatique' => $this->verifierTable('lettrage_automatique'),
            'Rapprochement systématique' => $this->verifierTable('rapprochements_periodiques'),
            'Centralisation des journaux' => $this->verifierTable('centralisation_journaux'),
            'Balance avant inventaire' => $this->verifierProcedure('generer_balance_avant_inventaire'),
            'Écritures de régularisation' => $this->verifierTable('ecritures_regularisation'),
            'Écritures de réouverture' => $this->verifierTable('ecritures_reouverture'),
            'Contrôle des équilibres' => $this->verifierProcedure('controle_equilibres_bilan')
        ];
        
        foreach ($etapes_chaines as $etape => $existe) {
            if (!$existe) {
                $chainons[] = $etape;
            }
        }
        
        return $chainons;
    }
}
?>
