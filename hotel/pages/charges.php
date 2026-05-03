<?php
$message = "";

// 1. Enregistrement d'une nouvelle charge
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_charge'])) {
    try {
        $sql = "INSERT INTO charges (libelle, montant, categorie, date_charge) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['libelle'], $_POST['montant'], $_POST['categorie'], $_POST['date']]);
        $message = "<div class='alert alert-success shadow-sm'>📉 Charge enregistrée avec succès !</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// 2. Récupération des charges du mois en cours
$current_month = date('m');
$current_year = date('Y');
$charges = $pdo->prepare("SELECT * FROM charges WHERE MONTH(date_charge) = ? AND YEAR(date_charge) = ? ORDER BY date_charge DESC");
$charges->execute([$current_month, $current_year]);
$liste_charges = $charges->fetchAll();

// 3. Total par catégorie pour le petit graphique
$totals = $pdo->prepare("SELECT categorie, SUM(montant) as total FROM charges WHERE MONTH(date_charge) = ? GROUP BY categorie");
$totals->execute([$current_month]);
$stats_categories = $totals->fetchAll();
?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white fw-bold">
                <i class="bi bi-receipt"></i> Nouvelle Facture / Dépense
            </div>
            <div class="card-body">
                <?= $message ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Libellé de la dépense</label>
                        <input type="text" name="libelle" class="form-control" placeholder="ex: Facture Senelec Mars" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Catégorie</label>
                            <select name="categorie" class="form-select" required>
                                <option value="Electricité">SENELEC</option>
                                <option value="Eau">Sen'Eau</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Autre">Autre Frais</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Montant (FCFA)</label>
                            <input type="number" name="montant" class="form-control fw-bold text-danger" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Date de facturation</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <button type="submit" name="ajouter_charge" class="btn btn-danger w-100 fw-bold shadow-sm py-2">
                        <i class="bi bi-plus-circle"></i> Enregistrer la dépense
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3 small text-muted text-uppercase">Répartition du mois</h6>
                <?php foreach($stats_categories as $stat): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span><?= $stat['categorie'] ?></span>
                            <span class="fw-bold"><?= number_format($stat['total'], 0, ',', ' ') ?> F</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-list-check text-muted"></i> Journal des charges (Ce mois)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Date</th>
                                <th>Libellé</th>
                                <th>Catégorie</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($liste_charges as $c): ?>
                            <tr>
                                <td class="small"><?= date('d/m', strtotime($c['date_charge'])) ?></td>
                                <td class="fw-bold"><?= $c['libelle'] ?></td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($c['categorie'] == 'Electricité') $badge = 'bg-warning text-dark';
                                        if($c['categorie'] == 'Eau') $badge = 'bg-info text-white';
                                        if($c['categorie'] == 'Maintenance') $badge = 'bg-primary';
                                    ?>
                                    <span class="badge <?= $badge ?> small" style="font-size: 0.7rem;"><?= $c['categorie'] ?></span>
                                </td>
                                <td class="fw-bold text-danger text-end"><?= number_format($c['montant'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($liste_charges)) echo "<tr><td colspan='4' class='text-center p-3 text-muted'>Aucune dépense ce mois-ci.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
