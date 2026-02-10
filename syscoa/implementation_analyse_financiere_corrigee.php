<?php
/**
 * IMPLÉMENTATION CORRIGÉE - Utilise les écritures existantes
 * Système SYSCOHADA
 */

class ImplementationAnalyseFinanciereCorrigee {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function executerAnalyseComplete() {
        echo "🎯 ANALYSE FINANCIÈRE AVEC DONNÉES EXISTANTES\n";
        echo "============================================\n";
        
        $this->verifierStructures();
        
        // Récupérer le dernier exercice
        $exercice_id = $this->getDernierExercice();
        if (!$exercice_id) {
            echo "❌ Aucun exercice comptable trouvé\n";
            return false;
        }
        
        echo "📊 Exercice analysé: ID $exercice_id\n";
        
        // Calculer les ratios
        $resultats = [];
        
        $resultats['liquidite'] = $this->calculerRatiosLiquidite($exercice_id);
        $resultats['solvabilite'] = $this->calculerRatiosSolvabilite($exercice_id);
        $resultats['rentabilite'] = $this->calculerRatiosRentabilite($exercice_id);
        
        $this->genererRapport($exercice_id, $resultats);
        
        return $resultats;
    }
    
    private function getDernierExercice() {
        $sql = "SELECT id_exercice FROM exercices_comptables ORDER BY date_debut DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id_exercice'] : null;
    }
    
    private function calculerRatiosLiquidite($exercice_id) {
        echo "\n💧 CALCUL DES RATIOS DE LIQUIDITÉ\n";
        
        $ratios = [];
        
        // Récupérer les soldes depuis les écritures
        $soldes = $this->calculerSoldesParClasse($exercice_id);
        
        // Liquidité Générale = Actif Circulant / Passif Circulant
        $actif_circulant = ($soldes['3'] ?? 0) + ($soldes['41'] ?? 0) + ($soldes['42'] ?? 0) + ($soldes['43'] ?? 0) + ($soldes['44'] ?? 0) + ($soldes['45'] ?? 0);
        $passif_circulant = ($soldes['40'] ?? 0) + ($soldes['42'] ?? 0) + ($soldes['43'] ?? 0) + ($soldes['44'] ?? 0) + ($soldes['45'] ?? 0) + ($soldes['46'] ?? 0) + ($soldes['47'] ?? 0);
        
        $liquidite_generale = $passif_circulant != 0 ? $actif_circulant / $passif_circulant : 0;
        
        $ratios['liquidite_generale'] = [
            'valeur' => round($liquidite_generale, 4),
            'interpretation' => $this->interpreterLiquiditeGenerale($liquidite_generale),
            'formule' => 'Actif Circulant / Passif Circulant'
        ];
        
        echo "  • Liquidité Générale: {$ratios['liquidite_generale']['valeur']} ({$ratios['liquidite_generale']['interpretation']})\n";
        
        // Liquidité Réduite = (Actif Circulant - Stocks) / Passif Circulant
        $stocks = $soldes['3'] ?? 0;
        $liquidite_reduite = $passif_circulant != 0 ? ($actif_circulant - $stocks) / $passif_circulant : 0;
        
        $ratios['liquidite_reduite'] = [
            'valeur' => round($liquidite_reduite, 4),
            'interpretation' => $this->interpreterLiquiditeReduite($liquidite_reduite),
            'formule' => '(Actif Circulant - Stocks) / Passif Circulant'
        ];
        
        echo "  • Liquidité Réduite: {$ratios['liquidite_reduite']['valeur']} ({$ratios['liquidite_reduite']['interpretation']})\n";
        
        $this->sauvegarderRatios($exercice_id, 'liquidite', $ratios);
        
        return $ratios;
    }
    
