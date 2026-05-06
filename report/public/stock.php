<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des stocks - Inventaire";
$page_icon = "box-seam";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des mouvements de stock (comptes classe 3)
$sql = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END) as entrees,
        SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END) as sorties
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON c.compte_id IN (e.compte_debite_id, e.compte_credite_id)
    WHERE c.compte_id BETWEEN 30 AND 39
    GROUP BY c.compte_id, c.intitule_compte
    ORDER BY c.compte_id
";
$stmt = $pdo->query($sql);
$stocks = $stmt->fetchAll();

// Traitement formulaire ajout stock
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_stock') {
        $compte_id = (int)$_POST['compte_id'];
        $montant = (float)$_POST['montant'];
        $libelle = trim($_POST['libelle']);
        
        if ($compte_id > 0 && $montant > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, montant, user_id) 
                                       VALUES (CURDATE(), ?, ?, ?, ?)");
                $stmt->execute(["Entrée en stock - $libelle", $compte_id, $montant, $_SESSION['user_id']]);
                $message = "✅ Stock ajouté avec succès";
                // Rafraîchir la page
                header("Refresh:0");
            } catch (Exception $e) {
                $message = "❌ Erreur : " . $e->getMessage();
            }
        }
    }
}

$total_stock = 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Gestion des stocks (Classe 3)</h5>
                    <small class="text-muted">Inventaire permanent des marchandises et matières premières</small>
                </div>
                <button type="button" class="btn-omega btn-sm" data-bs-toggle="modal" data-bs-target="#addStockModal">
                    <i class="bi bi-plus-lg"></i> Nouvel article
                </button>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>N° Compte</th>
                                <th>Intitulé</th>
                                <th>Entrées (FCFA)</th>
                                <th>Sorties (FCFA)</th>
                                <th>Stock actuel (FCFA)</th>
                                <th>Quantité estimée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stocks as $stock): 
                                $valeur_stock = $stock['entrees'] - $stock['sorties'];
                                $total_stock += $valeur_stock;
                                $quantite_estimee = $valeur_stock > 0 ? floor($valeur_stock / 1000) : 0;
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $stock['compte_id'] ?></td>
                                <td><?= htmlspecialchars($stock['intitule_compte']) ?></td>
                                <td class="text-end text-success"><?= number_format($stock['entrees'], 0, ',', ' ') ?></td>
                                <td class="text-end text-danger"><?= number_format($stock['sorties'], 0, ',', ' ') ?></td>
                                <td class="text-end fw-bold <?= $valeur_stock > 0 ? 'text-primary' : 'text-muted' ?>">
                                    <?= number_format($valeur_stock, 0, ',', ' ') ?>
                                </td>
                                <td class="text-center">
                                    <?php if($quantite_estimee > 0): ?>
                                        <span class="badge bg-info">~ <?= number_format($quantite_estimee) ?> unités</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">En rupture</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="4" class="text-end">VALEUR TOTALE DU STOCK :</td>
                                <td class="text-end text-primary"><?= number_format($total_stock, 0, ',', ' ') ?> F</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Indicateurs de gestion des stocks -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-box fs-2 text-primary"></i>
                                <h6>Rotation des stocks</h6>
                                <h5>-</h5>
                                <small>Nombre de renouvellements</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-clock fs-2 text-warning"></i>
                                <h6>Délai d'écoulement</h6>
                                <h5>-</h5>
                                <small>Jours de stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-percent fs-2 text-success"></i>
                                <h6>Taux de couverture</h6>
                                <h5><?= $total_stock > 0 ? '100%' : '0%' ?></h5>
                                <small>Stock / CA</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Stock -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Ajouter un article en stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_stock">
                    <div class="mb-3">
                        <label class="form-label">Compte de stock</label>
                        <select name="compte_id" class="form-select" required>
                            <option value="">Sélectionner un compte</option>
                            <?php foreach($stocks as $stock): ?>
                                <option value="<?= $stock['compte_id'] ?>"><?= $stock['compte_id'] ?> - <?= htmlspecialchars($stock['intitule_compte']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="libelle" class="form-control" placeholder="Nom de l'article" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valeur (FCFA)</label>
                        <input type="number" name="montant" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-omega">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
