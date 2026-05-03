<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();
$action = $_GET['action'] ?? 'liste';

if ($action == 'nouvelle') {
    $produits = $pdo->query("SELECT * FROM produits WHERE actif = 1 ORDER BY nom")->fetchAll();
    $clients = $pdo->query("SELECT * FROM clients WHERE actif = 1 ORDER BY nom")->fetchAll();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Traitement du formulaire...
        flash('Vente enregistrée', 'success');
        header('Location: ventes.php');
        exit;
    }
    ?>
    <div class="container mt-4">
        <h2>Nouvelle vente</h2>
        <a href="ventes.php" class="btn btn-secondary">Retour</a>
        
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>
        
        <form method="post" class="mt-3">
            <div class="card mb-3">
                <div class="card-header">Client</div>
                <div class="card-body">
                    <select name="client_id" class="form-control">
                        <option value="">-- Client anonyme --</option>
                        <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['nom'] . ' ' . ($c['prenom'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Produits</div>
                <div class="card-body">
                    <table class="table table-bordered" id="produits-table">
                        <thead>
                            <tr><th>Produit</th><th>Quantité</th><th>Prix</th><th>Total</th><th></th> </thead>
                        <tbody id="lignes-container">
                            <tr class="ligne-produit">
                                <td>
                                    <select name="produits[]" class="form-control" required>
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($produits as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix_vente'] ?>"><?= $p['nom'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" name="quantites[]" class="form-control quantite" value="1" min="1"></td>
                                <td><input type="number" name="prix[]" class="form-control prix" step="100"></td>
                                <td class="total-ligne">0</td>
                                <td><button type="button" class="btn btn-danger btn-sm supprimer">X</button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-end">Total HT:</td><td id="total-ht">0</td><td></td></tr>
                            <tr><td colspan="3" class="text-end">TVA 18%:</td><td id="total-tva">0</td><td></td></tr>
                            <tr><td colspan="3" class="text-end"><strong>Total TTC:</strong></td><td id="total-ttc"><strong>0</strong></td><td></td></tr>
                        </tfoot>
                     </table>
                    <button type="button" id="ajouter-ligne" class="btn btn-sm btn-primary">+ Ajouter produit</button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">Enregistrer la vente</button>
        </form>
    </div>
    
    <script>
        function calculerTotaux() {
            let totalHT = 0;
            document.querySelectorAll('.ligne-produit').forEach(row => {
                const qte = parseFloat(row.querySelector('.quantite').value) || 0;
                const prix = parseFloat(row.querySelector('.prix').value) || 0;
                const total = qte * prix;
                row.querySelector('.total-ligne').innerHTML = total.toLocaleString();
                totalHT += total;
            });
            const tva = totalHT * 0.18;
            const ttc = totalHT + tva;
            document.getElementById('total-ht').innerHTML = totalHT.toLocaleString();
            document.getElementById('total-tva').innerHTML = tva.toLocaleString();
            document.getElementById('total-ttc').innerHTML = ttc.toLocaleString();
        }
        
        document.getElementById('ajouter-ligne').addEventListener('click', function() {
            const container = document.getElementById('lignes-container');
            const newRow = container.querySelector('.ligne-produit').cloneNode(true);
            newRow.querySelector('.quantite').value = 1;
            newRow.querySelector('.prix').value = '';
            newRow.querySelector('.total-ligne').innerHTML = '0';
            container.appendChild(newRow);
            calculerTotaux();
        });
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('supprimer')) {
                const rows = document.querySelectorAll('.ligne-produit');
                if (rows.length > 1) {
                    e.target.closest('.ligne-produit').remove();
                    calculerTotaux();
                } else {
                    alert('Au moins un produit est requis');
                }
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('quantite') || e.target.classList.contains('prix')) {
                calculerTotaux();
            }
            if (e.target.tagName === 'SELECT') {
                const prix = e.target.selectedOptions[0].getAttribute('data-prix') || 0;
                const row = e.target.closest('.ligne-produit');
                row.querySelector('.prix').value = prix;
                calculerTotaux();
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantite') || e.target.classList.contains('prix')) {
                calculerTotaux();
            }
        });
    </script>
    <?php
} else {
    // Liste des ventes
    $ventes = $pdo->query("SELECT * FROM ventes ORDER BY date_vente DESC")->fetchAll();
    ?>
    <div class="container mt-4">
        <h2>Gestion des ventes</h2>
        <a href="ventes.php?action=nouvelle" class="btn btn-success">Nouvelle vente</a>
        <table class="table table-striped mt-3">
            <thead>
                <tr><th>N°</th><th>Date</th><th>Total TTC</th><th>Statut</th><th>Actions</th> </thead>
            <tbody>
                <?php foreach ($ventes as $v): ?>
                响应
                    <td><?= $v['numero_vente'] ?>响应
                    <td><?= $v['date_vente'] ?>响应
                    <td><?= number_format($v['total_ttc'], 0, ',', ' ') ?> FCFA响应
                    <td><?= $v['statut'] ?>响应
                    <td><a href="vente_detail.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-info">Détails</a>响应
                 ?>
                <?php endforeach; ?>
            </tbody>
         ?>
    </div>
    <?php
}
require_once 'footer.php';
?>