    private function calculerRatiosSolvabilite($exercice_id) {
        echo "\n🏦 CALCUL DES RATIOS DE SOLVABILITÉ\n";
        
        $ratios = [];
        $soldes = $this->calculerSoldesParClasse($exercice_id);
        
        // Autonomie Financière = Capitaux Propres / Total du Bilan
        $capitaux_propres = $soldes['1'] ?? 0;
        $total_actif = array_sum(array_map('abs', $soldes));
        
        $autonomie_financiere = $total_actif != 0 ? $capitaux_propres / $total_actif : 0;
        
        $ratios['autonomie_financiere'] = [
            'valeur' => round($autonomie_financiere, 4),
            'pourcentage' => round($autonomie_financiere * 100, 2) . '%',
            'interpretation' => $this->interpreterAutonomieFinanciere($autonomie_financiere),
            'formule' => 'Capitaux Propres / Total du Bilan'
        ];
        
        echo "  • Autonomie Financière: {$ratios['autonomie_financiere']['pourcentage']} ({$ratios['autonomie_financiere']['interpretation']})\n";
        
        // Endettement Global = Dettes Totales / Capitaux Propres
        $dettes_court_terme = ($soldes['40'] ?? 0) + ($soldes['42'] ?? 0) + ($soldes['43'] ?? 0) + ($soldes['44'] ?? 0) + ($soldes['45'] ?? 0);
        $dettes_long_terme = $soldes['16'] ?? 0;
        $dettes_totales = $dettes_court_terme + $dettes_long_terme;
        
        $endettement_global = $capitaux_propres != 0 ? $dettes_totales / $capitaux_propres : 0;
        
        $ratios['endettement_global'] = [
            'valeur' => round($endettement_global, 4),
            'interpretation' => $this->interpreterEndettementGlobal($endettement_global),
            'formule' => 'Dettes Totales / Capitaux Propres'
        ];
        
        echo "  • Endettement Global: {$ratios['endettement_global']['valeur']} ({$ratios['endettement_global']['interpretation']})\n";
        
        $this->sauvegarderRatios($exercice_id, 'solvabilite', $ratios);
        
        return $ratios;
    }
    
    private function calculerRatiosRentabilite($exercice_id) {
        echo "\n💰 CALCUL DES RATIOS DE RENTABILITÉ\n";
        
        $ratios = [];
        
        // Rentabilité des Capitaux Propres = Résultat Net / Capitaux Propres
        $sql_resultat = "SELECT SUM(debit - credit) as resultat 
                        FROM ecritures 
                        WHERE id_exercice = ? 
                        AND compte_num LIKE '12%'";
        
        $stmt = $this->db->prepare($sql_resultat);
        $stmt->execute([$exercice_id]);
        $resultat_net = $stmt->fetch(PDO::FETCH_ASSOC)['resultat'] ?? 0;
        
        $soldes = $this->calculerSoldesParClasse($exercice_id);
        $capitaux_propres = $soldes['1'] ?? 1;
        
        $roe = $capitaux_propres != 0 ? $resultat_net / $capitaux_propres : 0;
        
        $ratios['rentabilite_capitaux_propres'] = [
            'valeur' => round($roe, 4),
            'pourcentage' => round($roe * 100, 2) . '%',
            'interpretation' => $this->interpreterROE($roe),
            'formule' => 'Résultat Net / Capitaux Propres'
        ];
        
        echo "  • ROE (Return on Equity): {$ratios['rentabilite_capitaux_propres']['pourcentage']} ({$ratios['rentabilite_capitaux_propres']['interpretation']})\n";
        
        $this->sauvegarderRatios($exercice_id, 'rentabilite', $ratios);
        
        return $ratios;
    }
    
