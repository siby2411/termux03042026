<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();
$action = $_GET['action'] ?? 'liste';

if ($action == 'ajouter') {
    $fournisseurs = $pdo->query("SELECT * FROM fournisseurs ORDER BY nom")->fetchAll();
    $produits = $pdo->query("SELECT * FROM produits ORDER BY nom")->fetchAll();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fournisseur_id = $_POST['fournisseur_id'] ?? null;
        $produit_id = $_POST['produit_id'] ?? 0;
        $quantite = (float)($_POST['quantite'] ?? 0);
        $prix_unitaire = (float)($_POST['prix_unitaire'] ?? 0);
        $date_appro = $_POST['date_appro'] ?? date('Y-m-d');
        $reference = trim($_POST['reference'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$produit_id || $quantite <= 0 || $prix_unitaire <= 0) {
            flash('Veuillez remplir tous les champs obligatoires correctement', 'danger');
        } else {
            try {
                $pdo->beginTransaction();
                $total = $quantite * $prix_unitaire;
                
                $stmt = $pdo->prepare("INSERT INTO approvisionnements (fournisseur_id, produit_id, quantite, prix_unitaire, total, date_appro, reference, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fournisseur_id, $produit_id, $quantite, $prix_unitaire, $total, $date_appro, $reference, $notes]);
                
                $stmt = $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel + ? WHERE id = ?");
                $stmt->execute([$quantite, $produit_id]);
                
                $pdo->commit();
                flash('Approvisionnement enregistré avec succès ! Stock mis à jour', 'success');
                header('Location: approvisionnement.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                flash('Erreur: ' . $e->getMessage(), 'danger');
            }
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck-loading me-2"></i>Nouvel approvisionnement</h2>
        <a href="approvisionnement.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    </div>
    
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert"><?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Formulaire d'approvisionnement</h5></div>
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Fournisseur</label><select name="fournisseur_id" class="form-control"><option value="">-- Sélectionner un fournisseur --</option><?php foreach ($fournisseurs as $f): ?><option value="<?= $f['id'] ?>"><?= escape($f['nom']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label>Produit *</label><select name="produit_id" class="form-control" required><option value="">-- Sélectionner un produit --</option><?php foreach ($produits as $p): ?><option value="<?= $p['id'] ?>"><?= escape($p['nom']) ?> (Stock: <?= $p['stock_actuel'] ?>)</option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label>Quantité *</label><input type="number" name="quantite" class="form-control" step="0.001" min="0.001" required></div>
                    <div class="col-md-4 mb-3"><label>Prix unitaire (FCFA) *</label><input type="number" name="prix_unitaire" class="form-control" step="1" min="1" required></div>
                    <div class="col-md-4 mb-3"><label>Total estimé</label><input type="text" id="total_estime" class="form-control" readonly disabled></div>
                    <div class="col-md-6 mb-3"><label>Date</label><input type="date" name="date_appro" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-6 mb-3"><label>Référence</label><input type="text" name="reference" class="form-control" placeholder="BL-2026-XXXX"></div>
                    <div class="col-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>L'approvisionnement augmentera automatiquement le stock du produit sélectionné.</div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                <a href="approvisionnement.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
    <script>
        function calculerTotal() {
            const qte = document.querySelector('input[name="quantite"]').value;
            const prix = document.querySelector('input[name="prix_unitaire"]').value;
            if (qte && prix) document.getElementById('total_estime').value = (parseFloat(qte) * parseFloat(prix)).toLocaleString() + ' FCFA';
            else document.getElementById('total_estime').value = '';
        }
        document.querySelector('input[name="quantite"]').addEventListener('input', calculerTotal);
        document.querySelector('input[name="prix_unitaire"]').addEventListener('input', calculerTotal);
    </script>
    <?php
} elseif ($action == 'supprimer') {
    $id = $_GET['id'] ?? 0;
    if ($id) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT produit_id, quantite FROM approvisionnements WHERE id = ?");
            $stmt->execute([$id]);
            $appro = $stmt->fetch();
            if ($appro) {
                $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel - ? WHERE id = ?")->execute([$appro['quantite'], $appro['produit_id']]);
                $pdo->prepare("DELETE FROM approvisionnements WHERE id = ?")->execute([$id]);
            }
            $pdo->commit();
            flash('Approvisionnement supprimé et stock ajusté', 'success');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('Erreur: ' . $e->getMessage(), 'danger');
        }
    }
    header('Location: approvisionnement.php');
    exit;
} else {
    $approvisionnements = $pdo->query("
        SELECT a.*, p.nom as produit_nom, f.nom as fournisseur_nom 
        FROM approvisionnements a 
        LEFT JOIN produits p ON a.produit_id = p.id 
        LEFT JOIN fournisseurs f ON a.fournisseur_id = f.id 
        ORDER BY a.date_appro DESC, a.id DESC
    ")->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck-loading me-2"></i>Gestion des approvisionnements</h2>
        <a href="approvisionnement.php?action=ajouter" class="btn btn-success"><i class="fas fa-plus me-1"></i>Nouvel approvisionnement</a>
    </div>
    
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert"><?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Produit</th>
                            <th>Fournisseur</th>
                            <th class="text-end">Quantité</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-end">Total</th>
                            <th>Référence</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvisionnements as $a): ?>
                        <tr>
                            <td class="text-nowrap"><?= formatDate($a['date_appro']) ?></td>
                            <td><?= escape($a['produit_nom']) ?></td>
                            <td><?= escape($a['fournisseur_nom'] ?? '-') ?></td>
                            <td class="text-end"><?= number_format($a['quantite'], 2, ',', ' ') ?></td>
                            <td class="text-end"><?= formatMoney($a['prix_unitaire']) ?></td>
                            <td class="text-end fw-bold"><?= formatMoney($a['total']) ?></td>
                            <td><?= escape($a['reference'] ?? '-') ?></td>
                            <td class="text-center">
                                <a href="approvisionnement.php?action=supprimer&id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet approvisionnement ? Le stock sera ajusté.')" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($approvisionnements)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucun approvisionnement enregistré</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

require_once 'footer.php';
?>
