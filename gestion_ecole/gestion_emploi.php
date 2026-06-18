<?php 
include 'header_ecole.php';
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Traitement de l'ajout
if(isset($_POST['add_creneau'])) {
    $stmt = $conn->prepare("INSERT INTO emploi_temps (affectation_id, jour, heure_debut, heure_fin, salle) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $_POST['affectation_id'], $_POST['jour'], $_POST['debut'], $_POST['fin'], $_POST['salle']);
    $stmt->execute();
}
?>
<div class="container mt-4">
    <h2 class="text-primary fw-bold mb-4">Gestion de l'Emploi du Temps</h2>
    
    <div class="card p-4 shadow-sm mb-4">
        <form method="post" class="row g-3">
            <div class="col-md-3">
                <select name="affectation_id" class="form-select" required>
                    <option value="">Choisir Prof/Matière/Classe</option>
                    <?php 
                    $aff = $conn->query("SELECT a.id, p.nom, u.nom_uv, c.nom_class FROM affectations a JOIN professeurs p ON a.prof_id = p.id_prof JOIN uvs u ON a.uv_id = u.id JOIN classes c ON a.classe_id = c.id");
                    while($row = $aff->fetch_assoc()) echo "<option value='".$row['id']."'>".$row['nom']." - ".$row['nom_uv']." (".$row['nom_class'].")</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-2"><input type="text" name="jour" class="form-control" placeholder="Jour (ex: Lundi)" required></div>
            <div class="col-md-2"><input type="time" name="debut" class="form-control" required></div>
            <div class="col-md-2"><input type="time" name="fin" class="form-control" required></div>
            <div class="col-md-2"><input type="text" name="salle" class="form-control" placeholder="Salle" required></div>
            <div class="col-md-1"><button type="submit" name="add_creneau" class="btn btn-success w-100">Ajouter</button></div>
        </form>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
