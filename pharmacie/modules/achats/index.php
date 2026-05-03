<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

$page_title = "Historique des Achats";
$active_menu = "achats";
include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';

// Récupération des achats avec le nom du fournisseur
$achats = Database::query("
    SELECT a.*, f.nom as fournisseur_nom 
    FROM achats a 
    LEFT JOIN fournisseurs f ON a.fournisseur_id = f.id 
    ORDER BY a.date_achat DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Achats & Arrivages</h3>
        <p class="text-muted small">Suivi des entrées en stock</p>
    </div>
    <a href="nouveau.php" class="btn-omega shadow-sm">
        <i class="bi bi-cart-plus me-2"></i> Nouvel Achat / Arrivage
    </a>
</div>

<div class="omega-card shadow-sm border-0">
    <div class="omega-card-head gold-head">
        <i class="bi bi-journal-text me-2"></i> DERNIERS ARRIVAGES ENREGISTRÉS
    </div>
    <div class="bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Date</th>
                    <th>Référence</th>
                    <th>Fournisseur</th>
                    <th class="text-end">Montant Total</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($achats)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucun achat enregistré pour le moment.</td></tr>
                <?php else: foreach($achats as $a): ?>
                <tr>
                    <td class="ps-3"><?= date('d/m/Y', strtotime($a['date_achat'])) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($a['reference_facture'] ?: 'N/A') ?></span></td>
                    <td class="fw-bold"><?= htmlspecialchars($a['fournisseur_nom']) ?></td>
                    <td class="text-end fw-bold text-success"><?= number_format($a['montant_ttc'], 0, ',', ' ') ?> F</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border" title="Voir détails"><i class="bi bi-eye"></i></button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
