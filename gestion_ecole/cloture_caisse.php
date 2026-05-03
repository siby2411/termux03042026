<?php
session_start();
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$aujourdhui = date('Y-m-d');

// Vérification du verrouillage
$check_lock = $conn->prepare("SELECT * FROM clotures_validees WHERE date_cloture = ?");
$check_lock->bind_param("s", $aujourdhui);
$check_lock->execute();
$is_locked = $check_lock->get_result()->num_rows > 0;

// Calcul des totaux
$sql_total = "SELECT SUM(montant_verse) as total FROM paiements_scolarite WHERE DATE(date_paiement) = ?";
$stmt_t = $conn->prepare($sql_total);
$stmt_t->bind_param("s", $aujourdhui);
$stmt_t->execute();
$total_jour = $stmt_t->get_result()->fetch_assoc()['total'] ?? 0;

include 'header_ecole.php';
?>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-safe2-fill text-warning me-2"></i> Clôture de Caisse Journalière</h2>
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer"></i> Rapport PDF</button>
            <a href="export_caisse.php" class="btn btn-success ms-2"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
        </div>
    </div>

    <?php if ($is_locked): ?>
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-lock-fill fs-1 me-3"></i>
            <div>
                <h4 class="alert-heading fw-bold mb-0">JOURNÉE VERROUILLÉE</h4>
                <p class="mb-0">Cette caisse a été signée. Aucune modification n'est plus possible pour la date du <?= date('d/m/Y') ?>.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-warning border-start border-5 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-warning mb-1">Caisse Ouverte</h5>
                    <p class="text-muted mb-0 small">Veuillez vérifier les montants avant de signer la clôture.</p>
                </div>
                <form action="valider_cloture.php" method="POST">
                    <input type="hidden" name="date_caisse" value="<?= $aujourdhui ?>">
                    <button type="submit" name="valider_caisse" class="btn btn-warning fw-bold px-4 shadow-sm" onclick="return confirm('Voulez-vous vraiment verrouiller la caisse ?')">
                        <i class="bi bi-pencil-square"></i> Signer la Clôture
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white p-4">
                <h6 class="opacity-75 text-uppercase small fw-bold">Recette Totale du Jour</h6>
                <h1 class="display-5 fw-bold mb-0"><?= number_format($total_jour, 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h1>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <h5 class="fw-bold mb-3">Ventilation par Mode de Paiement</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>MODE</th>
                                <th class="text-center">TRANSACTIONS</th>
                                <th class="text-end">CUMUL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT mode_paiement, SUM(montant_verse) as mt, COUNT(*) as nb FROM paiements_scolarite WHERE DATE(date_paiement) = '$aujourdhui' GROUP BY mode_paiement");
                            while($m = $res->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= $m['mode_paiement'] ?></td>
                                <td class="text-center"><?= $m['nb'] ?></td>
                                <td class="text-end fw-bold text-primary"><?= number_format($m['mt'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
