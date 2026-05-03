<?php
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $tel = $_POST['telephone'];
    $ville = $_POST['ville'];

    $stmt = $pdo->prepare("INSERT INTO clients (nom, email, telephone, ville) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nom, $email, $tel, $ville])) {
        echo "<div class='alert alert-success'>Client ajouté ! <a href='clients.php'>Retour à la liste</a></div>";
    }
}
?>
<div class="card shadow-sm p-4">
    <h3><i class="fas fa-user-plus text-primary"></i> Ajouter un nouveau client</h3>
    <form method="POST">
        <div class="mb-3"><label>Nom / Raison Sociale</label><input type="text" name="nom" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Téléphone</label><input type="text" name="telephone" class="form-control"></div>
        <div class="mb-3"><label>Ville</label><input type="text" name="ville" class="form-control"></div>
        <button type="submit" class="btn btn-primary">Enregistrer le client</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
