<?php
// Utilisation de __DIR__ pour garantir le chemin absolu vers le répertoire de l'application
// Ceci résout l'erreur "Failed to open stream"

// 1. Connexion à la base de données
// On suppose que le fichier db.php contient la classe Database
require_once __DIR__ . '/config/db.php'; 

// 2. Initialisation de la connexion
$database = new Database();
$pdo = $database->getConnection(); // L'objet PDO est désormais dans $pdo

// --- PARAMÈTRES ET CONSTANTES ---
$frais_fixes_mensuels = 15000.00; 

// Définition de la période (pour filtrer les KPI sur le mois en cours)
$date_courante = new DateTime();
$mois_en_cours_debut = $date_courante->format('Y-m-01');
$date_fin_courante = $date_courante->format('Y-m-d');


// --- 3. CALCUL DES AGRÉGATS FINANCIERS VIA V_Marge_Ventes ---

try {
    // Requête principale pour CA, CDV et Marge Brute, filtrée par le mois
    $sql_kpi = "SELECT 
        SUM(MontantCA) AS TotalCA,
        SUM(MontantCDV) AS TotalCDV,
        SUM(MargeBrute) AS TotalMarge
    FROM 
        V_Marge_Ventes 
    WHERE 
        DateCommande BETWEEN :debut_mois AND :fin_courante"; 

    $stmt_kpi = $pdo->prepare($sql_kpi);
    $stmt_kpi->execute([
        ':debut_mois' => $mois_en_cours_debut,
        ':fin_courante' => $date_fin_courante
    ]);
    $kpi_data = $stmt_kpi->fetch(PDO::FETCH_ASSOC);

    // Initialisation des variables avec 0 en cas de données nulles
    $ca = (float)($kpi_data['TotalCA'] ?? 0);
    $cdv = (float)($kpi_data['TotalCDV'] ?? 0);
    $marge_brute = (float)($kpi_data['TotalMarge'] ?? 0);

    // 4. Calculs dérivés
    $resultat_net_est = $marge_brute - $frais_fixes_mensuels;
    $roi = ($cdv > 0) ? ($marge_brute / $cdv) * 100 : 0;

    // --- 5. CALCUL DE LA VALORISATION DU STOCK VIA V_Valorisation_Stock ---
    $sql_stock = "SELECT ValeurTotaleStock FROM V_Valorisation_Stock";

    // Utilisation de fetchColumn() car la vue retourne une seule colonne/ligne agrégée
    $stock_value = $pdo->query($sql_stock)->fetchColumn();
    $valorisation_stock = (float)($stock_value ?? 0);

} catch (PDOException $e) {
    // Afficher l'erreur en cas de problème de connexion ou de requête
    die("Erreur de calcul des indicateurs : " . $e->getMessage());
}


// --- FONCTION D'AFFICHAGE DES CHIFFRES FORMATÉS ---
function format_currency($amount) {
    return number_format($amount, 2, ',', ' ') . ' €';
}

function format_roi($roi) {
    return number_format($roi, 1, ',', ' ') . ' %';
}

// =========================================================================
// DEBUT DU RENDU HTML
// =========================================================================
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pilote - Gestion Prévisionnelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .kpi-card {
            border-left: 5px solid #007bff;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .neutral { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center"><i class="fas fa-tachometer-alt"></i> Dashboard Pilote</h1>
        <p class="text-center text-muted">Synthèse Financière - Période : <?php echo $mois_en_cours_debut . ' au ' . $date_fin_courante; ?></p>
        <hr>
        
        <div class="row mb-5">
            
            <div class="col-md-3">
                <div class="card kpi-card bg-white p-3">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Chiffre d'Affaires</h6>
                        <h2 class="card-text text-primary"><?php echo format_currency($ca); ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card kpi-card bg-white p-3" style="border-left-color: #28a745;">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Marge Brute HT</h6>
                        <h2 class="card-text <?php echo ($marge_brute >= 0) ? 'positive' : 'negative'; ?>"><?php echo format_currency($marge_brute); ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card kpi-card bg-white p-3" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Résultat Net Est. (Frais: <?php echo format_currency($frais_fixes_mensuels); ?>)</h6>
                        <h2 class="card-text <?php echo ($resultat_net_est >= 0) ? 'positive' : 'negative'; ?>"><?php echo format_currency($resultat_net_est); ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card kpi-card bg-white p-3" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <h6 class="card-title text-muted">ROI (Marge / CDV)</h6>
                        <h2 class="card-text neutral"><?php echo format_roi($roi); ?></h2>
                    </div>
                </div>
            </div>

        </div> <hr>

        <div class="row mb-5">
            <div class="col-md-6">
                 <div class="card kpi-card bg-white p-3" style="border-left-color: #17a2b8;">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Valeur Totale des Stocks (au CUMP)</h6>
                        <h2 class="card-text text-info"><?php echo format_currency($valorisation_stock); ?></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card kpi-card bg-white p-3">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Coût des Ventes (CDV)</h6>
                        <h2 class="card-text text-secondary"><?php echo format_currency($cdv); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</body>
</html>
