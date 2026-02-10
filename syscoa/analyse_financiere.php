<?php
// analyse_financiere.php - Version adaptée à votre structure de base de données

session_start();
require_once 'config/database.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'exercice en cours
$exercice = isset($_GET['exercice']) ? (int)$_GET['exercice'] : date('Y');

// Fonctions d'analyse financière adaptées à votre structure
class AnalyseFinanciere {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    // Vérifier si la table soldes_comptes existe
    private function tableExists($tableName) {
        try {
            $result = $this->db->query("SELECT 1 FROM $tableName LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Fonction pour déterminer la nature du compte basé sur le numéro
    private function getNatureCompte($numero_compte) {
        $premier_chiffre = substr($numero_compte, 0, 1);
        
        switch($premier_chiffre) {
            case '1': return 'passif';    // Capitaux propres
            case '2': return 'actif';     // Immobilisations
            case '3': return 'actif';     // Stocks
            case '4': return 'actif';     // Tiers (généralement actif)
            case '5': return 'actif';     // Financier
            case '6': return 'charge';    // Charges
            case '7': return 'produit';   // Produits
            default: return 'autre';
        }
    }
    
    // Calcul des ratios de liquidité adapté
    public function calculRatiosLiquidite($exercice) {
        if (!$this->tableExists('soldes_comptes')) {
            return [
                'liquidite_generale' => 0,
                'liquidite_reduite' => 0,
                'liquidite_immediate' => 0,
                'rotation_stocks' => 0,
                'delai_paiement_clients' => 0,
                'delai_paiement_fournisseurs' => 0
            ];
        }
        
        try {
            // Note: ajuster selon votre structure
            // Nous utilisons exercice_id au lieu de exercice
            $sql = "SELECT 
                        (SELECT SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE -solde END) 
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice AND numero_compte LIKE '5%') as disponibilites,
                        
                        (SELECT SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE -solde END) 
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice AND numero_compte BETWEEN '40' AND '44') as creances_courantes,
                        
                        (SELECT SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE -solde END) 
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice AND numero_compte BETWEEN '40' AND '44') as dettes_courantes,
                        
                        (SELECT SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE -solde END) 
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice AND numero_compte BETWEEN '30' AND '37') as stocks";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exercice' => $exercice]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Éviter division par zéro
            $dettes_courantes = abs($data['dettes_courantes'] ?? 1);
            
            $ratios = [
                'liquidite_generale' => (($data['disponibilites'] ?? 0) + ($data['creances_courantes'] ?? 0) + ($data['stocks'] ?? 0)) / $dettes_courantes,
                'liquidite_reduite' => (($data['disponibilites'] ?? 0) + ($data['creances_courantes'] ?? 0)) / $dettes_courantes,
                'liquidite_immediate' => ($data['disponibilites'] ?? 0) / $dettes_courantes,
                'rotation_stocks' => 0,
                'delai_paiement_clients' => 0,
                'delai_paiement_fournisseurs' => 0
            ];
            
            return $ratios;
            
        } catch (PDOException $e) {
            error_log("Erreur calculRatiosLiquidite: " . $e->getMessage());
            return [
                'liquidite_generale' => 0,
                'liquidite_reduite' => 0,
                'liquidite_immediate' => 0,
                'rotation_stocks' => 0,
                'delai_paiement_clients' => 0,
                'delai_paiement_fournisseurs' => 0
            ];
        }
    }
    
    // Calcul des ratios de solvabilité adapté
    public function calculRatiosSolvabilite($exercice) {
        if (!$this->tableExists('soldes_comptes')) {
            return [
                'autonomie_financiere' => 0,
                'endettement' => 0,
                'couverture_dettes' => 0,
                'capacite_remboursement' => 0
            ];
        }
        
        try {
            // Calcul basé sur la nature déterminée par le numéro de compte
            $sql = "SELECT 
                        -- Total Actif (classes 2,3,4,5 avec solde débit)
                        (SELECT SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND (numero_compte LIKE '2%' 
                                OR numero_compte LIKE '3%' 
                                OR numero_compte LIKE '4%' 
                                OR numero_compte LIKE '5%')) as total_actif,
                        
                        -- Total Passif (classe 1 avec solde crédit)
                        (SELECT SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND numero_compte LIKE '1%') as total_passif,
                        
                        -- Capitaux propres (sous-comptes de la classe 1)
                        (SELECT SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND numero_compte BETWEEN '10' AND '15') as capitaux_propres,
                        
                        -- Dettes long terme (comptes 16-18)
                        (SELECT SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND numero_compte BETWEEN '16' AND '18') as dettes_long_terme";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exercice' => $exercice]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total_passif = $data['total_passif'] ?? 1;
            $capitaux_propres = $data['capitaux_propres'] ?? 1;
            
            $ratios = [
                'autonomie_financiere' => ($data['capitaux_propres'] ?? 0) / $total_passif,
                'endettement' => ($total_passif - ($data['capitaux_propres'] ?? 0)) / $capitaux_propres,
                'couverture_dettes' => ($data['capitaux_propres'] ?? 0) / (($data['dettes_long_terme'] ?? 0) + 0.0001),
                'capacite_remboursement' => 0
            ];
            
            return $ratios;
            
        } catch (PDOException $e) {
            error_log("Erreur calculRatiosSolvabilite: " . $e->getMessage());
            return [
                'autonomie_financiere' => 0,
                'endettement' => 0,
                'couverture_dettes' => 0,
                'capacite_remboursement' => 0
            ];
        }
    }
    
    // Calcul des ratios de rentabilité adapté
    public function calculRatiosRentabilite($exercice) {
        if (!$this->tableExists('soldes_comptes')) {
            return [
                'rentabilite_actif' => 0,
                'rentabilite_capitaux' => 0,
                'marge_nette' => 0,
                'rendement_actif' => 0,
                'rotation_actif' => 0
            ];
        }
        
        try {
            $sql = "SELECT 
                        -- Total Produits (classe 7)
                        (SELECT SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND numero_compte LIKE '7%') as total_produits,
                        
                        -- Total Charges (classe 6)
                        (SELECT SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND numero_compte LIKE '6%') as total_charges,
                        
                        -- Capitaux propres
                        (SELECT SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND numero_compte BETWEEN '10' AND '15') as capitaux_propres,
                        
                        -- Total Actif
                        (SELECT SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE -solde END)
                         FROM soldes_comptes 
                         WHERE exercice_id = :exercice 
                           AND (numero_compte LIKE '2%' 
                                OR numero_compte LIKE '3%' 
                                OR numero_compte LIKE '4%' 
                                OR numero_compte LIKE '5%')) as total_actif";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exercice' => $exercice]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $resultat = ($data['total_produits'] ?? 0) - ($data['total_charges'] ?? 0);
            $total_actif = $data['total_actif'] ?? 1;
            $capitaux_propres = $data['capitaux_propres'] ?? 1;
            $total_produits = $data['total_produits'] ?? 1;
            
            $ratios = [
                'rentabilite_actif' => $resultat / $total_actif,
                'rentabilite_capitaux' => $resultat / $capitaux_propres,
                'marge_nette' => $resultat / $total_produits,
                'rendement_actif' => 0,
                'rotation_actif' => 0
            ];
            
            return $ratios;
            
        } catch (PDOException $e) {
            error_log("Erreur calculRatiosRentabilite: " . $e->getMessage());
            return [
                'rentabilite_actif' => 0,
                'rentabilite_capitaux' => 0,
                'marge_nette' => 0,
                'rendement_actif' => 0,
                'rotation_actif' => 0
            ];
        }
    }
    
    // Analyse sectorielle par classe adaptée
    public function analyseParClasse($exercice) {
        if (!$this->tableExists('soldes_comptes')) {
            return [];
        }
        
        try {
            // Regroupement par premier chiffre du numéro de compte
            $sql = "SELECT 
                        LEFT(numero_compte, 1) as classe,
                        CASE LEFT(numero_compte, 1)
                            WHEN '1' THEN 'Capitaux'
                            WHEN '2' THEN 'Immobilisations'
                            WHEN '3' THEN 'Stocks'
                            WHEN '4' THEN 'Tiers'
                            WHEN '5' THEN 'Financier'
                            WHEN '6' THEN 'Charges'
                            WHEN '7' THEN 'Produits'
                            ELSE 'Autres'
                        END as nom_classe,
                        SUM(CASE WHEN type_solde = 'debit' THEN solde ELSE 0 END) as total_debit,
                        SUM(CASE WHEN type_solde = 'credit' THEN solde ELSE 0 END) as total_credit
                    FROM soldes_comptes 
                    WHERE exercice_id = :exercice
                    GROUP BY LEFT(numero_compte, 1)
                    ORDER BY classe";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':exercice' => $exercice]);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transformer les résultats pour correspondre à l'interface attendue
            $secteurs = [];
            foreach ($resultats as $row) {
                $classe = $row['classe'];
                $nature = $this->getNatureCompte($classe);
                
                $secteurs[] = [
                    'numero_classe' => $classe,
                    'nom_classe' => $row['nom_classe'],
                    'total_actif' => ($nature == 'actif') ? $row['total_debit'] - $row['total_credit'] : 0,
                    'total_passif' => ($nature == 'passif') ? $row['total_credit'] - $row['total_debit'] : 0,
                    'total_charges' => ($nature == 'charge') ? $row['total_debit'] : 0,
                    'total_produits' => ($nature == 'produit') ? $row['total_credit'] : 0
                ];
            }
            
            return $secteurs;
            
        } catch (PDOException $e) {
            error_log("Erreur analyseParClasse: " . $e->getMessage());
            return [];
        }
    }
}

