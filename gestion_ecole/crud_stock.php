<?php include 'header_ecole.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i> Stock Consommables Informatiques</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStock"><i class="bi bi-plus-circle"></i> Nouvel Article</button>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Article</th>
                        <th>Catégorie</th>
                        <th>Quantité</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM stock ORDER BY article ASC");
                    while($row = $res->fetch_assoc()): 
                        $is_low = $row['quantite'] <= $row['seuil_alerte'];
                    ?>
                    <tr class="<?= $is_low ? 'table-warning' : '' ?>">
                        <td class="fw-bold"><?= $row['article'] ?></td>
                        <td><span class="badge bg-secondary"><?= $row['categorie'] ?></span></td>
                        <td><?= $row['quantite'] ?> unités</td>
                        <td>
                            <?php if($is_low): ?>
                                <span class="badge bg-danger text-white pulse-animation"><i class="bi bi-exclamation-triangle"></i> Réapprovisionner</span>
                            <?php else: ?>
                                <span class="badge bg-success">Correct</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
