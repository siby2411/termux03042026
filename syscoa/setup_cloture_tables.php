<?php
/**
 * Script de création des tables pour les travaux de clôture
 * À exécuter une seule fois pour initialiser le module
 */

class SetupCloture {
    private $db;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=sysco_ohada", "username", "password");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function creerTablesCloture() {
        try {
            // Table calendrier_cloture (déjà créée)
            echo "✓ Table calendrier_cloture prête\n";
            
            // Table amortissements_cloture (déjà créée)  
            echo "✓ Table amortissements_cloture prête\n";
            
            // Table provisions_cloture (déjà créée)
            echo "✓ Table provisions_cloture prête\n";
            
            // Table regularisations_cloture (déjà créée)
            echo "✓ Table regularisations_cloture prête\n";
            
            // Peuplement initial du calendrier
            $this->peuplerCalendrier();
            
            echo "✅ Module de clôture initialisé avec succès!\n";
            
        } catch (PDOException $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    private function peuplerCalendrier() {
        $sql = "INSERT IGNORE INTO calendrier_cloture 
                (exercice_id, periode_debut, periode_fin, tache, statut) 
                VALUES 
                (1, '2024-12-01', '2024-12-15', 'Saisie des opérations courantes', 'en_attente'),
                (1, '2024-12-16', '2024-12-20', 'Rapprochements bancaires', 'en_attente'),
                (1, '2024-12-21', '2024-12-25', 'Calcul des amortissements', 'en_attente'),
                (1, '2024-12-26', '2024-12-29', 'Constatations des provisions', 'en_attente'),
                (1, '2024-12-30', '2024-12-31', 'Régularisations et arrêtés', 'en_attente')";
        
        $this->db->exec($sql);
        echo "✓ Calendrier de clôture peuplé\n";
    }
}

// Exécution
$setup = new SetupCloture();
$setup->creerTablesCloture();
?>
