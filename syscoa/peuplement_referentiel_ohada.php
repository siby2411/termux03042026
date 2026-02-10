<?php
/**
 * Peuplement des données de référence OHADA
 * Comptes, familles, centres analytiques, etc.
 */

class PeuplementReferentielOhada {
    private $db;
    
    public function __construct() {
        require_once 'config.php';
        $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function peuplerReferentielComplet() {
        try {
            echo "📊 PEUPLEMENT DU RÉFÉRENTIEL OHADA\n";
            echo "==================================\n";
            
            $this->peuplerFamillesImmobilisations();
            $this->peuplerCentresAnalytiques();
            $this->peuplerCategoriesStock();
            $this->peuplerProceduresControle();
            
            echo "✅ RÉFÉRENTIEL OHADA PEUPLÉ AVEC SUCCÈS!\n";
            
        } catch (PDOException $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    private function peuplerFamillesImmobilisations() {
        $familles = [
            // Code, Libellé, Compte Immob, Compte Amort, Compte Dotation, Durée, Taux
            ['IMM-C', 'Constructions', 2011, 2811, 6811, 20, 5.00],
            ['IMM-I', 'Installations techniques', 2031, 2831, 6831, 10, 10.00],
            ['IMM-M', 'Matériel et outillage', 2041, 2841, 6841, 5, 20.00],
            ['IMM-MO', 'Mobilier de bureau', 2051, 2851, 6851, 10, 10.00],
            ['IMM-V', 'Véhicules de transport', 2061, 2861, 6861, 5, 20.00],
            ['IMM-O', 'Ordinateurs et logiciels', 2081, 2881, 6881, 3, 33.33]
        ];
        
        $sql = "INSERT IGNORE INTO familles_immobilisations 
                (code_famille, libelle_famille, compte_immobilisation, compte_amortissement, compte_dotation, duree_amortissement, taux_amortissement) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        foreach ($familles as $famille) {
            $stmt->execute($famille);
        }
        
        echo "✓ Familles d'immobilisations peuplées\n";
    }
    
    private function peuplerCentresAnalytiques() {
        $centres = [
            // Code, Libellé, Type, Parent
            ['DIR', 'Direction générale', 'fonctionnel', null],
            ['ADM', 'Administration', 'fonctionnel', 1],
            ['FIN', 'Finances et comptabilité', 'fonctionnel', 1],
            ['COM', 'Commercial', 'fonctionnel', 1],
            ['PROD', 'Production', 'section', null],
            ['AT1', 'Atelier 1', 'activite', 5],
            ['AT2', 'Atelier 2', 'activite', 5],
            ['LOG', 'Logistique', 'section', null],
            ['ACH', 'Achats', 'activite', 8],
            ['STK', 'Gestion des stocks', 'activite', 8]
        ];
        
        $sql = "INSERT IGNORE INTO centres_analytiques 
                (code_centre, libelle_centre, type_centre, centre_parent) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        foreach ($centres as $centre) {
            $stmt->execute($centre);
        }
        
        echo "✓ Centres analytiques peuplés\n";
    }
    
    private function peuplerCategoriesStock() {
        $categories = [
            // Code, Libellé, Compte Stock
            ['MAT-P', 'Matières premières', 3111],
            ['MAT-C', 'Matières consommables', 3121],
            ['PROD-ENC', 'Produits en cours', 3331],
            ['PROD-FIN', 'Produits finis', 3511],
            ['PROD-INT', 'Produits intermédiaires', 3521],
            ['MERCE', 'Marchandises', 3711]
        ];
        
        $sql = "INSERT IGNORE INTO categories_stock 
                (code_categorie, libelle_categorie, compte_stock) 
                VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        foreach ($categories as $categorie) {
            $stmt->execute($categorie);
        }
        
        echo "✓ Catégories de stock peuplées\n";
    }
    
    private function peuplerProceduresControle() {
        $procedures = [
            // Code, Libellé, Processus, Fréquence, Responsable
            ['CTL-CAIS', 'Contrôle de caisse', 'Trésorerie', 'quotidien', 'Caissier'],
            ['CTL-BQ', 'Rapprochement bancaire', 'Trésorerie', 'mensuel', 'Comptable'],
            ['CTL-FAC', 'Contrôle des factures', 'Achats/Ventes', 'quotidien', 'Comptable'],
            ['CTL-STK', 'Inventaire physique', 'Stocks', 'annuel', 'Responsable stock'],
            ['CTL-IMM', 'Contrôle des immobilisations', 'Immobilisations', 'annuel', 'Comptable'],
            ['CTL-CPT', 'Contrôle des écritures', 'Comptabilité', 'mensuel', 'Chef comptable']
        ];
        
        $sql = "INSERT IGNORE INTO procedures_controle 
                (code_procedure, libelle_procedure, processus_concerne, frequence, responsable) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        foreach ($procedures as $procedure) {
            $stmt->execute($procedure);
        }
        
        echo "✓ Procédures de contrôle peuplées\n";
    }
}

// Exécution
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $peuplement = new PeuplementReferentielOhada();
    $peuplement->peuplerReferentielComplet();
}
?>
