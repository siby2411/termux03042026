<?php
$page_title = "Compte de Résultat";
require_once 'layout.php';
require_once '../config/database.php'; // $conn disponible

// --- Récupération des comptes de charges (classe 6) ---
$stmt_charges = $conn->prepare("SELECT compte_id, intitule_compte 
                                FROM PLAN_COMPTABLE_UEMOA 
                                WHERE classe = 6");
$stmt_charges->execute();
$charges = $stmt_charges->fetchAll();

// --- Récupération des comptes de produits (classe 7) ---
$stmt_produits = $conn->prepare("SELECT compte_id, intitule_compte 
                                 FROM PLAN_COMPTABLE_UEMOA 
                                 WHERE classe = 7");
$stmt_produits->execute();
$produits = $stmt_produits->fetchAll();

// --- Calcul résultat par compte (en joignant avec ECRITURES_COMPTABLES) ---
function getTotal($conn, $compte_id) {
    $stmt = $conn->prepare("
        SELECT 
            SUM(montant) as total
        FROM ECRITURES_COMPTABLES
        WHERE compte_debite_id = :compte OR compte_credite_id = :compte
    ");
    $stmt->execute(['compte' => $compte_id]);
    $row = $stmt->fetch();
    return $row['total'] ?? 0;
}
?>

<div class="container-fluid">
    <h3 class="mb-4">Compte de Résultat</h3>

    <div class="row">
        <!-- CHARGES -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-danger text-white">Charges (Classe 6)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Compte</th>
                                <th>Libellé</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($charges as $c): ?>
                            <tr>
                                <td><?= $c['compte_id'] ?></td>
                                <td><?= $c['intitule_compte'] ?></td>
                                <td><?= number_format(getTotal($conn, $c['compte_id']),2,',',' ') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PRODUITS -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Produits (Classe 7)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Compte</th>
                                <th>Libellé</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($produits as $p): ?>
                            <tr>
                                <td><?= $p['compte_id'] ?></td>
                                <td><?= $p['intitule_compte'] ?></td>
                                <td><?= number_format(getTotal($conn, $p['compte_id']),2,',',' ') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>





