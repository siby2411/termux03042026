<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Immobilisations - Gestion des actifs";
$page_icon = "building";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des immobilisations (comptes classe 2)
$sql = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0) as valeur_brute,
        COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id AND e.libelle LIKE '%amortissement%' THEN e.montant ELSE 0 END), 0) as amortissements
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON c.compte_id IN (e.compte_debite_id, e.compte_credite_id)
    WHERE c.compte_id BETWEEN 20 AND 29
    GROUP BY c.compte_id, c.intitule_compte
    ORDER BY c.compte_id
";
$stmt = $pdo->query($sql);
$immobilisations = $stmt->fetchAll();

$total_brut = 0;
$total_amort = 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Immobilisations (Classe 2 - Actif)</h5>
                <small class="text-muted">Norme SYSCOHADA - Comptes d'immobilisations corporelles et incorporelles</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>N° Compte</th>
                                <th>Intitulé</th>
                                <th>Valeur brute (FCFA)</th>
                                <th>Amortissements (FCFA)</th>
                                <th>Valeur nette (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($immobilisations) > 0): ?>
                                <?php foreach($immobilisations as $row): 
                                    $valeur_nette = $row['valeur_brute'] - $row['amortissements'];
                                    $total_brut += $row['valeur_brute'];
                                    $total_amort += $row['amortissements'];
                                ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $row['compte_id'] ?></td>
                                    <td><?= htmlspecialchars($row['intitule_compte']) ?></td>
                                    <td class="text-end"><?= number_format($row['valeur_brute'], 0, ',', ' ') ?></td>
                                    <td class="text-end text-danger"><?= number_format($row['amortissements'], 0, ',', ' ') ?></td>
                                    <td class="text-end fw-bold text-primary"><?= number_format($valeur_nette, 0, ',', ' ') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1"></i><br>
                                        Aucune immobilisation enregistrée
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(count($immobilisations) > 0): ?>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">TOTAUX :</td>
                                <td class="text-end"><?= number_format($total_brut, 0, ',', ' ') ?></td>
                                <td class="text-end text-danger"><?= number_format($total_amort, 0, ',', ' ') ?></td>
                                <td class="text-end text-primary"><?= number_format($total_brut - $total_amort, 0, ',', ' ') ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Informations SYSCOHADA -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6><i class="bi bi-info-circle"></i> Informations SYSCOHADA - Immobilisations</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Comptes d'immobilisations (Classe 2) :</strong>
                        <ul class="mt-2">
                            <li><strong>21</strong> - Immobilisations incorporelles</li>
                            <li><strong>22</strong> - Terrains bâtis/non bâtis</li>
                            <li><strong>23</strong> - Constructions</li>
                            <li><strong>24</strong> - Installations techniques</li>
                            <li><strong>25</strong> - Autres immobilisations corporelles</li>
                            <li><strong>28</strong> - Amortissements</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-primary">
                            <i class="bi bi-calculator-fill"></i>
                            <strong>Valeur nette comptable (VNC) :</strong>
                            <?= number_format($total_brut - $total_amort, 0, ',', ' ') ?> FCFA
                        </div>
                        <a href="amortissements.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-calculator"></i> Calculer amortissements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
