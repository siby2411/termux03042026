<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Gestion des Immobilisations - OMEGA";
include "layout.php";

// 1. Traitement de l'ajout (Formulaire)
if(isset($_POST['add_immo'])){
    try {
        $stmt = $pdo->prepare("INSERT INTO immobilisations (code_immo, designation, date_acquisition, valeur_origine, duree_vie, vnc) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['code'], $_POST['label'], $_POST['date'], $_POST['valeur'], $_POST['duree'], $_POST['valeur']]);
        echo "<div class='alert alert-success form-centered mb-4 shadow-sm'>L'actif a été intégré au patrimoine avec succès.</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger form-centered mb-4'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// 2. Récupération sécurisée de la liste
try {
    $immos = $pdo->query("SELECT * FROM immobilisations ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $immos = [];
}
?>

<div class="form-centered">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark text-uppercase" style="letter-spacing: 2px;">Registre des Immobilisations</h2>
        <div class="bg-gold mx-auto" style="height: 4px; width: 60px; background-color: #c5a059;"></div>
    </div>

    <div class="card omega-card border-0 shadow-lg mb-5">
        <div class="card-header bg-white py-4 border-0">
            <h5 class="mb-0 fw-bold"><i class="bi bi-plus-circle-fill text-primary"></i> Acquisition d'un nouvel actif</h5>
        </div>
        <div class="card-body p-5">
            <form method="POST" class="row g-4">
                <div class="col-md-3">
                    <label class="form-label">Code Immo</label>
                    <input type="text" name="code" class="form-control bg-light" placeholder="EX: MAT-001" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Désignation (Nom de l'immobilisation)</label>
                    <input type="text" name="label" class="form-control bg-light" placeholder="Ex: Matériel Informatique DELL" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de mise en service</label>
                    <input type="date" name="date" class="form-control bg-light" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valeur d'entrée (F CFA)</label>
                    <input type="number" name="valeur" class="form-control bg-light fw-bold" placeholder="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Durée (Années)</label>
                    <input type="number" name="duree" class="form-control bg-light" placeholder="Ex: 5" required>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" name="add_immo" class="btn btn-omega px-5 py-3">
                        <i class="bi bi-shield-check"></i> ENREGISTRER L'ACTIF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card omega-card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead style="background-color: #00264d; color: white;">
                    <tr>
                        <th class="p-3">Code</th>
                        <th class="p-3">Désignation</th>
                        <th class="p-3">Acquisition</th>
                        <th class="p-3">Valeur Brute</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($immos)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Aucune immobilisation enregistrée.</td></tr>
                    <?php else: foreach($immos as $immo): ?>
                        <tr>
                            <td class="p-3 fw-bold"><?= $immo['code_immo'] ?></td>
                            <td class="p-3"><?= $immo['designation'] ?></td>
                            <td class="p-3"><?= date('d/m/Y', strtotime($immo['date_acquisition'])) ?></td>
                            <td class="p-3 fw-bold text-primary"><?= number_format($immo['valeur_origine'], 0, ',', ' ') ?> F</td>
                            <td class="p-3 text-center">
                                <button class="btn btn-sm btn-outline-dark rounded-pill">Détails</button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center py-5 text-muted small">
    © 2026 OMEGA Informatique CONSULTING - Standard SYSCOHADA UEMOA
</footer>
</body>
</html>
