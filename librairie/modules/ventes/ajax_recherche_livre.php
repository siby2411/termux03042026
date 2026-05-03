<?php
require_once '../../includes/config.php';

if (!isCaissier()) {
    exit('Accès non autorisé');
}

$search = $_POST['search'] ?? '';
if (strlen($search) < 2) {
    exit('');
}

$stmt = $pdo->prepare("
    SELECT id, titre, auteur, isbn, prix_vente, quantite_stock 
    FROM livres 
    WHERE (titre LIKE ? OR auteur LIKE ? OR isbn LIKE ?)
    AND quantite_stock > 0
    AND statut = 'disponible'
    LIMIT 10
");
$search_param = "%$search%";
$stmt->execute([$search_param, $search_param, $search_param]);
$livres = $stmt->fetchAll();

if (empty($livres)) {
    echo '<div class="search-result-item text-muted">Aucun livre trouvé</div>';
} else {
    foreach ($livres as $livre) {
        echo '<div class="search-result-item" onclick="ajouterAuPanier(' . $livre['id'] . ', \'' . addslashes($livre['titre']) . '\', ' . $livre['prix_vente'] . ', ' . $livre['quantite_stock'] . ')">';
        echo '<div class="row">';
        echo '<div class="col-7">';
        echo '<strong>' . htmlspecialchars($livre['titre']) . '</strong><br>';
        echo '<small class="text-muted">Auteur: ' . htmlspecialchars($livre['auteur']) . '</small>';
        echo '</div>';
        echo '<div class="col-3 text-right">';
        echo '<span class="badge bg-success">' . number_format($livre['prix_vente'], 0, ',', ' ') . ' FCFA</span>';
        echo '</div>';
        echo '<div class="col-2 text-right">';
        echo '<span class="badge bg-info">Stock: ' . $livre['quantite_stock'] . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
?>
