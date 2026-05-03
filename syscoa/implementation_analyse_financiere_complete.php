<?php
/**
 * IMPLÉMENTATION COMPLÈTE DES MODULES D'ANALYSE FINANCIÈRE
 * Système SYSCOHADA - Tous les ratios financiers
 */

class ImplementationAnalyseFinanciere {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * DÉPLOIEMENT COMPLET DE TOUS LES MODULES D'ANALYSE
     */
    public function deployerModulesComplets() {
        echo "🚀 DÉPLOIEMENT DES MODULES D'ANALYSE FINANCIÈRE COMPLÈTE\n";
        echo "========================================================\n";
        
        $this->creerStructuresRatios();
        $this->initialiserDonneesReference();
        
        return $this->executerCalculsPilotes();
    }
    
    /**
     * CRÉATION DES STRUCTURES POUR TOUS LES RATIOS
     */
    private function creerStructuresRatios() {
        echo "\n🏗️  CRÉATION DES STRUCTURES POUR LES RATIOS FINANCIERS\n";
        
        // Table principale des ratios
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
                FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice),
                INDEX idx_categorie (categorie),
                INDEX idx_exercice (exercice_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Table des analyses sectorielles
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS analyses_sectorielles (
                id_analyse INT PRIMARY KEY AUTO_INCREMENT,
                exercice_id INT NOT NULL,
                secteur_activite VARCHAR(100) NOT NULL,
                ratio_nom VARCHAR(100) NOT NULL,
                moyenne_sectorielle DECIMAL(10,4) NOT NULL,
                valeur_entreprise DECIMAL(10,4) NOT NULL,
                ecart_sectoriel DECIMAL(10,4) NOT NULL,
                positionnement ENUM('leader', 'bon', 'moyen', 'faible') DEFAULT 'moyen',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Table de suivi historique des ratios
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS historique_ratios (
                id_historique INT PRIMARY KEY AUTO_INCREMENT,
                exercice_id INT NOT NULL,
                ratio_nom VARCHAR(100) NOT NULL,
                valeur_n DECIMAL(10,4),
                valeur_n1 DECIMAL(10,4),
                valeur_n2 DECIMAL(10,4),
                tendance ENUM('amelioration', 'stabilite', 'deterioration') DEFAULT 'stabilite',
                amplitude_variation DECIMAL(10,4),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (exercice_id) REFERENCES exercices_comptables(id_exercice)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        echo "✅ Structures des ratios créées avec succès\n";
    }
    
    private function initialiserDonneesReference() {
        echo "\n📋 INITIALISATION DES DONNÉES DE RÉFÉRENCE\n";
        
        // Insérer les références des ratios
        $ratios_reference = [
            ['liquidite_generale', 'Actif Circulant / Passif Circulant', 1.5, 2.0],
            ['liquidite_reduite', '(Actif Circulant - Stocks) / Passif Circulant', 0.8, 1.0],
            ['liquidite_immediate', '(Disponibilités + VMP) / Passif Circulant', 0.2, 0.5],
            ['autonomie_financiere', 'Capitaux Propres / Total du Bilan', 0.3, 0.7],
            ['endettement_global', 'Dettes Totales / Capitaux Propres', 0.5, 1.5],
            ['rentabilite_capitaux_propres', 'Résultat Net / Capitaux Propres', 0.10, 0.20],
            ['rentabilite_actif', 'Résultat Net / Actif Total', 0.05, 0.15]
        ];
        
        foreach ($ratios_reference as $ratio) {
            $sql = "INSERT IGNORE INTO ratios_financiers 
                    (nom_ratio, formule_calcul, seuil_alerte_min, seuil_alerte_max)
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($ratio);
        }
        
        echo "✅ Données de référence initialisées\n";
    }
    
    private function executerCalculsPilotes() {
        echo "\n🧮 EXÉCUTION DES CALCULS PILOTES\n";
        
        // Récupérer le dernier exercice
        $sql = "SELECT id_exercice FROM exercices_comptables ORDER BY date_debut DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $exercice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exercice) {
            echo "❌ Aucun exercice comptable trouvé\n";
            return false;
        }
        
        $exercice_id = $exercice['id_exercice'];
        
        // Calculer tous les ratios
        $ratios_liquidite = new RatiosLiquidite($this->db);
        $resultats_liquidite = $ratios_liquidite->calculerTousRatiosLiquidite($exercice_id);
        
        $ratios_solvabilite = new RatiosSolvabilite($this->db);
        $resultats_solvabilite = $ratios_solvabilite->calculerTousRatiosSolvabilite($exercice_id);
        
        echo "✅ Calculs pilotes exécutés avec succès\n";
        
        return [
            'liquidite' => $resultats_liquidite,
            'solvabilite' => $resultats_solvabilite
        ];
    }
}

/**
 * 🎯 MODULE COMPLET DES RATIOS DE LIQUIDITÉ
 */
class RatiosLiquidite {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * CALCUL DE TOUS LES RATIOS DE LIQUIDITÉ
     */
    public function calculerTousRatiosLiquidite($exercice_id) {
        $ratios = [];
        
        // 1. RATIO DE LIQUIDITÉ GÉNÉRALE
        $ratios['liquidite_generale'] = $this->calculerLiquiditeGenerale($exercice_id);
        
        // 2. RATIO DE LIQUIDITÉ RÉDUITE
        $ratios['liquidite_reduite'] = $this->calculerLiquiditeReduite($exercice_id);
        
        // 3. RATIO DE LIQUIDITÉ IMMÉDIATE
        $ratios['liquidite_immediate'] = $this->calculerLiquiditeImmediate($exercice_id);
        
        // 4. RATIO DE TRÉSORERIE
        $ratios['ratio_tresorerie'] = $this->calculerRatioTresorerie($exercice_id);
        
        // 5. FONDS DE ROULEMENT NET GLOBAL / PASSIF CIRCULANT
        $ratios['fonds_roulement_passif_circulant'] = $this->calculerFRNGPassifCirculant($exercice_id);
        
        $this->sauvegarderRatios($exercice_id, 'liquidite', $ratios);
        
        return $ratios;
    }
    
