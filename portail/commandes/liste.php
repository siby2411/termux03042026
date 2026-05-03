<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$pdo = getPDO();
$type_filter = $_GET['type'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

$sql = "SELECT c.*, 
        CASE WHEN c.type_commande = 'vente' THEN cl.nom ELSE f.nom END as partenaire_nom,
        CASE WHEN c.type_commande = 'vente' THEN cl.prenom ELSE '' END as partenaire_prenom
        FROM commandes c
        LEFT JOIN clients cl ON c.client_id = cl.id
        LEFT JOIN fournisseurs f ON c.fournisseur_id = f.id
        WHERE 1=1";

$params = [];
if ($type_filter) {
    $sql .= " AND c.type_commande = ?";
    $params[] = $type_filter;
}
if ($statut_filter) {
    $sql .= " AND c.statut = ?";
    $params[] = $statut_filter;
}
$sql .= " ORDER BY c.date_commande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-cart me-2"></i>Gestion des commandes</h2>
            <a href="ajouter.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Nouvelle commande
            </a>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label>Type de commande</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="vente" <?= $type_filter == 'vente' ? 'selected' : '' ?>>Vente (Client)</option>
                            <option value="achat" <?= $type_filter == 'achat' ? 'selected' : '' ?>>Achat (Fournisseur)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="brouillon" <?= $statut_filter == 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                            <option value="confirmée" <?= $statut_filter == 'confirmée' ? 'selected' : '' ?>>Confirmée</option>
                            <option value="en_préparation" <?= $statut_filter == 'en_préparation' ? 'selected' : '' ?>>En préparation</option>
                            <option value="expédiée" <?= $statut_filter == 'expédiée' ? 'selected' : '' ?>>Expédiée</option>
                            <option value="livrée" <?= $statut_filter == 'livrée' ? 'selected' : '' ?>>Livrée</option>
                            <option value="annulée" <?= $statut_filter == 'annulée' ? 'selected' : '' ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <a href="liste.php" class="btn btn-secondary d-block">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des commandes -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° commande</th>
                                <th>Type</th>
                                <th>Partenaire</th>
                                <th>Date</th>
                                <th>Total HT</th>
                                <th>Total TTC</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $c): ?>
                            <tr>
                                <td><code><?= escape($c['numero_commande']) ?></code></td>
                                <td>
                                    <span class="badge <?= $c['type_commande'] == 'vente' ? 'bg-success' : 'bg-info' ?>">
                                        <?= $c['type_commande'] == 'vente' ? 'Vente' : 'Achat' ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= escape($c['partenaire_nom']) ?></strong>
                                    <?php if ($c['partenaire_prenom']): ?><br><small><?= escape($c['partenaire_prenom']) ?></small><?php endif; ?>
                                </td>
                                <td><?= formatDate($c['date_commande']) ?></td>
                                <td><?= formatMoney($c['total_ht']) ?></td>
                                <td class="fw-bold"><?= formatMoney($c['total_ttc']) ?></td>
                                <td>
                                    <?php
                                    $statut_class = match($c['statut']) {
                                        'livrée' => 'bg-success',
                                        'annulée' => 'bg-danger',
                                        'confirmée', 'en_préparation' => 'bg-warning',
                                        'expédiée' => 'bg-info',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statut_class ?>"><?= escape($c['statut']) ?></span>
                                </td>
                                <td>
                                    <a href="fiche.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($c['statut'] != 'livrée' && $c['statut'] != 'annulée'): ?>
                                    <a href="modifier.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="imprimer.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Imprimer" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($commandes)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Aucune commande trouvée
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