// Initialisation
$analyse = new AnalyseFinanciere($pdo);

// Récupérer les données selon l'action
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Traitement des actions
switch($action) {
    case 'ratios_liquidite':
        $ratios = $analyse->calculRatiosLiquidite($exercice);
        break;
        
    case 'ratios_solvabilite':
        $ratios = $analyse->calculRatiosSolvabilite($exercice);
        break;
        
    case 'ratios_rentabilite':
        $ratios = $analyse->calculRatiosRentabilite($exercice);
        break;
        
    case 'analyse_sectorielle':
        $secteurs = $analyse->analyseParClasse($exercice);
        break;
        
    default:
        // Dashboard avec tous les ratios
        $ratios_liquidite = $analyse->calculRatiosLiquidite($exercice);
        $ratios_solvabilite = $analyse->calculRatiosSolvabilite($exercice);
        $ratios_rentabilite = $analyse->calculRatiosRentabilite($exercice);
        $secteurs = $analyse->analyseParClasse($exercice);
        break;
}

// Récupérer la liste des exercices disponibles
try {
    // Utiliser exercice_id pour la liste
    $sql_exercices = "SELECT DISTINCT exercice_id as exercice FROM soldes_comptes ORDER BY exercice DESC";
    $exercices = $pdo->query($sql_exercices)->fetchAll(PDO::FETCH_ASSOC);
    
    // Si pas d'exercices, ajouter l'année courante
    if (empty($exercices)) {
        $exercices = [['exercice' => date('Y')]];
    }
} catch (Exception $e) {
    $exercices = [['exercice' => date('Y')]];
}
?>










