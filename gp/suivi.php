<?php require_once 'header.php'; ?>
<div class="card">
    <h2><i class="fas fa-search"></i> Recherche avancée</h2>
    <form method="get" action="suivi.php" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <input type="text" name="q_colis" placeholder="Numéro de colis">
        <input type="text" name="q_client" placeholder="Nom du client">
        <input type="text" name="q_produit" placeholder="Produit">
        <button type="submit" class="btn"><i class="fas fa-search"></i> Rechercher</button>
    </form>
</div>

<?php
$results = [];
if (isset($_GET['q_colis']) && !empty($_GET['q_colis'])) {
    $rech = '%'.$_GET['q_colis'].'%';
    $stmt = $pdo->prepare("SELECT c.*, e.nom as expediteur, d.nom as destinataire FROM colis c LEFT JOIN clients e ON c.client_expediteur_id = e.id LEFT JOIN clients d ON c.client_destinataire_id = d.id WHERE c.numero_suivi LIKE ?");
    $stmt->execute([$rech]);
    $results = $stmt->fetchAll();
} elseif (isset($_GET['q_client']) && !empty($_GET['q_client'])) {
    $rech = '%'.$_GET['q_client'].'%';
    $stmt = $pdo->prepare("SELECT c.*, e.nom as expediteur, d.nom as destinataire FROM colis c LEFT JOIN clients e ON c.client_expediteur_id = e.id LEFT JOIN clients d ON c.client_destinataire_id = d.id WHERE e.nom LIKE ? OR d.nom LIKE ?");
    $stmt->execute([$rech, $rech]);
    $results = $stmt->fetchAll();
} elseif (isset($_GET['q_produit']) && !empty($_GET['q_produit'])) {
    $rech = '%'.$_GET['q_produit'].'%';
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ?");
    $stmt->execute([$rech, $rech]);
    $products = $stmt->fetchAll();
    // On affiche les produits trouvés
    echo "<h3>Produits trouvés</h3><div class='produits-grid' style='display:flex;flex-wrap:wrap;'>";
    foreach ($products as $p) {
        echo "<div class='card' style='width:200px'><img src='{$p['image']}' width='100'><h4>{$p['nom']}</h4><p>{$p['description']}</p><strong>{$p['prix']} €</strong><br>Stock: {$p['stock']}</div>";
    }
    echo "</div>";
    $results = []; // pas de colis
}

if (!empty($results)): ?>
<h3>Résultats des colis</h3>
<table border="1" cellpadding="5">
    <tr><th>N° suivi</th><th>Expéditeur</th><th>Destinataire</th><th>Statut</th><th>Action</th></tr>
    <?php foreach ($results as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['numero_suivi']) ?></td>
        <td><?= htmlspecialchars($r['expediteur']) ?></td>
        <td><?= htmlspecialchars($r['destinataire']) ?></td>
        <td><?= $r['statut'] ?></td>
        <td><a href="suivi_carte.php?numero=<?= $r['numero_suivi'] ?>"><i class="fas fa-map"></i> Voir trajet</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php elseif (!isset($_GET['q_produit']) && !empty($_GET)): ?>
<p>Aucun résultat.</p>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
