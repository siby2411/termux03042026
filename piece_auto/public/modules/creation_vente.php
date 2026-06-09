<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = (int)$_POST['id_client'];
    $id_piece = (int)$_POST['id_piece'];
    $quantite = (int)$_POST['quantite'];
    $prix = (float)$_POST['prix'];

    $db->beginTransaction();
    try {
        // 1. Créer la commande (En-tête)
        $stmt = $db->prepare("INSERT INTO COMMANDE_VENTE (date_commande, id_client) VALUES (NOW(), ?)");
        $stmt->execute([$id_client]);
        $id_commande = $db->lastInsertId();

        // 2. Ajouter la ligne de vente (Détails)
        $stmt = $db->prepare("INSERT INTO LIGNES_VENTE (id_commande, id_piece, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_commande, $id_piece, $quantite, $prix]);

        // 3. Décrémenter le stock et créer un mouvement
        $update = $db->prepare("UPDATE PIECES SET stock_actuel = stock_actuel - ? WHERE id_piece = ?");
        $update->execute([$quantite, $id_piece]);
        
        $mouvement = $db->prepare("INSERT INTO MOUVEMENTS_STOCK (id_piece, type_mouvement, quantite, date_mouvement) VALUES (?, 'SORTIE', ?, NOW())");
        $mouvement->execute([$id_piece, $quantite]);

        $db->commit();
        $message = '<div class="alert alert-success">Vente enregistrée et stock mis à jour !</div>';
    } catch (Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
    }
}

// Données pour les listes déroulantes
$clients = $db->query("SELECT id_client, nom FROM CLIENTS")->fetchAll();
$pieces = $db->query("SELECT id_piece, nom_piece, prix_vente, stock_actuel FROM PIECES WHERE stock_actuel > 0")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 col-md-6 mx-auto">
        <div class="card-header bg-white py-3"><h5><i class="fas fa-shopping-cart"></i> Nouvelle Vente</h5></div>
        <div class="card-body">
            <?= $message ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Client</label>
                    <select name="id_client" class="form-control" required>
                        <?php foreach($clients as $c): ?><option value="<?= $c['id_client'] ?>"><?= $c['nom'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pièce</label>
                    <select name="id_piece" class="form-control" required>
                        <?php foreach($pieces as $p): ?>
                            <option value="<?= $p['id_piece'] ?>"><?= $p['nom_piece'] ?> (Stock: <?= $p['stock_actuel'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prix unitaire</label>
                    <input type="number" name="prix" class="form-control" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantité</label>
                    <input type="number" name="quantite" class="form-control" min="1" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Confirmer la Vente</button>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