    private function calculerSoldesParClasse($exercice_id) {
        $sql = "
            SELECT 
                LEFT(c.numero_compte, 2) as classe_compte,
                SUM(e.debit - e.credit) as solde
            FROM ecritures e
            JOIN comptes_ohada c ON e.compte_num = c.numero_compte
            WHERE e.id_exercice = ?
            GROUP BY LEFT(c.numero_compte, 2)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id]);
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $soldes = [];
        foreach ($resultats as $row) {
            $soldes[$row['classe_compte']] = (float)$row['solde'];
        }
        
        return $soldes;
    }
    
    private function interpreterLiquiditeGenerale($ratio) {
        if ($ratio >= 2.0) return 'excellent';
        if ($ratio >= 1.5) return 'bon';
        if ($ratio >= 1.0) return 'moyen';
        if ($ratio >= 0.8) return 'faible';
        return 'critique';
    }
    
    private function interpreterLiquiditeReduite($ratio) {
        if ($ratio >= 1.0) return 'excellent';
        if ($ratio >= 0.8) return 'bon';
        if ($ratio >= 0.6) return 'moyen';
        if ($ratio >= 0.4) return 'faible';
        return 'critique';
    }
    
    private function interpreterAutonomieFinanciere($ratio) {
        if ($ratio >= 0.5) return 'excellent';
        if ($ratio >= 0.3) return 'bon';
        if ($ratio >= 0.2) return 'moyen';
        if ($ratio >= 0.1) return 'faible';
        return 'critique';
    }
    
    private function interpreterEndettementGlobal($ratio) {
        if ($ratio <= 0.5) return 'excellent';
        if ($ratio <= 1.0) return 'bon';
        if ($ratio <= 1.5) return 'moyen';
        if ($ratio <= 2.0) return 'faible';
        return 'critique';
    }
    
    private function interpreterROE($ratio) {
        if ($ratio >= 0.15) return 'excellent';
        if ($ratio >= 0.10) return 'bon';
        if ($ratio >= 0.05) return 'moyen';
        if ($ratio >= 0.02) return 'faible';
        return 'critique';
    }
    
    private function sauvegarderRatios($exercice_id, $categorie, $ratios) {
        foreach ($ratios as $nom_ratio => $details) {
            $sql = "INSERT INTO ratios_financiers 
                    (exercice_id, categorie, nom_ratio, formule_calcul, valeur_calculee, 
                     interpretation, date_calcul)
                    VALUES (?, ?, ?, ?, ?, ?, CURDATE())
                    ON DUPLICATE KEY UPDATE 
                    valeur_calculee = ?, interpretation = ?, date_calcul = CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $exercice_id, $categorie, $nom_ratio, $details['formule'], $details['valeur'], $details['interpretation'],
                $details['valeur'], $details['interpretation']
            ]);
        }
    }
    
    private function verifierStructures() {
        $tables_necessaires = ['ratios_financiers', 'analyses_sectorielles', 'historique_ratios'];
        
        foreach ($tables_necessaires as $table) {
            $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
            if (!$stmt->fetch()) {
                echo "⚠️  Table $table manquante - création... ";
                $this->creerTableRatios();
                echo "✅\n";
                break;
            }
        }
    }
    
    private function creerTableRatios() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS ratios_financiers (
                id_ratio INT PRIMARY KEY AUTO_INCREMENT,
                exercice_id INT NOT NULL,
                categorie ENUM('liquidite', 'solvabilite', 'rentabilite', 'rotation', 'endettement', 'equilibre') NOT NULL,
                nom_ratio VARCHAR(100) NOT NULL,
                formule_calcul TEXT NOT NULL,
                valeur_calculee DECIMAL(10,4) NOT NULL,
                valeur_reference DECIMAL(10,4),
                interpretation ENUM('excellent', 'bon', 'moyen', 'faible', 'critique') DEFAULT 'moyen',
                seuil_alerte_min DECIMAL(10,4),
                seuil_alerte_max DECIMAL(10,4),
                date_calcul DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
    
    private function genererRapport($exercice_id, $resultats) {
        echo "\n📊 RAPPORT D'ANALYSE FINANCIÈRE\n";
        echo "==============================\n";
        
        foreach ($resultats as $categorie => $ratios) {
            echo "\n" . strtoupper($categorie) . ":\n";
            foreach ($ratios as $nom => $details) {
                $valeur = $details['valeur'];
                $interpretation = $details['interpretation'];
                echo "  • $nom: $valeur ($interpretation)\n";
            }
        }
        
        // Sauvegarde dans un fichier
        $rapport = "RAPPORT D'ANALYSE FINANCIÈRE - " . date('d/m/Y H:i:s') . "\n\n";
        foreach ($resultats as $categorie => $ratios) {
            $rapport .= strtoupper($categorie) . ":\n";
            foreach ($ratios as $nom => $details) {
                $valeur = $details['valeur'];
                $interpretation = $details['interpretation'];
                $rapport .= "  - $nom: $valeur ($interpretation)\n";
            }
            $rapport .= "\n";
        }
        
        file_put_contents("rapport_analyse_" . date('Y-m-d') . ".txt", $rapport);
        echo "\n📄 Rapport sauvegardé: rapport_analyse_" . date('Y-m-d') . ".txt\n";
    }
}

// EXÉCUTION PRINCIPALE
try {
    $host = 'localhost';
    $dbname = 'sysco_ohada';
    $username = 'root';
    $password = '123';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 LANCEMENT DE L'ANALYSE FINANCIÈRE\n";
    
    $analyse = new ImplementationAnalyseFinanciereCorrigee($db);
    $resultats = $analyse->executerAnalyseComplete();
    
    echo "\n✅ ANALYSE TERMINÉE AVEC SUCCÈS!\n";
    
} catch (PDOException $e) {
    die("❌ Erreur: " . $e->getMessage());
}