    /**
     * RATIO DE LIQUIDITÉ GÉNÉRALE (Current Ratio)
     * Formule: Actif Circulant / Passif Circulant
     */
    private function calculerLiquiditeGenerale($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '3%' 
                 AND exercice_id = ?) as actif_circulant,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '4%' 
                 AND exercice_id = ?) as passif_circulant
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $actif_circulant = $data['actif_circulant'] ?? 1;
        $passif_circulant = $data['passif_circulant'] ?? 1;
        
        $ratio = $passif_circulant != 0 ? $actif_circulant / $passif_circulant : 0;
        
        $interpretation = $this->interpreterLiquiditeGenerale($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'liquidite_generale', $ratio, 
            'Actif Circulant / Passif Circulant', $interpretation, 1.5, 2.0);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '1.5 - 2.0'
        ];
    }
    
    /**
     * RATIO DE LIQUIDITÉ RÉDUITE (Quick Ratio / Acid Test)
     * Formule: (Actif Circulant - Stocks) / Passif Circulant
     */
    private function calculerLiquiditeReduite($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '3%' 
                 AND exercice_id = ?) as actif_circulant,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '3%' 
                 AND numero_compte NOT LIKE '39%' 
                 AND exercice_id = ?) as stocks,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '4%' 
                 AND exercice_id = ?) as passif_circulant
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $actif_circulant = $data['actif_circulant'] ?? 0;
        $stocks = $data['stocks'] ?? 0;
        $passif_circulant = $data['passif_circulant'] ?? 1;
        
        $actif_liquide = $actif_circulant - $stocks;
        $ratio = $passif_circulant != 0 ? $actif_liquide / $passif_circulant : 0;
        
        $interpretation = $this->interpreterLiquiditeReduite($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'liquidite_reduite', $ratio,
            '(Actif Circulant - Stocks) / Passif Circulant', $interpretation, 0.8, 1.0);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '0.8 - 1.0'
        ];
    }
    
    /**
     * RATIO DE LIQUIDITÉ IMMÉDIATE (Cash Ratio)
     * Formule: (Disponibilités + VMP) / Passif Circulant
     */
    private function calculerLiquiditeImmediate($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '5%' 
                 AND exercice_id = ?) as disponibilites,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '26%' 
                 AND exercice_id = ?) as vmp,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '4%' 
                 AND exercice_id = ?) as passif_circulant
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $disponibilites = $data['disponibilites'] ?? 0;
        $vmp = $data['vmp'] ?? 0;
        $passif_circulant = $data['passif_circulant'] ?? 1;
        
        $actif_immediat = $disponibilites + $vmp;
        $ratio = $passif_circulant != 0 ? $actif_immediat / $passif_circulant : 0;
        
        $interpretation = $this->interpreterLiquiditeImmediate($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'liquidite_immediate', $ratio,
            '(Disponibilités + VMP) / Passif Circulant', $interpretation, 0.2, 0.5);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '0.2 - 0.5'
        ];
    }
    
    /**
     * RATIO DE TRÉSORERIE
     */
    private function calculerRatioTresorerie($exercice_id) {
        // Pour simplifier, on utilise une formule basique
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '5%' 
                 AND exercice_id = ?) as tresorerie,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '4%' 
                 AND exercice_id = ?) as passif_circulant
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $tresorerie = $data['tresorerie'] ?? 0;
        $passif_circulant = $data['passif_circulant'] ?? 1;
        
        $ratio = $passif_circulant != 0 ? $tresorerie / $passif_circulant : 0;
        
        $interpretation = $this->interpreterRatioTresorerie($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'ratio_tresorerie', $ratio,
            'Trésorerie / Passif Circulant', $interpretation, 0.1, 0.3);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '0.1 - 0.3'
        ];
    }
    
    /**
     * FRNG / PASSIF CIRCULANT
     */
    private function calculerFRNGPassifCirculant($exercice_id) {
        // Calcul simplifié du FRNG
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '1%' 
                 AND exercice_id = ?) as capitaux_permanents,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '2%' 
                 AND exercice_id = ?) as actif_immobilise,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '4%' 
                 AND exercice_id = ?) as passif_circulant
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $capitaux_permanents = $data['capitaux_permanents'] ?? 0;
        $actif_immobilise = $data['actif_immobilise'] ?? 0;
        $passif_circulant = $data['passif_circulant'] ?? 1;
        
        $frng = $capitaux_permanents - $actif_immobilise;
        $ratio = $passif_circulant != 0 ? $frng / $passif_circulant : 0;
        
        $interpretation = $this->interpreterFRNGPassifCirculant($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'fonds_roulement_passif_circulant', $ratio,
            'FRNG / Passif Circulant', $interpretation, 0.5, 1.0);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '0.5 - 1.0'
        ];
    }
    
    /**
     * INTERPRÉTATION DES RATIOS DE LIQUIDITÉ
     */
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
    
    private function interpreterLiquiditeImmediate($ratio) {
        if ($ratio >= 0.5) return 'excellent';
        if ($ratio >= 0.3) return 'bon';
        if ($ratio >= 0.2) return 'moyen';
        if ($ratio >= 0.1) return 'faible';
        return 'critique';
    }
    
    private function interpreterRatioTresorerie($ratio) {
        if ($ratio >= 0.3) return 'excellent';
        if ($ratio >= 0.2) return 'bon';
        if ($ratio >= 0.1) return 'moyen';
        if ($ratio >= 0.05) return 'faible';
        return 'critique';
    }
    
    private function interpreterFRNGPassifCirculant($ratio) {
        if ($ratio >= 1.0) return 'excellent';
        if ($ratio >= 0.7) return 'bon';
        if ($ratio >= 0.5) return 'moyen';
        if ($ratio >= 0.3) return 'faible';
        return 'critique';
    }
    
    private function sauvegarderRatio($exercice_id, $nom_ratio, $valeur, $formule, $interpretation, $seuil_min, $seuil_max) {
        $sql = "INSERT INTO ratios_financiers 
                (exercice_id, categorie, nom_ratio, formule_calcul, valeur_calculee, 
                 interpretation, seuil_alerte_min, seuil_alerte_max, date_calcul)
                VALUES (?, 'liquidite', ?, ?, ?, ?, ?, ?, CURDATE())
                ON DUPLICATE KEY UPDATE 
                valeur_calculee = ?, interpretation = ?, date_calcul = CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $exercice_id, $nom_ratio, $formule, $valeur, $interpretation, $seuil_min, $seuil_max,
            $valeur, $interpretation
        ]);
    }
    
    private function sauvegarderRatios($exercice_id, $categorie, $ratios) {
        foreach ($ratios as $nom => $data) {
            if (isset($data['valeur'])) {
                $this->sauvegarderRatio($exercice_id, $nom, $data['valeur'], '', $data['interpretation'], 0, 0);
            }
        }
    }
}

