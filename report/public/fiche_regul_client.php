<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$client_id = (int)($_GET['id'] ?? 0);
if (!$client_id) { header("Location: balance_clients.php"); exit(); }

$page_title = "Fiche de régularisation client";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupérer le client
$client = $pdo->prepare("SELECT * FROM TIERS WHERE id = ? AND type = 'CLIENT'");
$client->execute([$client_id]);
$c = $client->fetch();
if (!$c) { echo "<div class='alert alert-danger'>Client introuvable.</div>"; include 'inc_footer.php'; exit; }

// Toutes les écritures du client (débit = factures, crédit = règlements)
$ecritures = $pdo->prepare("
    SELECT date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece,
           CASE WHEN compte_debite_id = 411 THEN montant ELSE 0 END as debit,
           CASE WHEN compte_credite_id = 411 THEN montant ELSE 0 END as credit
    FROM ECRITURES_COMPTABLES
    WHERE compte_debite_id = 411 OR compte_credite_id = 411
    ORDER BY date_ecriture ASC
");
$ecritures->execute();
$mouvements = $ecritures->fetchAll();

$solde = 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Fiche de régularisation – <?= htmlspecialchars($c['raison_sociale']) ?></h5>
                <small>Détail des factures et règlements</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Référence</th><th>Libellé</th><th class="text-danger">Débit (facture)</th><th class="text-success">Crédit (règlement)</th><th class="text-primary">Solde après op.</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mouvements as $m): 
                                $debit = $m['debit'];
                                $credit = $m['credit'];
                                $solde += $debit - $credit;
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($m['date_ecriture'])) ?></td>
                                <td><?= htmlspecialchars($m['reference_piece'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($m['libelle']) ?></td>
                                <td class="text-end text-danger"><?= $debit ? number_format($debit,0,',',' ').' F' : '-' ?></td>
                                <td class="text-end text-success"><?= $credit ? number_format($credit,0,',',' ').' F' : '-' ?></td>
                                <td class="text-end fw-bold"><?= number_format($solde,0,',',' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="5" class="text-end">SOLDE FINAL :</td>
                                <td class="text-end"><?= number_format($solde,0,',',' ') ?> F</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="balance_agee_clients.php" class="btn btn-secondary">← Retour à la balance âgée</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
