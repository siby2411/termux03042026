<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();

// Récupérer les produits (sans la colonne actif si elle n'existe pas)
try {
    $produits = $pdo->query("SELECT * FROM produits ORDER BY nom")->fetchAll();
} catch (PDOException $e) {
    $produits = $pdo->query("SELECT * FROM produits WHERE 1=1 ORDER BY nom")->fetchAll();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;
    $produits_ids = $_POST['produits'] ?? [];
    $quantites = $_POST['quantites'] ?? [];
    $prix = $_POST['prix'] ?? [];
    $mode_paiement = $_POST['mode_paiement'] ?? 'Espèces';
    
    if (empty($produits_ids)) {
        $error = "Aucun produit sélectionné";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Créer la vente
            $numero_vente = 'VNT-' . date('Ymd') . '-' . rand(1000, 9999);
            $total_ht = 0;
            
            foreach ($produits_ids as $key => $prod_id) {
                $qte = $quantites[$key] ?? 1;
                $prix_unitaire = $prix[$key] ?? 0;
                $total_ht += $prix_unitaire * $qte;
            }
            
            $tva = $total_ht * 0.18;
            $total_ttc = $total_ht + $tva;
            
            $stmt = $pdo->prepare("INSERT INTO ventes (numero_vente, client_id, date_vente, total_ht, tva, total_ttc, mode_paiement, statut) VALUES (?, ?, NOW(), ?, ?, ?, ?, 'confirmée')");
            $stmt->execute([$numero_vente, $client_id, $total_ht, $tva, $total_ttc, $mode_paiement]);
            $vente_id = $pdo->lastInsertId();
            
            // Insérer les lignes et mettre à jour le stock
            foreach ($produits_ids as $key => $prod_id) {
                $qte = $quantites[$key] ?? 1;
                $prix_unitaire = $prix[$key] ?? 0;
                $total_ligne = $prix_unitaire * $qte;
                
                $stmt = $pdo->prepare("INSERT INTO ventes_lignes (vente_id, produit_id, quantite, prix_unitaire, total_ht) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$vente_id, $prod_id, $qte, $prix_unitaire, $total_ligne]);
                
                // Mettre à jour le stock
                $stmt = $pdo->prepare("UPDATE produits SET stock_actuel = stock_actuel - ? WHERE id = ?");
                $stmt->execute([$qte, $prod_id]);
            }
            
            $pdo->commit();
            $success = "Vente enregistrée avec succès ! N°: $numero_vente";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

// Récupérer les clients
$clients = $pdo->query("SELECT * FROM clients ORDER BY nom")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cash-register me-2"></i>Point de vente (POS)</h2>
    <a href="ventes.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= escape($success) ?></div>
<?php endif; ?>

<form method="post" id="formVente">
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informations client</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Client (optionnel)</label>
                    <select name="client_id" class="form-control">
                        <option value="">-- Client anonyme --</option>
                        <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= escape($c['nom'] . ' ' . ($c['prenom'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Mode de paiement</label>
                    <select name="mode_paiement" class="form-control">
                        <option value="Espèces">Espèces</option>
                        <option value="Carte">Carte bancaire</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Chèque">Chèque</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Panier</h5>
        </div>
        <div class="card-body">
            <div id="produits-container">
                <div class="row produit-row mb-3">
                    <div class="col-md-5">
                        <label>Produit</label>
                        <select name="produits[]" class="form-control produit-select" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($produits as $p): ?>
                            <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix_vente'] ?>" data-stock="<?= $p['stock_actuel'] ?>">
                                <?= escape($p['nom']) ?> (<?= formatMoney($p['prix_vente']) ?>) - Stock: <?= $p['stock_actuel'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Quantité</label>
                        <input type="number" name="quantites[]" class="form-control quantite" value="1" min="1" step="1" required>
                    </div>
                    <div class="col-md-3">
                        <label>Prix unitaire (FCFA)</label>
                        <input type="number" name="prix[]" class="form-control prix-unitaire" step="100" required>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger remove-produit w-100" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" id="add-produit" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-plus me-1"></i>Ajouter un produit
            </button>
            
            <hr>
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered">
                        <tr><th>Total HT</th><td id="total-ht">0 FCFA</td></tr>
                        <tr><th>TVA (18%)</th><td id="total-tva">0 FCFA</td></tr>
                        <tr class="table-active"><th>Total TTC</th><td id="total-ttc"><strong>0 FCFA</strong></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Enregistrer la vente</button>
        <button type="reset" class="btn btn-secondary btn-lg">Réinitialiser</button>
    </div>
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
        row.querySelector('.quantite').max = stock;
        calculerTotal();
    }
});

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantite') || e.target.classList.contains('prix-unitaire')) {
        const row = e.target.closest('.produit-row');
        const qte = row.querySelector('.quantite').value || 0;
        const stock = row.querySelector('.produit-select').selectedOptions[0]?.getAttribute('data-stock') || 0;
        if (qte > stock) {
            alert('Stock insuffisant ! Stock disponible: ' + stock);
            row.querySelector('.quantite').value = stock;
        }
        calculerTotal();
    }
});

document.getElementById('add-produit').addEventListener('click', function() {
    const container = document.getElementById('produits-container');
    const newRow = container.querySelector('.produit-row').cloneNode(true);
    newRow.querySelectorAll('input, select').forEach(input => {
        if (input.type === 'number') input.value = '';
        else if (input.tagName === 'SELECT') input.selectedIndex = 0;
    });
    container.appendChild(newRow);
    calculerTotal();
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-produit')) {
        const rows = document.querySelectorAll('.produit-row');
        if (rows.length > 1) {
            e.target.closest('.produit-row').remove();
            calculerTotal();
        } else {
            alert('Au moins un produit est requis');
        }
    }
});

document.querySelector('button[type="reset"]').addEventListener('click', function() {
    setTimeout(calculerTotal, 100);
});
</script>
<?php require_once 'footer.php'; ?>
