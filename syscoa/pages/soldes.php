<?php
// /var/www/syscoa/pages/soldes.php
$pdo = get_db_connection();

// Récupérer l'exercice
$exercice = $_GET['exercice'] ?? 1;

// Obtenir les données
$stmt = $pdo->prepare("SELECT * FROM vue_soldes_comptables WHERE id_exercice = ? ORDER BY compte_num");
$stmt->execute([$exercice]);
$data = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <h2>État des Soldes</h2>
        
        <!-- Sélecteur d'exercice -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="page" value="soldes">
                    <div class="col-md-4">
                        <label for="exercice" class="form-label">Exercice comptable</label>
                        <select name="exercice" id="exercice" class="form-select" onchange="this.form.submit()">
                            <?php
                            $stmt = $pdo->query("SELECT * FROM exercices_comptables ORDER BY annee DESC");
                            $exercices = $stmt->fetchAll();
                            
                            foreach ($exercices as $exo) {
                                $selected = ($exo['id_exercice'] == $exercice) ? 'selected' : '';
                                echo "<option value='{$exo['id_exercice']}' $selected>{$exo['annee']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tableau des soldes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Soldes par compte</h5>
            </div>
            <div class="card-body">
                <?php if (empty($data)): ?>
                    <div class="alert alert-info">Aucun solde trouvé pour cet exercice.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>N° Compte</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Solde</th>
                                    <th>Type de solde</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_solde = 0;
                                $total_debiteurs = 0;
                                $total_crediteurs = 0;
                                
                                foreach ($data as $row): 
                                    $total_solde += $row['solde'];
                                    
                                    if ($row['type_solde'] == 'Débiteur') {
                                        $total_debiteurs += $row['solde'];
                                    } else {
                                        $total_crediteurs += abs($row['solde']);
                                    }
                                    
                                    $solde_class = ($row['solde'] > 0) ? 'text-success' : (($row['solde'] < 0) ? 'text-danger' : '');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['compte_num']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nom_compte']); ?></td>
                                    <td class="text-end <?php echo $solde_class; ?>">
                                        <?php echo number_format($row['solde'], 2, ',', ' '); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($row['type_solde'] == 'Débiteur') ? 'bg-warning' : 'bg-info'; ?>">
                                            <?php echo htmlspecialchars($row['type_solde']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="2" class="text-end"><strong>TOTAUX :</strong></td>
                                    <td class="text-end"><strong><?php echo number_format($total_solde, 2, ',', ' '); ?></strong></td>
                                    <td>
                                        <span class="badge bg-warning">Débiteurs: <?php echo number_format($total_debiteurs, 2, ',', ' '); ?></span>
                                        <span class="badge bg-info ms-2">Créditeurs: <?php echo number_format($total_crediteurs, 2, ',', ' '); ?></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Lien vers phpMyAdmin -->
                    <div class="mt-3">
                        <a href="http://192.168.1.33:8080/phpmyadmin/index.php?route=/sql&db=sysco_ohada&sql=SELECT * FROM vue_soldes_comptables WHERE id_exercice = <?php echo $exercice; ?> LIMIT 0, 25" 
                           target="_blank" class="btn btn-sm btn-outline-secondary">
                           <i class="fas fa-external-link-alt"></i> Voir dans phpMyAdmin
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
