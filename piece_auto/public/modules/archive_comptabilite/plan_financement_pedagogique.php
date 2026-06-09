<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Tableau de financement PCG";
include 'inc_navbar.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2>Tableau de financement (PCG) – version pédagogique</h2>
                </div>
                <div class="card-body">
                    <p>Le tableau de financement analyse les variations du bilan entre deux exercices. Il se divise en :</p>
                    <ul><li>Tableau I : emplois et ressources (CAF, cessions, augmentations de capital, emprunts…)</li>
                    <li>Tableau II : besoins et dégagements liés au BFR (stocks, clients, fournisseurs…)</li></ul>
                    <p>Variation du FRNG = Ressources – Emplois = Dégagements – Besoins.</p>
                    <h4>Exemple simplifié</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr><th>Ressources</th><td>CAF=350, Cessions=50, Augmentation capital=100 → Total 500</td></tr>
                            <tr><th>Emplois</th><td>Acquisitions=200, Remboursements=120, Dividendes=40 → Total 360</td></tr>
                            <tr><th>Variation FRNG</th><td>500 – 360 = +140 (ressource nette)</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
