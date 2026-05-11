<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Vérification synchronisation Grand Livre";
$page_icon = "check-circle";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Statistiques de synchronisation
$stats = [
    'total_ecritures' => $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES")->fetchColumn(),
    'avec_journal' => $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES WHERE journal_id IS NOT NULL")->fetchColumn(),
    'avec_section' => $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES WHERE section_analytique_id IS NOT NULL")->fetchColumn(),
    'lettrees' => $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES WHERE lettrage_id IS NOT NULL")->fetchColumn(),
    'avec_modele' => $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES WHERE modele_id IS NOT NULL")->fetchColumn()
];

// Dernières écritures avec tous les liens
$dernieres = $pdo->query("
    SELECT e.*, 
           j.code as journal_code, j.libelle as journal_libelle,
           s.code as section_code, s.libelle as section_libelle,
           m.code as modele_code, m.libelle as modele_libelle
    FROM ECRITURES_COMPTABLES e
    LEFT JOIN JOURNAUX j ON e.journal_id = j.id
    LEFT JOIN SECTIONS_ANALYTIQUES s ON e.section_analytique_id = s.id
    LEFT JOIN MODELES_SAISIE m ON e.modele_id = m.id
    ORDER BY e.id DESC
    LIMIT 10
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-check-circle"></i> Vérification synchronisation</h5>
                <small>Intégration Multi-journaux, Analytique, Modèles, Lettrage</small>
            </div>
            <div class="card-body">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h4><?= $stats['total_ecritures'] ?></h4>
                                <small>Total écritures</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= $stats['avec_journal'] ?></h4>
                                <small>Liées à un journal</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= $stats['avec_section'] ?></h4>
                                <small>Liées à une section</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= $stats['lettrees'] ?></h4>
                                <small>Écritures lettrées</small>
                            </div>
                        </div>
                    </div>
                </div>

                <h6>📋 Dernières écritures avec liens</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th><th>Libellé</th><th>Journal</th><th>Section analytique</th>
                                <th>Modèle</th><th>Lettrée</th><th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dernieres as $e): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($e['date_ecriture'])) ?> </td>
                                <td><?= htmlspecialchars(substr($e['libelle'], 0, 40)) ?>... </td>
                                <td class="text-center">
                                    <?php if($e['journal_code']): ?>
                                        <span class="badge bg-primary"><?= $e['journal_code'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non affecté</span>
                                    <?php endif; ?>
                                 </td>
                                 <td class="text-center">
                                    <?php if($e['section_code']): ?>
                                        <span class="badge bg-info"><?= $e['section_code'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                 </td>
                                 <td class="text-center">
                                    <?php if($e['modele_code']): ?>
                                        <span class="badge bg-success"><?= $e['modele_code'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                 </td>
                                 <td class="text-center">
                                    <?= $e['lettrage_id'] ? '✅' : '❌' ?>
                                 </td>
                                <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