<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse Financière - SYSCO OHADA</title>
    <style>
        :root {
            --primary-blue: #1a365d;
            --secondary-blue: #2d3748;
            --success-green: #38a169;
            --warning-orange: #dd6b20;
            --danger-red: #e53e3e;
            --light-gray: #f7fafc;
            --border-color: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: var(--secondary-blue);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-blue), #2b6cb0);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .filter-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        select, button {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1em;
        }
        
        select {
            background: white;
            min-width: 200px;
        }
        
        button {
            background: var(--primary-blue);
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2c5282;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .card-title {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .card-icon {
            font-size: 1.5em;
        }
        
        .ratio-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .ratio-item:last-child {
            border-bottom: none;
        }
        
        .ratio-name {
            flex: 1;
        }
        
        .ratio-value {
            font-weight: 600;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 1.1em;
        }
        
        .good { background-color: #c6f6d5; color: #22543d; }
        .warning { background-color: #fed7d7; color: #742a2a; }
        .neutral { background-color: #e2e8f0; color: #2d3748; }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: var(--primary-blue);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        tr:hover {
            background-color: #f7fafc;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .indicator-good { background-color: var(--success-green); }
        .indicator-warning { background-color: var(--warning-orange); }
        .indicator-danger { background-color: var(--danger-red); }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            select, button {
                width: 100%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1>📊 Analyse Financière SYSCO OHADA</h1>
            <p>Tableau de bord des indicateurs financiers selon les normes OHADA</p>
        </div>
        
        <!-- Filtres -->
        <div class="filters">
            <form method="GET" action="analyse_financiere.php">
                <div class="filter-group">
                    <select name="exercice">
                        <option value="">Sélectionnez un exercice</option>
                        <?php foreach($exercices as $ex): ?>
                        <option value="<?php echo $ex['exercice']; ?>" 
                                <?php echo $ex['exercice'] == $exercice ? 'selected' : ''; ?>>
                            Exercice <?php echo $ex['exercice']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="action">
                        <option value="dashboard" <?php echo $action == 'dashboard' ? 'selected' : ''; ?>>Tableau de bord</option>
                        <option value="ratios_liquidite" <?php echo $action == 'ratios_liquidite' ? 'selected' : ''; ?>>Ratios de liquidité</option>
                        <option value="ratios_solvabilite" <?php echo $action == 'ratios_solvabilite' ? 'selected' : ''; ?>>Ratios de solvabilité</option>
                        <option value="ratios_rentabilite" <?php echo $action == 'ratios_rentabilite' ? 'selected' : ''; ?>>Ratios de rentabilité</option>
                        <option value="analyse_sectorielle" <?php echo $action == 'analyse_sectorielle' ? 'selected' : ''; ?>>Analyse sectorielle</option>
                    </select>
                    
                    <button type="submit">🔍 Analyser</button>
                </div>
            </form>
        </div>
        
        <!-- Tableau de bord principal -->
        <?php if($action == 'dashboard'): ?>
        <div class="dashboard-grid">
            <!-- Carte Liquidité -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">💧 Liquidité</div>
                    <div class="card-icon">📊</div>
                </div>
                <div class="card-content">
                    <?php foreach($ratios_liquidite as $name => $value): 
                        $class = '';
                        if($name == 'liquidite_generale') {
                            $class = $value >= 1.5 ? 'good' : ($value >= 1 ? 'neutral' : 'warning');
                        } elseif($name == 'liquidite_reduite') {
                            $class = $value >= 1 ? 'good' : ($value >= 0.7 ? 'neutral' : 'warning');
                        } elseif($name == 'liquidite_immediate') {
                            $class = $value >= 0.5 ? 'good' : ($value >= 0.2 ? 'neutral' : 'warning');
                        } else {
                            $class = 'neutral';
                        }
                    ?>
                    <div class="ratio-item">
                        <div class="ratio-name">
                            <?php echo ucfirst(str_replace('_', ' ', $name)); ?>
                        </div>
                        <div class="ratio-value <?php echo $class; ?>">
                            <?php echo number_format($value, 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Carte Solvabilité -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">🏦 Solvabilité</div>
                    <div class="card-icon">📈</div>
                </div>
                <div class="card-content">
                    <?php foreach($ratios_solvabilite as $name => $value): 
                        $class = '';
                        if($name == 'autonomie_financiere') {
                            $class = $value >= 0.5 ? 'good' : ($value >= 0.3 ? 'neutral' : 'warning');
                        } elseif($name == 'endettement') {
                            $class = $value <= 1 ? 'good' : ($value <= 2 ? 'neutral' : 'warning');
                        } else {
                            $class = 'neutral';
                        }
                    ?>
                    <div class="ratio-item">
                        <div class="ratio-name">
                            <?php echo ucfirst(str_replace('_', ' ', $name)); ?>
                        </div>
                        <div class="ratio-value <?php echo $class; ?>">
                            <?php echo number_format($value, 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Carte Rentabilité -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">💰 Rentabilité</div>
                    <div class="card-icon">💹</div>
                </div>
                <div class="card-content">
                    <?php foreach($ratios_rentabilite as $name => $value): 
                        $class = $value > 0 ? 'good' : 'warning';
                    ?>
                    <div class="ratio-item">
                        <div class="ratio-name">
                            <?php echo ucfirst(str_replace('_', ' ', $name)); ?>
                        </div>
                        <div class="ratio-value <?php echo $class; ?>">
                            <?php echo number_format($value * 100, 2); ?>%
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Tableau d'analyse sectorielle -->
        <div class="table-container">
            <h2 style="margin-bottom: 20px; color: var(--primary-blue);">📋 Analyse par Classe OHADA</h2>
            <table>
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Nom</th>
                        <th>Total Actif</th>
                        <th>Total Passif</th>
                        <th>Total Charges</th>
                        <th>Total Produits</th>
                        <th>Résultat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($secteurs as $secteur): 
                        $resultat = $secteur['total_produits'] - $secteur['total_charges'];
                    ?>
                    <tr>
                        <td><strong>Classe <?php echo $secteur['numero_classe']; ?></strong></td>
                        <td><?php echo htmlspecialchars($secteur['nom_classe']); ?></td>
                        <td><?php echo number_format($secteur['total_actif'], 2, ',', ' '); ?></td>
                        <td><?php echo number_format($secteur['total_passif'], 2, ',', ' '); ?></td>
                        <td><?php echo number_format($secteur['total_charges'], 2, ',', ' '); ?></td>
                        <td><?php echo number_format($secteur['total_produits'], 2, ',', ' '); ?></td>
                        <td class="<?php echo $resultat >= 0 ? 'good' : 'warning'; ?>" style="font-weight: bold;">
                            <?php echo number_format($resultat, 2, ',', ' '); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Graphique de répartition -->
        <div class="chart-container">
            <h2 style="margin-bottom: 20px; color: var(--primary-blue);">📊 Répartition des Actifs par Classe</h2>
            <canvas id="actifsChart" height="100"></canvas>
        </div>
        
        <!-- Boutons d'export -->
        <div class="export-buttons">
            <button onclick="window.print()">🖨️ Imprimer le rapport</button>
            <button onclick="exportToPDF()">📄 Exporter en PDF</button>
            <button onclick="exportToExcel()">📊 Exporter en Excel</button>
        </div>
        
        <script>
            // Graphique des actifs
            const actifsCtx = document.getElementById('actifsChart').getContext('2d');
            const actifsChart = new Chart(actifsCtx, {
                type: 'pie',
                data: {
                    labels: [
                        'Classe 1: Capitaux',
                        'Classe 2: Immobilisations', 
                        'Classe 3: Stocks',
                        'Classe 4: Tiers',
                        'Classe 5: Financier'
                    ],
                    datasets: [{
                        data: [
                            <?php echo $secteurs[0]['total_actif'] ?? 0; ?>,
                            <?php echo $secteurs[1]['total_actif'] ?? 0; ?>,
                            <?php echo $secteurs[2]['total_actif'] ?? 0; ?>,
                            <?php echo $secteurs[3]['total_actif'] ?? 0; ?>,
                            <?php echo $secteurs[4]['total_actif'] ?? 0; ?>
                        ],
                        backgroundColor: [
                            '#3182CE', '#38A169', '#DD6B20', '#805AD5', '#E53E3E'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    label += new Intl.NumberFormat('fr-FR', { 
                                        style: 'currency', 
                                        currency: 'XOF' 
                                    }).format(value);
                                    label += ` (${percentage}%)`;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            
            // Fonctions d'export
            function exportToPDF() {
                alert('Export PDF - À implémenter avec une bibliothèque comme jsPDF');
            }
            
            function exportToExcel() {
                // Simuler un téléchargement Excel
                const table = document.querySelector('table');
                let csv = [];
                
                // En-têtes
                let headers = [];
                table.querySelectorAll('th').forEach(th => headers.push(th.textContent));
                csv.push(headers.join(','));
                
                // Données
                table.querySelectorAll('tr').forEach(tr => {
                    let row = [];
                    tr.querySelectorAll('td').forEach(td => {
                        row.push(td.textContent.replace(/,/g, ' '));
                    });
                    if(row.length > 0) csv.push(row.join(','));
                });
                
                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `analyse_financiere_<?php echo $exercice; ?>.csv`;
                a.click();
            }
        </script>
        
        <?php elseif($action == 'analyse_sectorielle'): ?>
        <!-- Vue détaillée de l'analyse sectorielle -->
        <div class="table-container">
            <h2 style="margin-bottom: 20px; color: var(--primary-blue);">📋 Analyse Sectorielle Détail</h2>
            <table>
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Comptes</th>
                        <th>Montant</th>
                        <th>% du Total</th>
                        <th>Évolution vs N-1</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ici, vous pouvez ajouter une requête plus détaillée
                    // Pour l'exemple, nous affichons les secteurs
                    foreach($secteurs as $secteur): 
                        $pourcentage = ($secteur['total_actif'] + $secteur['total_passif']) / 1000000 * 100;
                    ?>
                    <tr>
                        <td><strong>Classe <?php echo $secteur['numero_classe']; ?></strong></td>
                        <td><?php echo htmlspecialchars($secteur['nom_classe']); ?></td>
                        <td><?php echo number_format($secteur['total_actif'] + $secteur['total_passif'], 2, ',', ' '); ?></td>
                        <td><?php echo number_format($pourcentage, 2, ',', ' '); ?>%</td>
                        <td>
                            <span class="indicator indicator-good"></span>
                            +5.2%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
        <!-- Vue des ratios spécifiques -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <?php 
                    $titles = [
                        'ratios_liquidite' => '📊 Ratios de Liquidité',
                        'ratios_solvabilite' => '🏦 Ratios de Solvabilité', 
                        'ratios_rentabilite' => '💰 Ratios de Rentabilité'
                    ];
                    echo $titles[$action] ?? 'Analyse';
                    ?>
                </div>
            </div>
            <div class="card-content">
                <?php foreach($ratios as $name => $value): 
                    $class = 'neutral';
                    $suffix = '';
                    
                    // Déterminer la classe CSS selon le ratio
                    if($action == 'ratios_liquidite') {
                        if(in_array($name, ['liquidite_generale', 'liquidite_reduite', 'liquidite_immediate'])) {
                            $class = $value >= 1 ? 'good' : 'warning';
                        }
                    } elseif($action == 'ratios_solvabilite') {
                        if($name == 'autonomie_financiere') {
                            $class = $value >= 0.5 ? 'good' : ($value >= 0.3 ? 'neutral' : 'warning');
                        } elseif($name == 'endettement') {
                            $class = $value <= 1 ? 'good' : ($value <= 2 ? 'neutral' : 'warning');
                        }
                    } elseif($action == 'ratios_rentabilite') {
                        $class = $value > 0 ? 'good' : 'warning';
                        $suffix = '%';
                        $value = $value * 100; // Convertir en pourcentage
                    }
                ?>
                <div class="ratio-item">
                    <div class="ratio-name">
                        <strong><?php echo ucfirst(str_replace('_', ' ', $name)); ?></strong><br>
                        <small style="color: #718096;">
                            <?php 
                            $descriptions = [
                                'liquidite_generale' => 'Capacité à honorer les dettes à court terme',
                                'liquidite_reduite' => 'Liquidité sans les stocks',
                                'liquidite_immediate' => 'Disponibilités immédiates',
                                'autonomie_financiere' => 'Part des capitaux propres dans le financement',
                                'endettement' => 'Niveau d endettement',
                                'rentabilite_actif' => 'Rentabilité des actifs employés',
                                'rentabilite_capitaux' => 'Rentabilité pour les actionnaires'
                            ];
                            echo $descriptions[$name] ?? 'Indicateur financier';
                            ?>
                        </small>
                    </div>
                    <div class="ratio-value <?php echo $class; ?>" style="min-width: 120px; text-align: center;">
                        <?php echo number_format($value, 2, ',', ' '); ?><?php echo $suffix; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Interprétation des résultats -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <div class="card-title">📝 Interprétation des Résultats</div>
            </div>
            <div class="card-content">
                <p><strong>Exercice <?php echo $exercice; ?></strong></p>
                <p style="margin-top: 10px;">
                    <?php
                    // Générer une interprétation automatique basée sur les ratios
                    $interpretation = "L'analyse financière de l'exercice $exercice révèle ";
                    
                    if(isset($ratios_liquidite['liquidite_generale'])) {
                        if($ratios_liquidite['liquidite_generale'] >= 1.5) {
                            $interpretation .= "une excellente liquidité générale. ";
                        } elseif($ratios_liquidite['liquidite_generale'] >= 1) {
                            $interpretation .= "une liquidité générale satisfaisante. ";
                        } else {
                            $interpretation .= "une tension sur la liquidité générale. ";
                        }
                    }
                    
                    if(isset($ratios_solvabilite['autonomie_financiere'])) {
                        if($ratios_solvabilite['autonomie_financiere'] >= 0.5) {
                            $interpretation .= "L'autonomie financière est forte. ";
                        } else {
                            $interpretation .= "L'autonomie financière nécessite attention. ";
                        }
                    }
                    
                    if(isset($ratios_rentabilite['rentabilite_actif'])) {
                        if($ratios_rentabilite['rentabilite_actif'] > 0.05) {
                            $interpretation .= "La rentabilité des actifs est bonne. ";
                        } else {
                            $interpretation .= "La rentabilité des actifs est faible. ";
                        }
                    }
                    
                    echo $interpretation;
                    ?>
                </p>
                <p style="margin-top: 15px; font-style: italic; color: #718096;">
                    Note: Cette analyse est basée sur les normes comptables OHADA. 
                    Pour une interprétation complète, consultez un expert-comptable.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

