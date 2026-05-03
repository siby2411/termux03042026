<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Grand Livre - OMEGA CONSULTING";
include "layout.php";

$compte_filter = $_GET['compte'] ?? '';
$where = $compte_filter ? "WHERE compte_debite_id = :cpte OR compte_credite_id = :cpte" : "";

$sql = "SELECT * FROM ECRITURES_COMPTABLES $where ORDER BY date_ecriture ASC";
$stmt = $pdo->prepare($sql);
if($compte_filter) $stmt->bindValue(':cpte', $compte_filter);
$stmt->execute();
$ecritures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-centered">
    <div class="card omega-card border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filtrer par compte (ex: 521)</label>
                    <input type="number" name="compte" class="form-control" value="<?= htmlspecialchars($compte_filter) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-omega w-100">Filtrer</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Libellé</th>
                            <th>Débit</th>
                            <th>Crédit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_debit = 0; $total_credit = 0;
                        foreach($ecritures as $e): 
                            $d = ($compte_filter == $e['compte_debite_id'] || !$compte_filter) ? $e['montant'] : 0;
                            $c = ($compte_filter == $e['compte_credite_id'] || !$compte_filter) ? $e['montant'] : 0;
                            $total_debit += $d; $total_credit += $c;
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($e['date_ecriture'])) ?></td>
                            <td><span class="badge bg-light text-dark"><?= $e['reference_piece'] ?></span></td>
                            <td><?= htmlspecialchars($e['libelle']) ?></td>
                            <td class="text-end"><?= number_format($d, 0, ',', ' ') ?></td>
                            <td class="text-end text-danger"><?= number_format($c, 0, ',', ' ') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">TOTAL :</td>
                            <td class="text-end text-primary"><?= number_format($total_debit, 0, ',', ' ') ?> F</td>
                            <td class="text-end text-danger"><?= number_format($total_credit, 0, ',', ' ') ?> F</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>
