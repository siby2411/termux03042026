<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Récupération des étudiants pour le menu déroulant
$etudiants = $conn->query("SELECT code_etudiant, nom, prenom FROM etudiants WHERE code_etudiant IS NOT NULL");

include 'header_ecole.php';
?>
<div class="container mt-4">
    <h2 class="text-primary"><i class="bi bi-file-earmark-text"></i> Générateur de Bulletins</h2>
    <div class="card p-4 shadow-sm mt-3">
        <form method="GET" action="bulletin.php" class="row g-3 align-items-center">
            <div class="col-auto">
                <label class="form-label">Sélectionner un étudiant :</label>
            </div>
            <div class="col-auto">
                <select name="code_etudiant" class="form-select" required>
                    <option value="">Choisir un étudiant...</option>
                    <?php while($e = $etudiants->fetch_assoc()): ?>
                        <option value="<?= $e['code_etudiant'] ?>">
                            <?= $e['code_etudiant'] ?> - <?= $e['nom'] . ' ' . $e['prenom'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Générer le Bulletin</button>
            </div>
        </form>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
