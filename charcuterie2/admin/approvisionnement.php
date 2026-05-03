<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();
$action = $_GET['action'] ?? 'liste';

if ($action == 'ajouter') {
    $fournisseurs = $pdo->query("SELECT * FROM fournisseurs WHERE actif = 1 ORDER BY nom")->fetchAll();
    $produits = $pdo->query("SELECT * FROM produits WHERE actif = 1 ORDER BY nom")->fetchAll();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fournisseur_id = $_POST['fournisseur_id'] ?? null;
        $produit_id = $_POST['produit_id'] ?? null;
        $quantite = $_POST['quantite'] ?? 0;
        $prix_unitaire = $_POST['prix_unitaire'] ?? 0;
        $date_appro = $_POST['date_appro'] ?? date('Y-m-d');
        $reference = $_POST['reference'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!$produit_id || !$quantite || !$prix_unitaire) {
            $_SESSION['flash'] = ['message' => 'Veuillez remplir tous les champs obligatoires', 'type' => 'danger'];
        } else {
            try {
                $pdo->beginTransaction();
                
                $total = $quantite * $prix_unitaire;
                
                // Enregistrer l'approvisionnement avec la structure existante
                $stmt = $pdo->prepare("INSERT INTO approvisionnements (produit_id, fournisseur_id, quantite, prix_unitaire, total, date_appro, reference, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$produit_id, $fournisseur_id, $quantite, $prix_unitaire, $total, $date_appro, $reference, $notes]);
                
                // Mettre à jour le stock
                $stmt = $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel + ? WHERE id = ?");
                $stmt->execute([$quantite, $produit_id]);
                
                $pdo->commit();
                $_SESSION['flash'] = ['message' => 'Approvisionnement enregistré avec succès ! Réf: ' . $reference, 'type' => 'success'];
                header('Location: approvisionnement.php');
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['flash'] = ['message' => 'Erreur: ' . $e->getMessage(), 'type' => 'danger'];
            }
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck me-2"></i>Nouvel approvisionnement</h2>
        <a href="approvisionnement.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    </div>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    
    <form method="post">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Produit *</label>
                        <select name="produit_id" class="form-control" required>
                            <option value="">-- Sélectionner un produit --</option>
                            <?php foreach ($produits as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= escape($p['nom']) ?> (Stock: <?= $p['stock_actuel'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Fournisseur</label>
                        <select name="fournisseur_id" class="form-control">
                            <option value="">-- Sélectionner un fournisseur --</option>
                            <?php foreach ($fournisseurs as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= escape($f['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Quantité *</label>
                        <input type="number" name="quantite" class="form-control" step="1" min="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Prix unitaire (FCFA) *</label>
                        <input type="number" name="prix_unitaire" class="form-control" step="100" min="0" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Date d'approvisionnement</label>
                        <input type="date" name="date_appro" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Référence facture</label>
                        <input type="text" name="reference" class="form-control" placeholder="Facture N°...">
                    </div>
                    <div class="col-12 mb-3">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Informations complémentaires..."></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            <a href="approvisionnement.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
    <?php
} else {
    // Liste des approvisionnements avec recherche
    $search = $_GET['search'] ?? '';
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';
    
    $sql = "SELECT a.*, p.nom as produit_nom, f.nom as fournisseur_nom 
            FROM approvisionnements a 
            LEFT JOIN produits p ON a.produit_id = p.id 
            LEFT JOIN fournisseurs f ON a.fournisseur_id = f.id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (p.nom LIKE ? OR a.reference LIKE ? OR f.nom LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if (!empty($date_debut)) {
        $sql .= " AND a.date_appro >= ?";
        $params[] = $date_debut;
    }
    if (!empty($date_fin)) {
        $sql .= " AND a.date_appro <= ?";
        $params[] = $date_fin;
    }
    
    $sql .= " ORDER BY a.date_appro DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $approvisionnements = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck me-2"></i>Gestion des approvisionnements</h2>
        <a href="approvisionnement.php?action=ajouter" class="btn btn-success"><i class="fas fa-plus me-1"></i>Nouvel approvisionnement</a>
    </div>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    
    <!-- Formulaire de recherche -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Recherche avancée</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label>Recherche</label>
                    <input type="text" name="search" class="form-control" placeholder="Produit, facture, fournisseur..." value="<?= escape($search) ?>">
                </div>
                <div class="col-md-3">
                    <label>Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
                </div>
                <div class="col-md-3">
                    <label>Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Rechercher</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Produit</th><th>Fournisseur</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th><th>Référence</th><th>Actions</th> </thead>
                    <tbody>
                        <?php foreach ($approvisionnements as $a): ?>
                        响应
                            响应<?= formatDate($a['date_appro']) ?>响应
                            响应<strong><?= escape($a['produit_nom']) ?></strong>响应
                            响应<?= escape($a['fournisseur_nom'] ?? '-') ?>响应
                            响应<?= $a['quantite'] ?>响应
                            响应<?= formatMoney($a['prix_unitaire']) ?>响应
                            <td class="fw-bold"><?= formatMoney($a['total']) ?>响应
                            响应
                                <?php if ($a['reference']): ?>
                                    <span class="badge bg-info"><?= escape($a['reference']) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            响应
                            响应
                                <a href="bon_livraison.php?type=appro&id=<?= $a['id'] ?>" class="btn btn-sm btn-success" title="Bon de livraison">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="approvisionnement_detail.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                            响应
                         ?>
                        <?php endforeach; ?>
                        <?php if (empty($approvisionnements)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucun approvisionnement trouvé</td></tr>
                        <?php endif; ?>
                    </tbody>
                 ?>
            </div>
        </div>
    </div>
    <?php
}

require_once 'footer.php';
?>
