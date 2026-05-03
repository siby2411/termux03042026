<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$page_title = "Configuration UV - OMEGA";
include 'layout_ecole.php';
?>

<div class="form-centered">
    <div class="card omega-card">
        <div class="card-header bg-dark text-white py-3 text-center">
            <h4 class="mb-0">Attribution des UV par Classe</h4>
        </div>
        <div class="card-body p-4">
            <form action="insert_matiere_uv.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Classe Cible</label>
                    <select class="form-select" name="classe_id" required>
                        <?php
                        $res = $conn->query("SELECT id, nom_class FROM classes");
                        while($row = $res->fetch_assoc()) echo "<option value='".$row['id']."'>".$row['nom_class']."</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Semestre</label>
                    <select class="form-select" name="semestre" required>
                        <option value="1">Semestre 1</option>
                        <option value="2">Semestre 2</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Nom de la Matière</label>
                    <input type="text" class="form-control" name="nom_matiere" placeholder="Ex: Mathématiques" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Unité de Valeur (UV)</label>
                    <input type="text" class="form-control" name="uv" placeholder="Ex: Algèbre Linéaire" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Coefficient</label>
                    <input type="number" step="0.1" name="coefficient" class="form-control" value="1.0" required>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-omega shadow px-5">AJOUTER À LA CLASSE</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
