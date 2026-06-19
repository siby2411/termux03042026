<?php 
include 'header_ecole.php';
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Traitement ajout
if(isset($_POST['add_creneau'])) {
    $stmt = $conn->prepare("INSERT INTO emploi_temps (affectation_id, jour, heure_debut, heure_fin, salle) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $_POST['affectation_id'], $_POST['jour'], $_POST['debut'], $_POST['fin'], $_POST['salle']);
    $stmt->execute();
}
?>
<div class="container mt-4">
    <h2 class="text-primary fw-bold mb-4">Planification Académique (Verrouillée)</h2>
    
    <div class="card p-4 shadow-sm mb-4">
        <form method="post" class="row g-3">
            <div class="col-md-4">
                <label>Matière autorisée par Classe :</label>
                <select name="affectation_id" class="form-select" required>
                    <option value="">Sélectionner un cours du programme</option>
                    <?php 
                    $sql = "SELECT a.id, p.nom, u.nom_uv, c.nom_class 
                            FROM affectations a 
                            JOIN professeurs p ON a.prof_id = p.id_prof 
                            JOIN uvs u ON a.uv_id = u.id 
                            JOIN classes c ON a.classe_id = c.id
                            JOIN programme_classe pc ON (pc.classe_id = c.id AND pc.uv_id = u.id)";
                    $aff = $conn->query($sql);
                    while($row = $aff->fetch_assoc()) echo "<option value='".$row['id']."'>[".$row['nom_class']."] ".$row['nom_uv']." - ".$row['nom']." </option>";
                    ?>
                </select>
            </div>
            <div class="col-md-2"><label>Jour :</label><input type="text" name="jour" class="form-control" placeholder="Lundi" required></div>
            <div class="col-md-2"><label>Début :</label><input type="time" name="debut" class="form-control" required></div>
            <div class="col-md-2"><label>Fin :</label><input type="time" name="fin" class="form-control" required></div>
            <div class="col-md-2 align-self-end"><button type="submit" name="add_creneau" class="btn btn-success w-100">Planifier</button></div>
        </form>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
