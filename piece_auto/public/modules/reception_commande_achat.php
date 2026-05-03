<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../includes/auth_check.php';
// /var/www/piece_auto/public/modules/reception_commande_achat.php - (MISE À JOUR CUMP)

$page_title = "Réception de Commande d'Achat";
include '../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$commande_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$commande_id) {
    $message = '<div class="alert alert-danger">ID de commande manquant.</div>';
    $commande = null;
} else {
    // --- 1. Récupération des détails de la commande ---
    $query_commande = "SELECT ca.*, f.nom as nom_fournisseur, u.username as nom_utilisateur
                       FROM COMMANDES_ACHAT ca
                       JOIN FOURNISSEURS f ON ca.fournisseur_id = f.id_fournisseur
                       JOIN UTILISATEURS u ON ca.utilisateur_id = u.id_utilisateur
                       WHERE ca.id_commande_achat = :id";
    $stmt_commande = $db->prepare($query_commande);
    $stmt_commande->execute([':id' => $commande_id]);
    $commande = $stmt_commande->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        $message = '<div class="alert alert-danger">Commande non trouvée.</div>';
    }
}

// --- 2. GESTION DU POST (RÉCEPTION DE COMMANDE ET MISE À JOUR DU STOCK + CUMP) ---
if ($commande && $commande['statut'] == 'EN_COURS' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'receive') {
    try {
        $db->beginTransaction();
        
        // a) Mise à jour du statut de la commande
        $query_update_cmd = "UPDATE COMMANDES_ACHAT SET statut = 'REÇUE', date_reception = CURDATE() WHERE id_commande_achat = :id";
        $stmt_update_cmd = $db->prepare($query_update_cmd);
        $stmt_update_cmd->execute([':id' => $commande_id]);
        
        // b) Récupération des lignes de commande (y compris les anciennes valeurs de stock/CUMP)
        $query_lignes = "SELECT la.piece_id, la.quantite, la.prix_achat_unitaire, p.quantite_stock, p.cump_actuel
                         FROM LIGNES_ACHAT la
                         JOIN PIECES p ON la.piece_id = p.id_piece
                         WHERE la.commande_achat_id = :id";
        $stmt_lignes = $db->prepare($query_lignes);
        $stmt_lignes->execute([':id' => $commande_id]);
        $lignes = $stmt_lignes->fetchAll(PDO::FETCH_ASSOC);
        
        // Préparation des requêtes d'UPDATE et d'INSERT
        $query_update_piece = "UPDATE PIECES 
                               SET quantite_stock = quantite_stock + :quantite_entree, 
                                   cump_actuel = :nouveau_cump,
                                   valeur_stock_total = (:quantite_entree + quantite_stock) * :nouveau_cump
                               WHERE id_piece = :piece_id";
        $stmt_update_piece = $db->prepare($query_update_piece);

        $query_insert_history = "INSERT INTO HISTORIQUE_CUMP (piece_id, ancien_cump, nouveau_cump, quantite_entree, source_type, source_id) 
                                 VALUES (:piece_id, :ancien_cump, :nouveau_cump, :quantite_entree, 'ACHAT', :source_id)";
        $stmt_insert_history = $db->prepare($query_insert_history);


        foreach ($lignes as $ligne) {
            $piece_id = $ligne['piece_id'];
            $quantite_entree = $ligne['quantite'];
            $prix_achat = $ligne['prix_achat_unitaire'];
            $stock_ancien = (int)$ligne['quantite_stock'];
            $cump_ancien = (float)$ligne['cump_actuel'];

            // Calcul du nouveau CUMP
            $nouveau_stock_total = $stock_ancien + $quantite_entree;

            // Éviter la division par zéro (si le stock était à 0)
            if ($nouveau_stock_total > 0) {
                $valeur_stock_initiale = $stock_ancien * $cump_ancien;
                $valeur_entree = $quantite_entree * $prix_achat;
                
                $nouveau_cump = ($valeur_stock_initiale + $valeur_entree) / $nouveau_stock_total;
            } else {
                // Ce cas ne devrait pas arriver si $quantite_entree > 0
                $nouveau_cump = $prix_achat;
            }

            // Mise à jour de la table PIECES
            $stmt_update_piece->execute([
                ':quantite_entree' => $quantite_entree,
                ':nouveau_cump' => round($nouveau_cump, 2),
                ':piece_id' => $piece_id
            ]);

            // Enregistrement dans l'historique CUMP
            if (round($nouveau_cump, 2) != round($cump_ancien, 2)) {
                $stmt_insert_history->execute([
                    ':piece_id' => $piece_id,
                    ':ancien_cump' => $cump_ancien,
                    ':nouveau_cump' => round($nouveau_cump, 2),
                    ':quantite_entree' => $quantite_entree,
                    ':source_id' => $commande_id
                ]);
            }
        }
        
        $db->commit();
        $message = '<div class="alert alert-success">Commande N°' . $commande_id . ' réceptionnée avec succès. Le stock et le CUMP ont été mis à jour.</div>';
        
        // Rafraîchir l'objet commande après la mise à jour
        $stmt_commande->execute([':id' => $commande_id]);
        $commande = $stmt_commande->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Erreur lors de la réception : ' . $e->getMessage() . '</div>';
    }
}

