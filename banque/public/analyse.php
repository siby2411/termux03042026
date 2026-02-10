<?php
/**
 * PUBLIC/ANALYSE.PHP
 * Outil de Diagnostic Financier et de Reporting ALM pour la Mutuelle.
 * Par l'Expert Diplômé.
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/header.php'; 
require_once '../includes/fonctions.php'; 

$message = '';

// ------------------------------------------------------------------
// --- 1. RÉCUPÉRATION DES DONNÉES FINANCIÈRES BRUTES ---
// ------------------------------------------------------------------

// Données agrégées sur les comptes et crédits
$sql_agregats = "
    SELECT 
        -- Passif de la Mutuelle (Dépôts clients)
        SUM(CASE WHEN co.Statut = 'Ouvert' THEN co.Solde ELSE 0 END) AS TotalDepotsPassif,
        
        -- Actif de la Mutuelle (Prêts octroyés)
        COALESCE(SUM(cr.MontantPrincipal), 0.00) AS TotalPretsActif,
        COALESCE(SUM(CASE WHEN cr.Statut = 'Defaut' THEN cr.MontantPrincipal ELSE 0 END), 0.00) AS PretsDefaut,
        
        -- Fonds de Roulement et Liquidités (Simplifié: on considère une partie des comptes clients comme liquidités de la mutuelle)
        SUM(CASE WHEN co.TypeCompte IN ('CompteCourant', 'Epargne') AND co.Statut = 'Ouvert' THEN co.Solde ELSE 0 END) AS ActifsLiquidesSimules
        
    FROM COMPTES co
    LEFT JOIN CREDITS cr ON co.ClientID = cr.ClientID AND cr.Statut IN ('Approuve', 'EnCours', 'Defaut')
";

$result_agregats = $conn->query($sql_agregats);
$data = $result_agregats ? $result_agregats->fetch_assoc() : [];

// Valeurs Brutes (avec simulation si la DB est vide pour permettre l'affichage)
$TotalDepotsPassif = (float)($data['TotalDepotsPassif'] ?? 750000.00); 
$TotalPretsActif = (float)($data['TotalPretsActif'] ?? 500000.00);
$PretsDefaut = (float)($data['PretsDefaut'] ?? 15000.00);
$ActifsLiquidesSimules = (float)($data['ActifsLiquidesSimules'] ?? 120000.00); 

// Taux Moyen (Critères définis)
$TauxMoyenCredit = 6.50;
$TauxMoyenDepot = 3.00;

// ------------------------------------------------------------------
// --- 2. CALCUL DES INDICATEURS DE DIAGNOSTIC ---
// ------------------------------------------------------------------

// A. Ratio de Liquidité Immédiate (RLI)
// Un RLI supérieur à 1.20 est souvent jugé sain.
$RLI = ($TotalDepotsPassif > 0) ? $ActifsLiquidesSimules / $TotalDepotsPassif : 0;
$RLI_color = ($RLI >= 1.20) ? 'success' : 'danger';
$RLI_status = ($RLI >= 1.20) ? 'Sain' : 'Sous-liquidité';

// B. Taux de Prêts Non Performants (NPL - Non-Performing Loans)
// Un NPL inférieur à 5% est un excellent signe de gestion du risque.
$NPL = ($TotalPretsActif > 0) ? ($PretsDefaut / $TotalPretsActif) * 100 : 0;
$NPL_color = ($NPL <= 5.00) ? 'success' : 'danger';
$NPL_status = ($NPL <= 5.00) ? 'Faible Risque' : 'Risque Élevé';

// C. Marge Nette d'Intérêt (MNI)
$MNI = $TauxMoyenCredit - $TauxMoyenDepot;
$MNI_color = ($MNI > 0) ? 'success' : 'danger';

// ------------------------------------------------------------------
// --- 3. RÉCUPÉRATION DES DONNÉES HISTORIQUES POUR GRAPHIQUES (SIMULATION) ---
// ------------------------------------------------------------------
// Ces données devraient être issues d'une requête SQL GROUP BY MONTH(DateOperation)
$labels_flux = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai'];
$data_depots_mensuels = [35000, 42000, 38000, 50000, 45000];
$data_credits_debloques = [25000, 30000, 20000, 35000, 32000];
$data_defaut_mensuels = [1000, 1500, 500, 2000, 1000];
?>

<h1 class="mt-4"><i class="fas fa-chart-line me-2"></i> Diagnostic et Analyse Financière Expert</h1>
<p class="text-muted">Évaluation de la santé financière et du risque de la Mutuelle.</p>

<hr>
<h2>Indicateurs de Performance et de Risque (KPI)</h2>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-center bg-light p-3">
            <p class="text-muted mb-0">Ratio de Liquidité (RLI)</p>
            <h3 class="display-6 text-<?= $RLI_color ?>"><?= number_format($RLI, 2) ?></h3>
            <p class="small text-muted">Statut: <span class="fw-bold text-<?= $RLI_color ?>"><?= $RLI_status ?></span> (Objectif: > 1.20)</p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-center bg-light p-3">
            <p class="text-muted mb-0">Taux NPL (Prêts Défaillants)</p>
            <h3 class="display-6 text-<?= $NPL_color ?>"><?= number_format($NPL, 2) ?> %</h3>
            <p class="small text-muted">Statut: <span class="fw-bold text-<?= $NPL_color ?>"><?= $NPL_status ?></span> (Objectif: < 5%)</p>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-center bg-light p-3">
            <p class="text-muted mb-0">Marge Nette d'Intérêt</p>
            <h3 class="display-6 text-<?= $MNI_color ?>"><?= number_format($MNI, 2) ?> %</h3>
            <p class="small text-muted">Crédit: <?= number_format($TauxMoyenCredit, 2) ?>% | Épargne: <?= number_format($TauxMoyenDepot, 2) ?>%</p>
        </div>
    </div>
</div>

<hr>
<h2>Analyse des Flux : Dépôts vs Crédits</h2>
<p class="text-muted">Compare l'activité de collecte (Passif) et d'investissement (Actif) de la mutuelle.</p>
<div class="card p-4">
    <canvas id="fluxChart"></canvas>
</div>

<hr>
<h2>Gestion du Risque : Évolution des Prêts Défaillants</h2>
<p class="text-muted">Affiche l'effort mensuel d'investissement vs. la part qui tombe en défaut.</p>
<div class="card p-4">
    <canvas id="defautChart"></canvas>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels_flux = <?= json_encode($labels_flux) ?>;
    const data_depots_mensuels = <?= json_encode($data_depots_mensuels) ?>;
    const data_credits_debloques = <?= json_encode($data_credits_debloques) ?>;
    const data_defaut_mensuels = <?= json_encode($data_defaut_mensuels) ?>;

    // --- GRAPHIQUE 1: FLUX DÉPÔTS vs CRÉDITS ---
    new Chart(document.getElementById('fluxChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels_flux,
            datasets: [{
                label: 'Dépôts Clients (Entrées, Passif)',
                data: data_depots_mensuels,
                backgroundColor: 'rgba(0, 153, 77, 0.7)',
            }, {
                label: 'Crédits Débloqués (Sorties/Actif)',
                data: data_credits_debloques,
                backgroundColor: 'rgba(0, 86, 179, 0.7)',
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { title: { display: true, text: 'Volumes Mensuels de Dépôts et de Crédits (€)' } }
        }
    });

    // --- GRAPHIQUE 2: RISQUE DE DÉFAUT ---
     new Chart(document.getElementById('defautChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels_flux,
            datasets: [{
                label: 'Crédits Débloqués (Échelle de Gauche)',
                data: data_credits_debloques,
                borderColor: '#0056b3',
                yAxisID: 'y1',
                tension: 0.2
            }, {
                label: 'Montant en Défaut (Échelle de Droite)',
                data: data_defaut_mensuels,
                borderColor: '#dc3545',
                yAxisID: 'y2',
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            plugins: { title: { display: true, text: 'Relation entre Déblocage et Montant en Défaut' } },
            scales: {
                y1: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Crédits Débloqués (€)' } },
                y2: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Montant en Défaut (€)' }, grid: { drawOnChartArea: false } }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
