<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Générer une facture
if(isset($_GET['commande_id'])) {
    $commande_id = $_GET['commande_id'];
    
    $query = $conn->prepare("
        SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.email, cl.adresse 
        FROM commandes c 
        LEFT JOIN clients cl ON c.client_id = cl.id 
        WHERE c.id = ?
    ");
    $query->execute([$commande_id]);
    $commande = $query->fetch();
    
    $query_articles = $conn->prepare("
        SELECT ca.*, p.nom as plat_nom 
        FROM commande_articles ca 
        LEFT JOIN plats p ON ca.plat_id = p.id 
        WHERE ca.commande_id = ?
    ");
    $query_articles->execute([$commande_id]);
    $articles = $query_articles->fetchAll();
}
?>

<?php if(isset($commande)): ?>
<div class="card">
    <h2>🧾 Facture #<?= $commande['id'] ?></h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3>La Bonne Cuisine</h3>
            <p>123 Rue du Restaurant<br>
            75001 Paris<br>
            Tél: 01 23 45 67 89<br>
            SIRET: 123 456 789 00012</p>
        </div>
        <div>
            <h3>Client</h3>
            <p><?= $commande['prenom'] . ' ' . $commande['nom'] ?><br>
            <?= $commande['adresse'] ?><br>
            Tél: <?= $commande['telephone'] ?><br>
            Email: <?= $commande['email'] ?></p>
        </div>
    </div>
    
    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <strong>Commande #<?= $commande['id'] ?></strong><br>
        Date: <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?><br>
        Type: <?= match($commande['type_commande']) {
            'sur_place' => 'Sur place',
            'a_emporter' => 'À emporter',
            'livraison' => 'Livraison'
        } ?><br>
        Statut: <?= str_replace('_', ' ', $commande['statut']) ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Plat</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($articles as $article): ?>
            <tr>
                <td><?= $article['plat_nom'] ?>
                    <?php if($article['instructions_speciales']): ?>
                        <br><small><em><?= $article['instructions_speciales'] ?></em></small>
                    <?php endif; ?>
                </td>
                <td><?= $article['quantite'] ?></td>
                <td><?= number_format($article['prix_unitaire'], 2, ',', ' ') ?> €</td>
                <td><?= number_format($article['sous_total'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total HT:</td>
                <td><?= number_format($commande['total_ht'], 2, ',', ' ') ?> €</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TVA (10%):</td>
                <td><?= number_format($commande['tva'], 2, ',', ' ') ?> €</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total TTC:</td>
                <td><strong><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="margin-top: 2rem; padding: 1rem; background: #e8f5e8; border-radius: 8px;">
        <strong>Mode de paiement:</strong> <?= ucfirst($commande['mode_paiement']) ?><br>
        <strong>Statut paiement:</strong> <?= str_replace('_', ' ', $commande['statut_paiement']) ?>
    </div>
    
    <div style="margin-top: 2rem; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimer la facture</button>
        <a href="factures.php" class="btn">📋 Retour aux factures</a>
    </div>
</div>
<?php else: ?>
<div class="card">
    <h2>🧾 Factures</h2>
    <table>
        <thead>
            <tr>
                <th>ID Commande</th>
                <th>Client</th>
                <th>Date</th>
                <th>Type</th>
                <th>Total TTC</th>
                <th>Statut Paiement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = $conn->query("
                SELECT c.*, cl.nom, cl.prenom 
                FROM commandes c 
                LEFT JOIN clients cl ON c.client_id = cl.id 
                ORDER BY c.date_commande DESC
            ");
            while($commande = $query->fetch(PDO::FETCH_ASSOC)): 
            ?>
            <tr>
                <td><strong>#<?= $commande['id'] ?></strong></td>
                <td><?= $commande['prenom'] . ' ' . $commande['nom'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                <td>
                    <?= match($commande['type_commande']) {
                        'sur_place' => '🍽️',
                        'a_emporter' => '🥡',
                        'livraison' => '🚗'
                    } ?>
                </td>
                <td><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</td>
                <td>
                    <span class="badge <?= $commande['statut_paiement'] == 'paye' ? 'badge-success' : 'badge-warning' ?>">
                        <?= str_replace('_', ' ', $commande['statut_paiement']) ?>
                    </span>
                </td>
                <td>
                    <a href="factures.php?commande_id=<?= $commande['id'] ?>" class="btn btn-primary">🧾 Facture</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<style>
@media print {
    .header, .btn {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