/**
 * 📊 MODULE COMPLET DES RATIOS DE SOLVABILITÉ
 */
class RatiosSolvabilite {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function calculerTousRatiosSolvabilite($exercice_id) {
        $ratios = [];
        
        // 1. RATIO D'AUTONOMIE FINANCIÈRE
        $ratios['autonomie_financiere'] = $this->calculerAutonomieFinanciere($exercice_id);
        
        // 2. RATIO D'ENDETTEMENT GLOBAL
        $ratios['endettement_global'] = $this->calculerEndettementGlobal($exercice_id);
        
        // 3. RATIO DE COUVERTURE DES DETTES
        $ratios['couverture_dettes'] = $this->calculerCouvertureDettes($exercice_id);
        
        // 4. RATIO DE LEVIER FINANCIER
        $ratios['levier_financier'] = $this->calculerLevierFinancier($exercice_id);
        
        // 5. CAPACITÉ DE REMBOURSEMENT
        $ratios['capacite_remboursement'] = $this->calculerCapaciteRemboursement($exercice_id);
        
        $this->sauvegarderRatios($exercice_id, 'solvabilite', $ratios);
        
        return $ratios;
    }
    
    /**
     * RATIO D'AUTONOMIE FINANCIÈRE
     * Formule: Capitaux Propres / Total du Bilan
     */
    private function calculerAutonomieFinanciere($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '1%' 
                 AND exercice_id = ?) as capitaux_propres,
                
