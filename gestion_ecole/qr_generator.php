<?php include 'header_ecole.php'; require_once 'db_connect_ecole.php'; $conn = db_connect_ecole(); ?>
<div class="container mt-4">
    <h2>Générateur de Cartes</h2>
    <table class="table">
        <?php $res = $conn->query("SELECT id, code_etudiant, nom, prenom FROM etudiants");
        while($e = $res->fetch_assoc()): ?>
        <tr><td><?= $e['code_etudiant'] ?></td><td><?= $e['nom'] ?> <?= $e['prenom'] ?></td>
        <td><a href="verification.php?id=<?= $e['id'] ?>" class="btn btn-info">Carte</a></td></tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include 'footer_ecole.php'; ?>
