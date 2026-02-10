<?php
// /var/www/syscoa/pages/operations.php
$pdo = get_db_connection();

// Obtenir les données récentes
$data = get_syscohada_data('operations', ['limit' => 50]);
?>

<div class="row">
    <div class="col-md-12">
        <h2>Opérations Comptables</h2>
        
        <!-- Tableau des opérations -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dernières opérations</h5>
            </div>
            <div class="card-body">
                <?php if (isset($data['error'])): ?>
                    <div class="alert alert-danger"><?php echo $data['error']; ?></div>
                <?php elseif (empty($data)): ?>
                    <div class="alert alert-info">Aucune opération trouvée.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N° Écriture</th>
                                    <th>Journal</th>
                                    <th>Compte</th>
                                    <th>Libellé</th>
                                    <th>Tiers</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                    <th>Exercice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['date_ecriture']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ecriture_id']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($row['journal_code'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($row['compte_num']); ?></code>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['libelle']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nom_tiers'] ?? ''); ?></td>
                                    <td class="text-end">
                                        <?php if ($row['debit'] > 0): ?>
                                            <span class="text-danger">
                                                <?php echo number_format($row['debit'], 2, ',', ' '); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($row['credit'] > 0): ?>
                                            <span class="text-success">
                                                <?php echo number_format($row['credit'], 2, ',', ' '); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($row['annee'] ?? ''); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Lien vers phpMyAdmin -->
                    <div class="mt-3">
                        <a href="http://192.168.1.33:8080/phpmyadmin/index.php?route=/sql&db=sysco_ohada&sql=SELECT * FROM vue_module_comptabilite ORDER BY date_ecriture DESC LIMIT 0, 25" 
                           target="_blank" class="btn btn-sm btn-outline-secondary">
                           <i class="fas fa-external-link-alt"></i> Voir dans phpMyAdmin
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
