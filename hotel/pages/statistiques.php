<?php
// 1. Définition de la période
$mois = $_GET['mois'] ?? date('m');
$annee = $_GET['annee'] ?? date('Y');

// 2. Calcul du Chiffre d'Affaires (CA) 
// Correction : On s'assure que c.prix existe et on gère les dates
$stmt_ca = $pdo->prepare("
    SELECT SUM(c.prix * DATEDIFF(r.date_depart, r.date_arrivee)) as total_ca 
    FROM reservations r 
    JOIN chambres c ON r.chambre_id = c.id 
    WHERE MONTH(r.date_arrivee) = ? AND YEAR(r.date_arrivee) = ? AND r.statut = 'Confirmée'
");
$stmt_ca->execute([$mois, $annee]);
$ca = $stmt_ca->fetchColumn() ?: 0;

// 3. Calcul des Charges Opérationnelles
$stmt_charges = $pdo->prepare("SELECT SUM(montant) FROM charges WHERE MONTH(date_charge) = ? AND YEAR(date_charge) = ?");
$stmt_charges->execute([$mois, $annee]);
$total_charges = $stmt_charges->fetchColumn() ?: 0;

// 4. Calcul de la Masse Salariale
$stmt_paies = $pdo->prepare("SELECT SUM(montant) FROM paies WHERE MONTH(date_paiement) = ? AND YEAR(date_paiement) = ?");
$stmt_paies->execute([$mois, $annee]);
$total_paies = $stmt_paies->fetchColumn() ?: 0;

// 5. Bilan Final
$total_depenses = $total_charges + $total_paies;
$benefice_net = $ca - $total_depenses;
$marge = ($ca > 0) ? ($benefice_net / $ca) * 100 : 0;
?>

<div class="card border-0 shadow-sm mb-4 border-start border-5 border-warning">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0">Rapport Financier OMEGA</h4>
            <small class="text-muted text-uppercase small">Analyse de performance mensuelle</small>
        </div>
        <form class="d-flex gap-2 bg-light p-2 rounded shadow-sm">
            <input type="hidden" name="page" value="statistiques">
            <select name="mois" class="form-select form-select-sm border-0 bg-transparent fw-bold">
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?= sprintf('%02d', $m) ?>" <?= $mois == $m ? 'selected' : '' ?>>Mois <?= $m ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">Actualiser</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-dark text-white h-100">
            <div class="d-flex justify-content-between mb-3">
                <span class="small opacity-75">CHIFFRE D'AFFAIRES</span>
                <i class="bi bi-arrow-up-right-circle text-success"></i>
            </div>
            <h2 class="fw-bold"><?= number_format($ca, 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h2>
            <div class="progress mt-3" style="height: 4px; background: rgba(255,255,255,0.1);">
                <div class="progress-bar bg-success" style="width: 100%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white h-100 border-bottom border-danger border-4">
            <div class="d-flex justify-content-between mb-3">
                <span class="small text-muted">DÉPENSES TOTALES</span>
                <i class="bi bi-cash-stack text-danger"></i>
            </div>
            <h2 class="fw-bold"><?= number_format($total_depenses, 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h2>
            <p class="mb-0 small text-muted">Salaires : <?= number_format($total_paies, 0, ',', ' ') ?> F</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100 <?= $benefice_net >= 0 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
            <div class="d-flex justify-content-between mb-3">
                <span class="small opacity-75">RÉSULTAT NET</span>
                <i class="bi bi-wallet2"></i>
            </div>
            <h2 class="fw-bold"><?= number_format($benefice_net, 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h2>
            <div class="mt-2 small">Marge nette : <b><?= round($marge, 1) ?>%</b></div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-4">Structure des Coûts OMEGA INFORMATIQUE</h5>
            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <tbody>
                        <tr>
                            <td width="30%">Exploitation (Senelec/Eau)</td>
                            <td width="50%">
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-warning" style="width: <?= ($ca > 0) ? ($total_charges/$ca)*100 : 0 ?>%"></div>
                                </div>
                            </td>
                            <td class="text-end fw-bold"><?= number_format($total_charges, 0, ',', ' ') ?> F</td>
                        </tr>
                        <tr>
                            <td>Capital Humain (Paies)</td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-info" style="width: <?= ($ca > 0) ? ($total_paies/$ca)*100 : 0 ?>%"></div>
                                </div>
                            </td>
                            <td class="text-end fw-bold"><?= number_format($total_paies, 0, ',', ' ') ?> F</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
