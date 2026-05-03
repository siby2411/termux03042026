<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$page_title = "État des Stocks";
include '../../includes/header.php';

// On récupère les pièces
$pieces = $db->query("SELECT * FROM PIECES ORDER BY stock_actuel ASC")->fetchAll(PDO::FETCH_ASSOC);

// On récupère les flux récents (Correction ici : quantite_impact)
$flux = $db->query("SELECT m.*, p.nom_piece 
                   FROM MOUVEMENTS_STOCK m 
                   JOIN PIECES p ON m.id_piece = p.id_piece 
                   ORDER BY m.date_mouvement DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Stock Actuel</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th class="text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pieces as $p): ?>
                        <tr>
                            <td><code><?= $p['reference'] ?></code></td>
                            <td><?= $p['nom_piece'] ?></td>
                            <td class="text-center">
                                <span class="badge <?= $p['stock_actuel'] <= 5 ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $p['stock_actuel'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Flux Récents</div>
            <div class="list-group list-group-flush">
                <?php foreach($flux as $f): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <small class="fw-bold <?= $f['type_mouvement'] == 'Vente' ? 'text-danger' : 'text-success' ?>">
                            <?= strtoupper($f['type_mouvement']) ?>
                        </small>
                        <small class="text-muted"><?= date('d/m H:i', strtotime($f['date_mouvement'])) ?></small>
                    </div>
                    <div class="small"><?= $f['nom_piece'] ?></div>
                    <div class="small fw-bold">Quantité : <?= $f['quantite_impact'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