                (SELECT COALESCE(SUM(ABS(solde)), 0) 
                 FROM soldes_comptes 
                 WHERE exercice_id = ?) as total_bilan
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $capitaux_propres = $data['capitaux_propres'] ?? 0;
        $total_bilan = $data['total_bilan'] ?? 1;
        
        $ratio = $total_bilan != 0 ? $capitaux_propres / $total_bilan : 0;
        
        $interpretation = $this->interpreterAutonomieFinanciere($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'autonomie_financiere', $ratio,
            'Capitaux Propres / Total du Bilan', $interpretation, 0.3, 0.7);
        
        return [
            'valeur' => round($ratio, 4),
            'pourcentage' => round($ratio * 100, 2) . '%',
            'interpretation' => $interpretation
        ];
    }
    
    /**
     * RATIO D'ENDETTEMENT GLOBAL
     * Formule: Dettes Totales / Capitaux Propres
     */
    private function calculerEndettementGlobal($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '1%' 
                 AND exercice_id = ?) as capitaux_propres,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '16%' 
                 AND exercice_id = ?) as dettes_long_terme,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '4%' 
                 AND exercice_id = ?) as dettes_court_terme
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $capitaux_propres = $data['capitaux_propres'] ?? 1;
        $dettes_totales = ($data['dettes_long_terme'] ?? 0) + ($data['dettes_court_terme'] ?? 0);
        
        $ratio = $capitaux_propres != 0 ? $dettes_totales / $capitaux_propres : 0;
        
        $interpretation = $this->interpreterEndettementGlobal($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'endettement_global', $ratio,
            'Dettes Totales / Capitaux Propres', $interpretation, 0.5, 1.5);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '0.5 - 1.5'
        ];
    }
    
    /**
     * RATIO DE COUVERTURE DES DETTES
     * Formule: CAF / Dettes Financières
     */
    private function calculerCouvertureDettes($exercice_id) {
        // Calcul simplifié de la CAF (Capacité d'Autofinancement)
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '12%' 
                 AND exercice_id = ?) as resultat_net,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '28%' 
                 AND exercice_id = ?) as dotations_amortissement,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '16%' 
                 AND exercice_id = ?) as dettes_financieres
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $resultat_net = $data['resultat_net'] ?? 0;
        $dotations = $data['dotations_amortissement'] ?? 0;
        $dettes_financieres = $data['dettes_financieres'] ?? 1;
        
        $caf = $resultat_net + $dotations;
        $ratio = $dettes_financieres != 0 ? $caf / $dettes_financieres : 0;
        
        $interpretation = $this->interpreterCouvertureDettes($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'couverture_dettes', $ratio,
            'CAF / Dettes Financières', $interpretation, 0.3, 0.5);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '0.3 - 0.5'
        ];
    }
    
    /**
     * RATIO DE LEVIER FINANCIER
     * Formule: Actif Total / Capitaux Propres
     */
    private function calculerLevierFinancier($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(ABS(solde)), 0) 
                 FROM soldes_comptes 
                 WHERE exercice_id = ?) as actif_total,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '1%' 
                 AND exercice_id = ?) as capitaux_propres
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $actif_total = $data['actif_total'] ?? 1;
        $capitaux_propres = $data['capitaux_propres'] ?? 1;
        
        $ratio = $capitaux_propres != 0 ? $actif_total / $capitaux_propres : 0;
        
        $interpretation = $this->interpreterLevierFinancier($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'levier_financier', $ratio,
            'Actif Total / Capitaux Propres', $interpretation, 1.5, 3.0);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '1.5 - 3.0'
        ];
    }
    
    /**
     * CAPACITÉ DE REMBOURSEMENT
     * Formule: Dettes Financières / CAF
     */
    private function calculerCapaciteRemboursement($exercice_id) {
        $sql = "
            SELECT 
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '12%' 
                 AND exercice_id = ?) as resultat_net,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '28%' 
                 AND exercice_id = ?) as dotations_amortissement,
                
                (SELECT COALESCE(SUM(solde), 0) 
                 FROM soldes_comptes 
                 WHERE numero_compte LIKE '16%' 
                 AND exercice_id = ?) as dettes_financieres
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exercice_id, $exercice_id, $exercice_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $resultat_net = $data['resultat_net'] ?? 0;
        $dotations = $data['dotations_amortissement'] ?? 0;
        $dettes_financieres = $data['dettes_financieres'] ?? 1;
        
        $caf = $resultat_net + $dotations;
        $ratio = $caf != 0 ? $dettes_financieres / $caf : 0;
        
        $interpretation = $this->interpreterCapaciteRemboursement($ratio);
        
        $this->sauvegarderRatio($exercice_id, 'capacite_remboursement', $ratio,
            'Dettes Financières / CAF', $interpretation, 2.0, 4.0);
        
        return [
            'valeur' => round($ratio, 4),
            'interpretation' => $interpretation,
            'seuil_optimal' => '2.0 - 4.0'
        ];
    }
    
    /**
     * INTERPRÉTATION DES RATIOS DE SOLVABILITÉ
     */
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
    
    private function interpreterCouvertureDettes($ratio) {
        if ($ratio >= 0.5) return 'excellent';
        if ($ratio >= 0.3) return 'bon';
        if ($ratio >= 0.2) return 'moyen';
        if ($ratio >= 0.1) return 'faible';
        return 'critique';
    }
    
    private function interpreterLevierFinancier($ratio) {
        if ($ratio <= 2.0) return 'excellent';
        if ($ratio <= 3.0) return 'bon';
        if ($ratio <= 4.0) return 'moyen';
        if ($ratio <= 5.0) return 'faible';
        return 'critique';
    }
    
    private function interpreterCapaciteRemboursement($ratio) {
        if ($ratio <= 3.0) return 'excellent';
        if ($ratio <= 4.0) return 'bon';
        if ($ratio <= 5.0) return 'moyen';
        if ($ratio <= 6.0) return 'faible';
        return 'critique';
    }
    
    private function sauvegarderRatio($exercice_id, $nom_ratio, $valeur, $formule, $interpretation, $seuil_min, $seuil_max) {
        $sql = "INSERT INTO ratios_financiers 
                (exercice_id, categorie, nom_ratio, formule_calcul, valeur_calculee, 
                 interpretation, seuil_alerte_min, seuil_alerte_max, date_calcul)
                VALUES (?, 'solvabilite', ?, ?, ?, ?, ?, ?, CURDATE())
                ON DUPLICATE KEY UPDATE 
                valeur_calculee = ?, interpretation = ?, date_calcul = CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $exercice_id, $nom_ratio, $formule, $valeur, $interpretation, $seuil_min, $seuil_max,
            $valeur, $interpretation
        ]);
    }
    
    private function sauvegarderRatios($exercice_id, $categorie, $ratios) {
        foreach ($ratios as $nom => $data) {
            if (isset($data['valeur'])) {
                $this->sauvegarderRatio($exercice_id, $nom, $data['valeur'], '', $data['interpretation'], 0, 0);
            }
        }
    }
}

