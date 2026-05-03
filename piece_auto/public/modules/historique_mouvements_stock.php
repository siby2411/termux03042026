<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Historique des Mouvements";
include '../../includes/header.php';

$query = "SELECT m.*, p.nom_piece, p.reference 
          FROM MOUVEMENTS_STOCK m 
          JOIN PIECES p ON m.id_piece = p.id_piece 
          ORDER BY m.date_mouvement DESC LIMIT 100";
$stmt = $db->query($query);
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <span><i class="fas fa-history me-2"></i> Flux de Stock (100 derniers)</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-light">Exporter PDF</button>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date/Heure</th>
                    <th>Article</th>
                    <th>Type</th>
                    <th class="text-center">Quantité</th>
                    <th class="text-center">Avant</th>
                    <th class="text-center">Après</th>
                    <th class="text-end">Valeur Unit.</th>
                </tr>
            </thead>
            <tbody>
                <?php while($m = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    $badge = ($m['type_mouvement'] == 'Vente') ? 'bg-danger' : (($m['type_mouvement'] == 'Achat') ? 'bg-success' : 'bg-info');
                ?>
                <tr>
                    <td class="small"><?= date('d/m/Y H:i', strtotime($m['date_mouvement'])) ?></td>
                    <td>
                        <div class="fw-bold"><?= $row['nom_piece'] ?? $m['nom_piece'] ?></div>
                        <small class="text-muted"><?= $m['reference'] ?></small>
                    </td>
                    <td><span class="badge <?= $badge ?>"><?= $m['type_mouvement'] ?></span></td>
                    <td class="text-center fw-bold"><?= $m['quantite_impact'] ?></td>
                    <td class="text-center text-muted"><?= $m['stock_avant_mouvement'] ?></td>
                    <td class="text-center fw-bold text-primary"><?= $m['stock_apres_mouvement'] ?></td>
                    <td class="text-end"><?= number_format($m['prix_unitaire'], 0, ',', ' ') ?> F</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
