<?php
/**
 * deployment_final_modules.php
 * Déploiement final de tous les modules SYSCOHADA
 */

$host = '127.0.0.1';
$dbname = 'sysco_ohada';
$username = 'root';
$password = '123';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Connexion à la base de données réussie\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage());
}

class DeploymentFinalModules {
    private $modules = [
        'gestion_pieces_comptables.php' => [
            'nom' => 'Gestion des Pièces Comptables',
            'contenu' => '<?php
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
            $data[\'numero\'], $data[\'type\'], $data[\'date\'], 
            $data[\'montant\'], $data[\'tiers_id\'], $data[\'reference\']
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
?>'
        ],
        
        'controle_validation_ecritures.php' => [
            'nom' => 'Contrôle et Validation des Écritures', 
            'contenu' => '<?php
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
            \'conforme\' => abs($resultat[\'ecart\']) < 0.01,
            \'total_debit\' => $resultat[\'total_debit\'],
            \'total_credit\' => $resultat[\'total_credit\'],
            \'ecart\' => $resultat[\'ecart\']
        ];
    }
}
?>'
        ],
        
        'etats_financiers_complets.php' => [
            'nom' => 'États Financiers Complets',
            'contenu' => '<?php
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
?>'
        ]
    ];
    
    public function deployerTousModules() {
        echo "🚀 DÉPLOIEMENT DES MODULES SYSCOHADA\n";
        echo "====================================\n";
        
        $resultats = [];
        foreach ($this->modules as $fichier => $module) {
            echo "📦 Déploiement: {$module['nom']}... ";
            
            if (file_put_contents($fichier, $module['contenu'])) {
                echo "✅\n";
                $resultats[$fichier] = "DÉPLOYÉ";
            } else {
                echo "❌\n";
                $resultats[$fichier] = "ERREUR";
            }
        }
        
        return $resultats;
    }
}

// EXÉCUTION
echo "🎯 DÉPLOIEMENT FINAL DES MODULES\n";
echo "================================\n";

$deployment = new DeploymentFinalModules();
$resultat = $deployment->deployerTousModules();

echo "\n📊 RAPPORT DE DÉPLOIEMENT:\n";
foreach ($resultat as $fichier => $statut) {
    echo "  $fichier: $statut\n";
}

echo "\n🎉 DÉPLOIEMENT TERMINÉ!\n";
?>