// --- 3. Affichage du contenu ---
?>

<h1><i class="fas fa-truck-loading"></i> <?= $page_title ?></h1>
<p class="lead">Valider la réception de la marchandise et mettre à jour le stock.</p>
<hr>

<?= $message ?>

<?php if ($commande): ?>

    <h2>Commande N°<?= htmlspecialchars($commande['id_commande_achat']) ?> (Statut : <span class="badge bg-<?= $commande['statut'] == 'REÇUE' ? 'success' : 'warning' ?>"><?= htmlspecialchars($commande['statut']) ?></span>)</h2>
    <div class="row mb-4">
        <div class="col-md-6">
            <p><strong>Fournisseur:</strong> <?= htmlspecialchars($commande['nom_fournisseur']) ?></p>
            <p><strong>Date de Commande:</strong> <?= htmlspecialchars($commande['date_commande']) ?></p>
            <p><strong>Montant Total:</strong> <?= number_format($commande['montant_total'], 2) ?> €</p>
        </div>
        <div class="col-md-6">
            <p><strong>Créée par:</strong> <?= htmlspecialchars($commande['nom_utilisateur']) ?></p>
            <p><strong>Date de Réception:</strong> <?= $commande['date_reception'] ?? 'Non reçue' ?></p>
        </div>
    </div>

    <h3>Détails des Articles Commandés</h3>
    <?php
    $query_lignes = "SELECT la.quantite, la.prix_achat_unitaire, p.reference, p.nom, p.quantite_stock, p.cump_actuel 
                     FROM LIGNES_ACHAT la
                     JOIN PIECES p ON la.piece_id = p.id_piece
                     WHERE la.commande_achat_id = :id";
    $stmt_lignes = $db->prepare($query_lignes);
    $stmt_lignes->execute([':id' => $commande_id]);
    $lignes = $stmt_lignes->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Référence Pièce</th>
                <th>Nom Pièce</th>
                <th>Quantité Commandée</th>
                <th>Prix Achat Unitaire (€)</th>
                <th>Stock Actuel Avant Réception</th>
                <th>CUMP Actuel Avant Réception</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): ?>
                <tr>
                    <td><?= htmlspecialchars($ligne['reference']) ?></td>
                    <td><?= htmlspecialchars($ligne['nom']) ?></td>
                    <td><?= htmlspecialchars($ligne['quantite']) ?></td>
                    <td><?= number_format($ligne['prix_achat_unitaire'], 2) ?> €</td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($ligne['quantite_stock']) ?></span></td>
                    <td><span class="badge bg-info text-dark"><?= number_format(htmlspecialchars($ligne['cump_actuel']), 2) ?> €</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($commande['statut'] == 'EN_COURS'): ?>
        <form method="POST" onsubmit="return confirm('Confirmez-vous la réception complète de cette commande ? Le stock et le CUMP seront mis à jour.');">
            <input type="hidden" name="action" value="receive">
            <button type="submit" class="btn btn-success btn-lg mt-4">
                <i class="fas fa-check"></i> Valider la Réception et Mettre à Jour le Stock & CUMP
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-info mt-4">Cette commande a déjà été réceptionnée le <?= htmlspecialchars($commande['date_reception']) ?>.</div>
    <?php endif; ?>

<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
