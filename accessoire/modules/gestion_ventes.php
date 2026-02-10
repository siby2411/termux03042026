<?php
// /var/www/piece_auto/modules/gestion_ventes.php

include '../config/Database.php';
include '../includes/header.php';
$page_title = "Gestion des Ventes et Transactions";

$database = new Database();
$db = $database->getConnection(); 
$message_status = "";

// --- 1. LOGIQUE : GESTION DE LA VENTE (Transaction Simple) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_sale') {
    
    $db->beginTransaction(); // Démarre la transaction
    try {
        $id_client = (int)$_POST['id_client'];
        $id_piece = (int)$_POST['id_piece'];
        $quantite_vendue = (int)$_POST['quantite'];
        
        // 1. Vérification du stock
        $stock_query = "SELECT quantite_dispo FROM STOCK WHERE id_piece = :idp FOR UPDATE";
        $stock_stmt = $db->prepare($stock_query);
        $stock_stmt->execute([':idp' => $id_piece]);
        $stock = $stock_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stock || $stock['quantite_dispo'] < $quantite_vendue) {
            throw new Exception("Stock insuffisant. Disponible: " . ($stock['quantite_dispo'] ?? 0));
        }

        // 2. Récupérer le prix de vente de la pièce
        $price_query = "SELECT prix_vente FROM PIECES WHERE id_piece = :idp";
        $price_stmt = $db->prepare($price_query);
        $price_stmt->execute([':idp' => $id_piece]);
        $piece_price = $price_stmt->fetchColumn();
        
        $total_ht = $piece_price * $quantite_vendue;
        $total_ttc = $total_ht * 1.20; // TVA à 20%
        
        // 3. Enregistrer la VENTE
        $vente_query = "INSERT INTO VENTES (id_client, total_ht, total_ttc, statut) VALUES (:idc, :ht, :ttc, 'Payée')";
        $vente_stmt = $db->prepare($vente_query);
        $vente_stmt->execute([':idc' => $id_client, ':ht' => $total_ht, ':ttc' => $total_ttc]);
        $id_vente = $db->lastInsertId();

        // 4. Enregistrer la LIGNE_VENTE
        $ligne_query = "INSERT INTO LIGNES_VENTE (id_vente, id_piece, quantite, prix_unitaire_vendu) VALUES (:idv, :idp, :qte, :prix)";
        $ligne_stmt = $db->prepare($ligne_query);
        $ligne_stmt->execute([':idv' => $id_vente, ':idp' => $id_piece, ':qte' => $quantite_vendue, ':prix' => $piece_price]);

        // 5. Décrémenter le STOCK
        $update_stock_query = "UPDATE STOCK SET quantite_dispo = quantite_dispo - :qte WHERE id_piece = :idp";
        $update_stock_stmt = $db->prepare($update_stock_query);
        $update_stock_stmt->execute([':qte' => $quantite_vendue, ':idp' => $id_piece]);

        $db->commit(); // Validation de la transaction
        $message_status = "<div class='alert alert-success'>Vente N°$id_vente enregistrée (Total TTC: " . number_format($total_ttc, 2, ',', ' ') . " €). Stock mis à jour.</div>";

    } catch (Exception $e) {
        $db->rollBack(); // Annulation en cas d'erreur
        $message_status = "<div class='alert alert-danger'>Erreur lors de la vente : " . $e->getMessage() . "</div>";
    }
}

// --- 2. LOGIQUE : RÉCUPÉRATION DES DONNÉES DE BASE POUR LE FORMULAIRE ---
$clients = $db->query("SELECT id_client, nom, prenom FROM CLIENTS ORDER BY nom LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
$pieces = $db->query("SELECT P.id_piece, P.nom_piece, P.reference_sku, COALESCE(S.quantite_dispo, 0) AS stock FROM PIECES P LEFT JOIN STOCK S ON P.id_piece = S.id_piece ORDER BY P.nom_piece")->fetchAll(PDO::FETCH_ASSOC);

// --- 3. LOGIQUE : RÉCUPÉRATION DES DERNIÈRES VENTES ---
$ventes = $db->query("SELECT V.*, C.nom, C.prenom FROM VENTES V LEFT JOIN CLIENTS C ON V.id_client = C.id_client ORDER BY V.date_vente DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Enregistrement de Vente Rapide</h2>
        <a href="#formulaire_vente" class="btn btn-success mb-4" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="formulaire_vente">
            <i class="fas fa-cash-register"></i> Nouvelle Transaction
        </a>
        
        <?= $message_status ?>

        <div class="collapse mb-5" id="formulaire_vente">
            <div class="card p-4">
                <h4 class="card-title mb-4">Vente Unitaire (Test)</h4>
                <form method="POST" action="gestion_ventes.php">
                    <input type="hidden" name="action" value="add_sale">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_client" class="form-label">Client</label>
                            <select id="id_client" name="id_client" class="form-select" required>
                                <option value="">Choisir le Client...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id_client'] ?>"><?= $client['nom'] . ' ' . $client['prenom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="id_piece" class="form-label">Pièce Vendue</label>
                            <select id="id_piece" name="id_piece" class="form-select" required>
                                <option value="">Choisir la Pièce...</option>
                                <?php foreach ($pieces as $piece): ?>
                                    <option value="<?= $piece['id_piece'] ?>">
                                        <?= $piece['nom_piece'] ?> (Réf: <?= $piece['reference_sku'] ?> | Stock: <?= $piece['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="quantite" class="form-label">Quantité</label>
                            <input type="number" min="1" class="form-control" id="quantite" name="quantite" value="1" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-dollar-sign"></i> Finaliser la Vente</button>
                </form>
                <small class="text-muted mt-2">Ce formulaire enregistre une seule pièce à la fois et met le stock à jour.</small>
            </div>
        </div>

        <div class="card p-4">
            <h4 class="card-title mb-4">Historique des 10 Dernières Ventes</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Vente</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Total TTC</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventes)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune vente enregistrée.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ventes as $v): ?>
                                <tr>
                                    <td class="fw-bold"><?= $v['id_vente'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?></td>
                                    <td><?= $v['nom'] . ' ' . $v['prenom'] ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($v['total_ttc'], 2, ',', ' ') ?> €</td>
                                    <td><span class="badge bg-success"><?= $v['statut'] ?></span></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary" title="Voir Détails"><i class="fas fa-file-alt"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