// EXÉCUTION PRINCIPALE
try {
    $host = '127.0.0.1';
    $dbname = 'sysco_ohada';
    $username = 'root';
    $password = '123';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 LANCEMENT DE L'IMPLÉMENTATION DES MODULES D'ANALYSE FINANCIÈRE\n";
    
    // Déploiement complet
    $implementation = new ImplementationAnalyseFinanciere($db);
    $resultat = $implementation->deployerModulesComplets();
    
    echo "\n✅ IMPLÉMENTATION TERMINÉE AVEC SUCCÈS!\n";
    
    // Afficher un résumé
    if ($resultat) {
        echo "\n📊 RÉSUMÉ DES CALCULS EFFECTUÉS:\n";
        echo "Ratios de Liquidité: " . count($resultat['liquidite']) . " calculés\n";
        echo "Ratios de Solvabilité: " . count($resultat['solvabilite']) . " calculés\n";
        
        // Vérification dans la base
        $sql = "SELECT categorie, COUNT(*) as nb_ratios 
                FROM ratios_financiers 
                WHERE exercice_id IS NOT NULL 
                GROUP BY categorie";
        $stmt = $db->query($sql);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n📈 STATISTIQUES DE LA BASE:\n";
        foreach ($stats as $stat) {
            echo "  - {$stat['categorie']}: {$stat['nb_ratios']} ratios\n";
        }
    }
    
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage());
}
