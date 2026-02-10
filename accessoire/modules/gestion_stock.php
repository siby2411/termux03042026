<?php
// /var/www/piece_auto/modules/gestion_stock.php
include_once '../config/Database.php';
include '../includes/header.php'; // Inclut le début HTML et définit $user_role et $username

// --- Initialisation des variables de rôle ---
// Les variables $user_role et $username sont définies dans includes/header.php, 
// nous nous assurons qu'elles sont disponibles ici pour la vérification.
$user_role = $user_role ?? ($_SESSION['user_role'] ?? 'Guest');


$page_title = "Inventaire et Gestion des Stocks";
$database = new Database();
$db = $database->getConnection();
$message_status = "";

// Vérification de l'accès (Admin ou Stockeur) - C'est ici que l'exécution s'arrête si non autorisé
if ($user_role != 'Admin' && $user_role != 'Stockeur') {
    echo "<div class='alert alert-danger'>Accès refusé. Seuls les Administrateurs et Stockeurs peuvent gérer les stocks.</div>";
    include '../includes/footer.php';
    exit;
}

// --- LOGIQUE D'AJUSTEMENT DE STOCK (Si nécessaire) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'adjust_stock') {
    $id_piece = (int)($_POST['id_piece'] ?? 0);
    $ajustement = (int)($_POST['ajustement'] ?? 0);
    $motif = trim($_POST['motif'] ?? 'Ajustement Manuel');

    if ($id_piece > 0 && $ajustement != 0) {
        $db->beginTransaction();
        try {
            // 1. Mettre à jour la table STOCK
            // CORRECTION: Utilisation de quantite_dispo
            $query_update = "
                INSERT INTO STOCK (id_piece, quantite_dispo) VALUES (:idp, :qte)
                ON DUPLICATE KEY UPDATE quantite_dispo = quantite_dispo + :qte_update
            ";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->execute([':idp' => $id_piece, ':qte' => $ajustement, ':qte_update' => $ajustement]);

            // 2. Enregistrer le Mouvement de Stock (STRATÉGIQUE)
            $query_log = "INSERT INTO MOUVEMENTS_STOCK (id_piece, date_mouvement, quantite_change, type_mouvement, reference_externe) 
                          VALUES (:id_p, NOW(), :qte, 'Ajustement', :motif)";
            $stmt_log = $db->prepare($query_log);
            $stmt_log->execute([
                ':id_p' => $id_piece, 
                ':qte' => $ajustement, 
                ':motif' => $motif
            ]);
            
            $db->commit();
            $message_status = "<div class='alert alert-success'>Stock mis à jour pour la pièce ID {$id_piece}. Ajustement de {$ajustement} unités.</div>";

        } catch (PDOException $e) {
            $db->rollBack();
            $message_status = "<div class='alert alert-danger'>Erreur d'ajustement de stock : " . $e->getMessage() . "</div>";
        }
    }
}


// --- RÉCUPÉRATION DES DONNÉES DE STOCK ---
$query_stock = "
    SELECT 
        P.id_piece, P.reference_sku, P.nom_piece, P.prix_achat,
        COALESCE(S.quantite_dispo, 0) AS quantite_stock, /* <-- CORRECTION SQL */
        MA.nom_marque, C.nom_categorie
    FROM PIECES P
    LEFT JOIN STOCK S ON P.id_piece = S.id_piece
    LEFT JOIN MARQUES_AUTO MA ON P.id_marque = MA.id_marque
    LEFT JOIN CATEGORIES C ON P.id_categorie = C.id_categorie
    ORDER BY P.nom_piece
";

// Exécution de la requête dans un bloc try-catch pour détecter les erreurs SQL silencieuses
try {
    $stmt_stock = $db->query($query_stock);
    $inventaire = $stmt_stock->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if ($user_role == 'Admin') {
           $message_status .= "<div class='alert alert-danger'>ERREUR SQL (GESTION STOCK): " . $e->getMessage() . "</div>";
    }
    $inventaire = []; 
}


function format_currency($amount) {
    return number_format($amount, 2, ',', ' ') . ' €';
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-boxes"></i> <?= $page_title ?></h2>
        
        <?= $message_status ?>

        <div class="card p-4 shadow">
            <h4 class="card-title mb-4">Inventaire Actuel (<?= count($inventaire) ?> références)</h4>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>Désignation</th>
                            <th>Marque</th>
                            <th>Catégorie</th>
                            <th class="text-end">Prix Achat U.</th>
                            <th class="text-end">Stock Actuel</th>
                            <th class="text-end">Valeur Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventaire)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Aucune pièce trouvée ou erreur de connexion à la base de données.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventaire as $piece): ?>
                            <tr>
                                <td><?= htmlspecialchars($piece['reference_sku']) ?></td>
                                <td><?= htmlspecialchars($piece['nom_piece']) ?></td>
                                <td><?= htmlspecialchars($piece['nom_marque'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($piece['nom_categorie'] ?? '-') ?></td>
                                <td class="text-end"><?= format_currency($piece['prix_achat']) ?></td>
                                <td class="text-end fw-bold">
                                    <span class="badge bg-<?= $piece['quantite_stock'] > 10 ? 'success' : 'danger' ?>">
                                        <?= number_format($piece['quantite_stock']) ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= format_currency($piece['quantite_stock'] * $piece['prix_achat']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info adjust-stock-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#adjustModal"
                                            data-id="<?= $piece['id_piece'] ?>"
                                            data-nom="<?= htmlspecialchars($piece['nom_piece']) ?>">
                                        Ajuster
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adjustModal" tabindex="-1" aria-labelledby="adjustModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="gestion_stock.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="adjustModalLabel">Ajustement de Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="adjust_stock">
                    <input type="hidden" name="id_piece" id="adjust_id_piece">
                    
                    <p>Ajustement pour la pièce : <strong id="adjust_piece_nom"></strong></p>

                    <div class="mb-3">
                        <label for="ajustement" class="form-label">Quantité à Ajouter / Retirer</label>
                        <input type="number" class="form-control" id="ajustement" name="ajustement" required>
                        <small class="form-text text-muted">Utilisez un nombre **positif** pour ajouter (entrée) ou **négatif** pour retirer (sortie). Ex: 5 ou -3.</small>
                    </div>
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif de l'ajustement</label>
                        <input type="text" class="form-control" id="motif" name="motif" required value="Inventaire/Casse">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info">Valider l'Ajustement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustModal = document.getElementById('adjustModal');
    if (adjustModal) {
        adjustModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id_piece = button.getAttribute('data-id');
            const nom_piece = button.getAttribute('data-nom');
            
            adjustModal.querySelector('#adjust_id_piece').value = id_piece;
            adjustModal.querySelector('#adjust_piece_nom').textContent = nom_piece;
            adjustModal.querySelector('#ajustement').value = ''; 
        });
    }
});
</script>

<?php 
include '../includes/footer.php'; 
?>
