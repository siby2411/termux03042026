<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if(isset($_POST['add_immo'])) {
    $stmt = $db->prepare("INSERT INTO immobilisations (nom_materiel, cout_acquisition, duree_vie_ans, date_achat) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['nom'], $_POST['cout'], $_POST['duree'], $_POST['date']]);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">Ajouter Immobilisation / Charge</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3"><label>Désignation</label><input type="text" name="nom" class="form-control" required></div>
                    <div class="mb-3"><label>Coût d'acquisition (FCFA)</label><input type="number" name="cout" class="form-control" required></div>
                    <div class="mb-3"><label>Durée amortissement (ans)</label><input type="number" name="duree" class="form-control" value="5"></div>
                    <div class="mb-3"><label>Date d'achat</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <button name="add_immo" class="btn btn-primary w-100">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Liste des Immobilisations</div>
            <table class="table align-middle">
                <thead><tr><th>Nom</th><th>Coût</th><th>Mensualité</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $list = $db->query("SELECT * FROM immobilisations ORDER BY date_achat DESC");
                    while($i = $list->fetch()) {
                        $mensuel = $i['cout_acquisition'] / ($i['duree_vie_ans'] * 12);
                        echo "<tr><td>{$i['nom_materiel']}</td><td>" . number_format($i['cout_acquisition'], 0, ',', ' ') . " F</td><td>" . number_format($mensuel, 0, ',', ' ') . " F/mois</td><td><button class='btn btn-sm btn-danger'><i class='fas fa-trash'></i></button></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
