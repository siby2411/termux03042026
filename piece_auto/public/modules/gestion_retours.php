<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$page_title = "Gestion des Retours Clients";
include '../../includes/header.php';

// On récupère les 20 dernières lignes de vente pour proposer un retour
$query = "SELECT lv.*, p.nom_piece, p.reference, cv.date_vente, c.nom as client_nom 
          FROM LIGNE_VENTE lv 
          JOIN PIECES p ON lv.id_piece = p.id_piece 
          JOIN COMMANDE_VENTE cv ON lv.id_commande_vente = cv.id_commande_vente
          JOIN CLIENTS c ON cv.id_client = c.id_client
          ORDER BY cv.date_vente DESC LIMIT 20";
$stmt = $db->query($query);
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-warning text-dark fw-bold">
        <i class="fas fa-undo me-2"></i> Effectuer un nouveau retour
    </div>
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Date Vente</th>
                    <th>Client</th>
                    <th>Article</th>
                    <th>Qté Vendue</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($l = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($l['date_vente'])) ?></td>
                    <td><?= $l['client_nom'] ?></td>
                    <td><b><?= $l['nom_piece'] ?></b></td>
                    <td><?= $l['quantite'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalRetour<?= $l['id_ligne_vente'] ?>">
                            <i class="fas fa-arrow-left"></i> Retourner
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="modalRetour<?= $l['id_ligne_vente'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <form action="traitement_retour.php" method="POST" class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Retourner : <?= $l['nom_piece'] ?></h5>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id_piece" value="<?= $l['id_piece'] ?>">
                        <input type="hidden" name="id_ligne_vente" value="<?= $l['id_ligne_vente'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Quantité à retourner (Max: <?= $l['quantite'] ?>)</label>
                            <input type="number" name="qte" class="form-control" max="<?= $l['quantite'] ?>" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">État de la pièce</label>
                            <select name="etat" class="form-select">
                                <option value="Réintégré">Réintégrer au Stock (Neuf)</option>
                                <option value="Défectueux">Défectueux (Sortie Définitive)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motif du retour</label>
                            <textarea name="motif" class="form-control" rows="2" placeholder="Ex: Erreur de référence..."></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Valider le retour</button>
                      </div>
                    </form>
                  </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
