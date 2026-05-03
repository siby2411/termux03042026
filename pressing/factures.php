<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Générer une facture
if(isset($_GET['generer_facture'])) {
    $commande_id = $_GET['generer_facture'];
    
    $query = $conn->prepare("
        SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.email, cl.adresse 
        FROM commandes c 
        LEFT JOIN clients cl ON c.client_id = cl.id 
        WHERE c.id = ?
    ");
    $query->execute([$commande_id]);
    $commande = $query->fetch();
    
    $query_articles = $conn->prepare("
        SELECT ca.*, s.nom as service_nom 
        FROM commande_articles ca 
        LEFT JOIN services s ON ca.service_id = s.id 
        WHERE ca.commande_id = ?
    ");
    $query_articles->execute([$commande_id]);
    $articles = $query_articles->fetchAll();
}
?>

<?php if(isset($commande)): ?>
<div class="card">
    <h2>Facture #<?= $commande['id'] ?></h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3>Pressing Pro</h3>
            <p>123 Rue du Commerce<br>
            75001 Paris<br>
            Tél: 01 23 45 67 89<br>
            Email: contact@pressingpro.fr</p>
        </div>
        <div>
            <h3>Client</h3>
            <p><?= $commande['prenom'] . ' ' . $commande['nom'] ?><br>
            <?= $commande['adresse'] ?><br>
            Tél: <?= $commande['telephone'] ?><br>
            Email: <?= $commande['email'] ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($articles as $article): ?>
            <tr>
                <td><?= $article['service_nom'] ?></td>
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
                <td colspan="3" style="text-align: right; font-weight: bold;">TVA (20%):</td>
                <td><?= number_format($commande['tva'], 2, ',', ' ') ?> €</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total TTC:</td>
                <td><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</td>
            </tr>
        </tfoot>
    </table>
    
    <div style="margin-top: 2rem; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Imprimer la facture</button>
        <a href="factures.php" class="btn">Retour aux factures</a>
    </div>
</div>
<?php else: ?>
<div class="card">
    <h2>Factures</h2>
    <table>
        <thead>
            <tr>
                <th>ID Commande</th>
                <th>Client</th>
                <th>Date</th>
                <th>Total TTC</th>
                <th>Statut</th>
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
                <td><?= $commande['id'] ?></td>
                <td><?= $commande['prenom'] . ' ' . $commande['nom'] ?></td>
                <td><?= date('d/m/Y', strtotime($commande['date_commande'])) ?></td>
                <td><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</td>
                <td>
                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; 
                        background: <?= 
                            $commande['statut'] == 'termine' ? '#27ae60' : 
                            ($commande['statut'] == 'en_cours' ? '#3498db' : 
                            ($commande['statut'] == 'recupere' ? '#95a5a6' : '#f39c12')) 
                        ?>; color: white;">
                        <?= str_replace('_', ' ', $commande['statut']) ?>
                    </span>
                </td>
                <td>
                    <a href="factures.php?generer_facture=<?= $commande['id'] ?>" class="btn btn-primary">Générer Facture</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
