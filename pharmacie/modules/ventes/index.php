<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

$page_title = "Historique des Ventes";
$active_menu = "ventes";
include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';

// --- FILTRES ---
$caissier_id = $_GET['caissier_id'] ?? '';
$date_debut  = $_GET['date_debut'] ?? date('Y-m-d');
$date_fin    = $_GET['date_fin'] ?? date('Y-m-d');
$periode     = $_GET['periode'] ?? 'jour'; // jour, mois, periode

// Récupérer la liste des caissiers pour le menu déroulant
$caissiers = Database::query("SELECT id, prenom, nom FROM utilisateurs WHERE actif = 1 ORDER BY nom ASC");

// Construction de la requête SQL
$sql = "SELECT v.*, u.prenom as u_prenom, u.nom as u_nom 
        FROM ventes v 
        LEFT JOIN utilisateurs u ON v.utilisateur_id = u.id 
        WHERE 1=1";
$params = [];

if ($caissier_id) {
    $sql .= " AND v.utilisateur_id = ?";
    $params[] = $caissier_id;
}

if ($periode == 'jour') {
    $sql .= " AND DATE(v.date_vente) = ?";
    $params[] = $date_debut;
} elseif ($periode == 'mois') {
    $sql .= " AND MONTH(v.date_vente) = MONTH(?) AND YEAR(v.date_vente) = YEAR(?)";
    $params[] = $date_debut;
    $params[] = $date_debut;
} else {
    $sql .= " AND DATE(v.date_vente) BETWEEN ? AND ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
}

$sql .= " ORDER BY v.date_vente DESC";
$ventes = Database::query($sql, $params);

// Calcul du total de la sélection
$total_selection = 0;
foreach($ventes as $v) { $total_selection += $v['montant_total']; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-receipt-cutoff me-2"></i>Historique des Ventes</h3>
    <div class="bg-white p-2 px-3 rounded shadow-sm border border-success">
        <small class="text-muted d-block">Total Sélectionné</small>
        <span class="h5 fw-bold text-success mb-0"><?= number_format($total_selection, 0, ',', ' ') ?> F CFA</span>
    </div>
</div>

<div class="omega-card p-4 mb-4 shadow-sm">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="small fw-bold">Caissier / Agent</label>
            <select name="caissier_id" class="form-select">
                <option value="">Tous les agents</option>
                <?php foreach($caissiers as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $caissier_id == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Type de Période</label>
            <select name="periode" class="form-select" onchange="this.form.submit()">
                <option value="jour" <?= $periode == 'jour'?'selected':'' ?>>Par Jour</option>
                <option value="mois" <?= $periode == 'mois'?'selected':'' ?>>Par Mois</option>
                <option value="range" <?= $periode == 'range'?'selected':'' ?>>Personnalisé</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small fw-bold">Date Début / Unique</label>
            <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
        </div>
        <?php if($periode == 'range'): ?>
        <div class="col-md-2">
            <label class="small fw-bold">Date Fin</label>
            <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
        </div>
        <?php endif; ?>
        <div class="col-md-3">
            <button type="submit" class="btn-omega w-100 py-2"><i class="bi bi-filter"></i> Appliquer le filtre</button>
        </div>
    </form>
</div>

<div class="omega-card shadow-sm border-0">
    <div class="omega-card-head blue-head">LISTE DES TRANSACTIONS</div>
    <div class="bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Réf. Vente</th>
                    <th>Date & Heure</th>
                    <th>Caissier</th>
                    <th>Mode Paiement</th>
                    <th class="text-end pe-3">Montant Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($ventes)): ?>
                <tr><td colspan="5" class="text-center py-5 text-muted italic">Aucune vente trouvée pour cette période.</td></tr>
                <?php else: foreach($ventes as $v): ?>
                <tr>
                    <td class="ps-3 fw-bold text-primary">#<?= str_pad($v['id'], 6, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?></td>
                    <td>
                        <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i><?= htmlspecialchars($v['u_prenom'].' '.$v['u_nom']) ?></span>
                    </td>
                    <td><span class="small"><?= htmlspecialchars($v['mode_paiement'] ?? 'Espèces') ?></span></td>
                    <td class="text-end pe-3 fw-bold"><?= number_format($v['montant_total'], 0, ',', ' ') ?> F</td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
