<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();
$action = $_GET['action'] ?? 'liste';

if ($action == 'nouvelle') {
    $produits = $pdo->query("SELECT * FROM produits WHERE actif = 1 ORDER BY nom")->fetchAll();
    $clients = $pdo->query("SELECT * FROM clients WHERE actif = 1 ORDER BY nom")->fetchAll();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client_id = $_POST['client_id'] ?? null;
        $produits_ids = $_POST['produits'] ?? [];
        $quantites = $_POST['quantites'] ?? [];
        $prix = $_POST['prix'] ?? [];
        
        if (empty($produits_ids)) {
            flash('Aucun produit sélectionné', 'danger');
        } else {
            try {
                $pdo->beginTransaction();
                
                $numero_vente = 'VNT-' . date('Ymd') . '-' . rand(1000, 9999);
                $total_ht = 0;
                $lignes = [];
                
                foreach ($produits_ids as $key => $prod_id) {
                    $qte = $quantites[$key] ?? 1;
                    $prix_unitaire = $prix[$key] ?? 0;
                    $total_ligne = $prix_unitaire * $qte;
                    $total_ht += $total_ligne;
                    $lignes[] = ['produit_id' => $prod_id, 'quantite' => $qte, 'prix' => $prix_unitaire];
                    
                    // Vérifier le stock
                    $stmt = $pdo->prepare("SELECT stock_actuel FROM produits WHERE id = ?");
                    $stmt->execute([$prod_id]);
                    $stock = $stmt->fetchColumn();
                    if ($stock < $qte) {
                        throw new Exception("Stock insuffisant pour le produit ID $prod_id");
                    }
                }
                
                $tva = $total_ht * 0.18;
                $total_ttc = $total_ht + $tva;
                
                // Créer la vente
                $stmt = $pdo->prepare("INSERT INTO ventes (numero_vente, client_id, date_vente, total_ht, tva, total_ttc, statut) VALUES (?, ?, NOW(), ?, ?, ?, 'confirmée')");
                $stmt->execute([$numero_vente, $client_id, $total_ht, $tva, $total_ttc]);
                $vente_id = $pdo->lastInsertId();
                
                // Insérer les lignes et diminuer le stock
                foreach ($lignes as $l) {
                    $total_ligne = $l['prix'] * $l['quantite'];
                    $stmt = $pdo->prepare("INSERT INTO ventes_lignes (vente_id, produit_id, quantite, prix_unitaire, total_ht) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$vente_id, $l['produit_id'], $l['quantite'], $l['prix'], $total_ligne]);
                    
                    // Diminuer le stock
                    $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel - ? WHERE id = ?")->execute([$l['quantite'], $l['produit_id']]);
                }
                
                $pdo->commit();
                flash('Vente enregistrée avec succès ! Stock diminué', 'success');
                header('Location: ventes.php');
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                flash('Erreur: ' . $e->getMessage(), 'danger');
            }
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-cart me-2"></i>Nouvelle vente</h2>
        <a href="ventes.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    </div>
    
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <form method="post" id="formVente">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white"><h5 class="mb-0">Informations client</h5></div>
            <div class="card-body">
                <select name="client_id" class="form-control" required>
                    <option value="">-- Sélectionner un client --</option>
                    <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= escape($c['nom'] . ' ' . ($c['prenom'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Produits</h5></div>
            <div class="card-body">
                <div id="produits-container">
                    <div class="row produit-row mb-3">
                        <div class="col-md-5"><label>Produit</label><select name="produits[]" class="form-control produit-select" required><option value="">-- Sélectionner --</option><?php foreach ($produits as $p): ?><option value="<?= $p['id'] ?>" data-prix="<?= $p['prix_vente'] ?>" data-stock="<?= $p['stock_actuel'] ?>"><?= escape($p['nom']) ?> (Stock: <?= $p['stock_actuel'] ?>)</option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label>Quantité</label><input type="number" name="quantites[]" class="form-control quantite" value="1" min="1" step="1" required></div>
                        <div class="col-md-3"><label>Prix unitaire</label><input type="number" name="prix[]" class="form-control prix-unitaire" step="100" required></div>
                        <div class="col-md-1"><label>&nbsp;</label><button type="button" class="btn btn-danger remove-produit w-100"><i class="fas fa-trash"></i></button></div>
                    </div>
                </div>
                <button type="button" id="add-produit" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-plus me-1"></i>Ajouter un produit</button>
                <hr>
                <div class="row"><div class="col-md-6 offset-md-6"><table class="table table-bordered"><tr><th>Total HT</th><td id="total-ht">0 FCFA</td></tr><tr><th>TVA (18%)</th><td id="total-tva">0 FCFA</td></tr><tr class="table-active"><th>Total TTC</th><td id="total-ttc"><strong>0 FCFA</strong></td></tr></table></div></div>
            </div>
        </div>
        
        <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer la vente</button><a href="ventes.php" class="btn btn-secondary ms-2">Annuler</a></div>
    </form>
    
    <script>
        function calculerTotal() {
            let total = 0;
            document.querySelectorAll('.produit-row').forEach(row => {
                const qte = row.querySelector('.quantite').value || 0;
                const prix = row.querySelector('.prix-unitaire').value || 0;
                total += qte * prix;
            });
            const tva = total * 0.18;
            const ttc = total + tva;
            document.getElementById('total-ht').innerHTML = total.toLocaleString() + ' FCFA';
            document.getElementById('total-tva').innerHTML = tva.toLocaleString() + ' FCFA';
            document.getElementById('total-ttc').innerHTML = '<strong>' + ttc.toLocaleString() + ' FCFA</strong>';
        }
        
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('produit-select')) {
                const prix = e.target.selectedOptions[0]?.getAttribute('data-prix') || 0;
                const stock = e.target.selectedOptions[0]?.getAttribute('data-stock') || 0;
                const row = e.target.closest('.produit-row');
                row.querySelector('.prix-unitaire').value = prix;
                calculerTotal();
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantite') || e.target.classList.contains('prix-unitaire')) calculerTotal();
        });
        
        document.getElementById('add-produit').addEventListener('click', function() {
            const container = document.getElementById('produits-container');
            const newRow = container.querySelector('.produit-row').cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(input => { if (input.type === 'number') input.value = ''; else if (input.tagName === 'SELECT') input.selectedIndex = 0; });
            container.appendChild(newRow);
            calculerTotal();
        });
        
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-produit')) {
                const rows = document.querySelectorAll('.produit-row');
                if (rows.length > 1) e.target.closest('.produit-row').remove();
                else alert('Au moins un produit est requis');
                calculerTotal();
            }
        });
    </script>
    <?php
} else {
    $ventes = $pdo->query("SELECT v.*, c.nom as client_nom, c.prenom as client_prenom FROM ventes v LEFT JOIN clients c ON v.client_id = c.id ORDER BY v.date_vente DESC")->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-cart me-2"></i>Gestion des ventes</h2>
        <a href="ventes.php?action=nouvelle" class="btn btn-success"><i class="fas fa-plus me-1"></i>Nouvelle vente</a>
    </div>
    
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert"><?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    
    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>N° vente</th><th>Client</th><th>Date</th><th>Total HT</th><th>Total TTC</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($ventes as $v): ?>
        <tr><td><code><?= escape($v['numero_vente']) ?></code></td><td><?= escape($v['client_nom'] . ' ' . ($v['client_prenom'] ?? '')) ?></td><td><?= formatDateTime($v['date_vente']) ?></td><td><?= formatMoney($v['total_ht']) ?></td><td><?= formatMoney($v['total_ttc']) ?></td><td><span class="badge bg-success"><?= escape($v['statut']) ?></span></td><td><a href="vente_detail.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td></tr>
        <?php endforeach; ?>
    </tbody></table></div></div></div>
    <?php
}

require_once 'footer.php';
?>
