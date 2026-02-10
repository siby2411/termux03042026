<?php
// /var/www/piece_auto/modules/reporting_strategique.php


include_once '../config/Database.php';
include_once '../includes/auth_check.php'; // Utilisez le chemin relatif simple comme les autres fichiers
include '../includes/header.php';




$page_title = "Tableau de Bord Stratégique & Reporting";
$database = new Database();
$db = $database->getConnection();
$message_status = "";

// Vérification de l'accès (Admin ou Analyse seulement)
if ($_SESSION['user_role'] != 'Admin' && $_SESSION['user_role'] != 'Analyse') {
    echo "<div class='alert alert-danger'>Accès refusé. Seuls les Administrateurs et Analystes peuvent consulter ce rapport.</div>";
    include '../includes/footer.php';
    exit;
}

// Fonction utilitaire pour le formatage monétaire
function format_currency($amount) {
    return number_format($amount, 2, ',', ' ') . ' €';
}

// Définition de la période d'analyse (Exemple: Derniers 30 jours)
$start_date = date('Y-m-d', strtotime('-30 days'));
$end_date = date('Y-m-d H:i:s');


// --- 1. INDICATEURS CLÉS DE PERFORMANCE (KPI) ---

// A. Calcul du Chiffre d'Affaires, Coût des Ventes (COGS) et Marge sur la période
$query_kpis = "
    SELECT 
        SUM(V.total_ht) AS total_ca,
        -- Estimation du COGS (Coût des Biens Vendus) : Somme (Prix d'achat de la pièce * quantité vendue)
        SUM(LV.quantite * P.prix_achat) AS total_cogs
    FROM VENTES V
    JOIN LIGNES_VENTE LV ON V.id_vente = LV.id_vente
    JOIN PIECES P ON LV.id_piece = P.id_piece
    WHERE V.date_vente BETWEEN :start_date AND :end_date
";
$stmt_kpis = $db->prepare($query_kpis);
$stmt_kpis->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$kpis = $stmt_kpis->fetch(PDO::FETCH_ASSOC);

$ca = (float)$kpis['total_ca'] ?? 0;
$cogs = (float)$kpis['total_cogs'] ?? 0;
$marge = $ca - $cogs;
$taux_marge = ($ca > 0) ? ($marge / $ca) * 100 : 0;

// B. Nombre de commandes traitées
$query_nb_ventes = "SELECT COUNT(id_vente) AS nb FROM VENTES WHERE date_vente BETWEEN :start_date AND :end_date";
$stmt_nb_ventes = $db->prepare($query_nb_ventes);
$stmt_nb_ventes->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$nb_ventes = (int)$stmt_nb_ventes->fetchColumn();


// --- 2. ANALYSE DU STOCK (Décision sur le réapprovisionnement) ---

// A. Pièces à faible stock (Risque de rupture)
$query_low_stock = "
    SELECT 
        P.nom_piece, P.reference_sku, S.quantite_dispo, P.prix_achat 
    FROM PIECES P
    JOIN STOCK S ON P.id_piece = S.id_piece
    WHERE S.quantite_dispo <= 10 -- Seuil critique fixé à 10
    ORDER BY S.quantite_dispo ASC
    LIMIT 10
";
// Correction Ligne 68 (précédemment S.quantite_stock) : Remplacé par S.quantite_dispo
// Correction Ligne 70 (précédemment S.quantite_stock) : Remplacé par S.quantite_dispo
$low_stock = $db->query($query_low_stock)->fetchAll(PDO::FETCH_ASSOC);

// B. Pièces à stock élevé (Risque de surstock/obsolescence)
$query_high_stock = "
    SELECT 
        P.nom_piece, P.reference_sku, S.quantite_dispo, P.prix_achat 
    FROM PIECES P
    JOIN STOCK S ON P.id_piece = S.id_piece
    WHERE S.quantite_dispo >= 500 -- Seuil élevé fixé à 500
    ORDER BY S.quantite_dispo DESC
    LIMIT 10
";
// Correction Ligne 81 (précédemment S.quantite_stock) : Remplacé par S.quantite_dispo
// Correction Ligne 83 (précédemment S.quantite_stock) : Remplacé par S.quantite_dispo
$high_stock = $db->query($query_high_stock)->fetchAll(PDO::FETCH_ASSOC);


// --- 3. TOP PRODUITS (Analyse de la demande) ---

// A. Top 5 des pièces vendues en quantité
$query_top_pieces_qte = "
    SELECT 
        P.nom_piece, P.reference_sku, SUM(LV.quantite) AS total_qte_vendue
    FROM LIGNES_VENTE LV
    JOIN PIECES P ON LV.id_piece = P.id_piece
    JOIN VENTES V ON LV.id_vente = V.id_vente
    WHERE V.date_vente BETWEEN :start_date AND :end_date
    GROUP BY P.id_piece
    ORDER BY total_qte_vendue DESC
    LIMIT 5
";
$stmt_top_qte = $db->prepare($query_top_pieces_qte);
$stmt_top_qte->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$top_pieces_qte = $stmt_top_qte->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> <?= $page_title ?></h2>
        <p class="text-muted">Analyse des données sur la période : **<?= date('d/m/Y', strtotime($start_date)) ?>** au **<?= date('d/m/Y', strtotime($end_date)) ?>**</p>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card text-center bg-success text-white shadow">
            <div class="card-body">
                <h5 class="card-title">Chiffre d'Affaires HT</h5>
                <p class="card-text fs-3 fw-bold"><?= format_currency($ca) ?></p>
                <small><?= $nb_ventes ?> Ventes</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center bg-primary text-white shadow">
            <div class="card-body">
                <h5 class="card-title">Marge Brute Estimée</h5>
                <p class="card-text fs-3 fw-bold"><?= format_currency($marge) ?></p>
                <small>Coût des Ventes (COGS): <?= format_currency($cogs) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center bg-warning shadow">
            <div class="card-body">
                <h5 class="card-title">Taux de Marge Moyen</h5>
                <p class="card-text fs-3 fw-bold"><?= number_format($taux_marge, 2) ?> %</p>
                <small>Décision: Ajuster les prix de vente pour atteindre la cible.</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h4 class="mb-3 text-danger"><i class="fas fa-bell"></i> Pièces en Rupture (Seuil ≤ 10)</h4>
        <div class="card shadow p-3">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Pièce</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Coût Achat U.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($low_stock)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Pas de risque de rupture identifié.</td></tr>
                    <?php else: ?>
                        <?php foreach ($low_stock as $piece): ?>
                            <tr>
                                <td><?= htmlspecialchars($piece['nom_piece']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($piece['quantite_dispo']) ?></td> <td class="text-end"><?= format_currency($piece['prix_achat']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <small class="text-danger mt-2">Décision: Créer des commandes d'achat urgentes pour ces articles.</small>
        </div>
    </div>

    <div class="col-md-6">
        <h4 class="mb-3 text-info"><i class="fas fa-box-open"></i> Pièces en Surstock (Seuil ≥ 500)</h4>
        <div class="card shadow p-3">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Pièce</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Valeur Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($high_stock)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Pas de surstock important identifié.</td></tr>
                    <?php else: ?>
                        <?php foreach ($high_stock as $piece): ?>
                            <tr>
                                <td><?= htmlspecialchars($piece['nom_piece']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($piece['quantite_dispo']) ?></td> <td class="text-end"><?= format_currency($piece['quantite_dispo'] * $piece['prix_achat']) ?></td> </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <small class="text-info mt-2">Décision: Lancer des promotions ou des soldes pour réduire les stocks.</small>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h4 class="mb-3 text-primary"><i class="fas fa-star"></i> Top 5 des Pièces les Plus Demandées</h4>
        <ss="card shadow p-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Référence Pièce</th>
                            <th>Désignation</th>
                            <th class="text-end">Quantité Vendue (30 jours)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_pieces_qte)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Aucune vente enregistrée sur la période.</td></tr>
                        <?php else: ?>
                            <?php $rank = 1; foreach ($top_pieces_qte as $piece): ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($piece['reference_sku']) ?></td>
                                    <td><?= htmlspecialchars($piece['nom_piece']) ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($piece['total_qte_vendue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <small class="mt-2">Décision: Garantir un stock de sécurité suffisant pour ces références clés.</small>
        </div>
    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
