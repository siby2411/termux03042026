<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
include 'header_ecole.php';

$code = $_GET['code_etudiant'] ?? '';
$etu = $conn->query("SELECT * FROM etudiants WHERE code_etudiant = '$code'")->fetch_assoc();
$bul = $conn->query("SELECT * FROM bulletins WHERE code_etudiant = '$code'")->fetch_assoc();
?>

<style>
    .omega-header { background: linear-gradient(135deg, #1a2a6c, #000); color: white; padding: 25px; border-bottom: 5px solid #D4AF37; }
    .table-omega thead { background-color: #1a2a6c; color: #D4AF37; }
    @media print { .d-print-none { display: none; } }
</style>

<div class="container mt-5">
    <div class="omega-header text-center shadow rounded-top">
        <h2 class="fw-bold">OMEGA INFORMATIQUE CONSULTING</h2>
        <p class="mb-0 text-uppercase">Gestion École de Formation de Haute Qualité</p>
    </div>

    <div class="row my-4 align-items-center">
        <div class="col-md-8">
            <h5>Étudiant : <strong><?= $etu['nom'].' '.$etu['prenom'] ?? 'N/A' ?></strong></h5>
            <p class="mb-0">Code : <?= $code ?></p>
        </div>
        <div class="col-md-4 text-md-end">
            <p>Année Académique : <strong>2025-2026</strong></p>
            <button onclick="window.print()" class="btn btn-primary d-print-none"><i class="bi bi-printer"></i> Imprimer</button>
        </div>
    </div>

    <?php foreach([1, 2] as $sem): ?>
        <h5 class="mt-4 text-primary"><i class="bi bi-calendar-event"></i> Semestre <?= $sem ?></h5>
        <table class="table table-bordered table-omega table-hover">
            <thead>
                <tr class="text-center"><th>Matière</th><th>CC1</th><th>CC2</th><th>Examen</th><th>Moyenne</th></tr>
            </thead>
            <tbody>
                <?php 
                $res = $conn->query("SELECT m.nom_matiere, n.note_cc1, n.note_cc2, n.note_exam, n.moyenne_matiere 
                                     FROM notes n JOIN matieres m ON n.id_matiere = m.id 
                                     WHERE n.code_etudiant='$code' AND n.semestre=$sem");
                if ($res->num_rows > 0) {
                    while($n = $res->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td class="text-start"><?= $n['nom_matiere'] ?></td>
                        <td><?= number_format($n['note_cc1'], 2) ?></td>
                        <td><?= number_format($n['note_cc2'], 2) ?></td>
                        <td><?= number_format($n['note_exam'], 2) ?></td>
                        <td class="fw-bold text-primary"><?= number_format($n['moyenne_matiere'], 2) ?></td>
                    </tr>
                    <?php endwhile; 
                } else {
                    echo "<tr><td colspan='5' class='text-center'>Aucune note pour ce semestre</td></tr>";
                } ?>
            </tbody>
        </table>
        <div class="alert alert-secondary text-end">
            Moyenne Semestre <?= $sem ?> : <strong><?= number_format($bul['moyenne_semestre'.$sem] ?? 0, 2) ?></strong>
        </div>
    <?php endforeach; ?>

    <div class="mt-5 p-4 bg-dark text-white text-center rounded shadow">
        <h5>Moyenne Générale Annuelle</h5>
        <?php 
        $sem1 = $bul['moyenne_semestre1'] ?? 0;
        $sem2 = $bul['moyenne_semestre2'] ?? 0;
        $moy = ($sem2 > 0) ? (($sem1 + $sem2) / 2) : $sem1;
        echo '<h2 class="display-5 fw-bold text-warning">' . number_format($moy, 2) . '</h2>';
        ?>
    </div>

    <?php
    $mention = ($moy >= 16) ? "Très Bien" : (($moy >= 14) ? "Bien" : (($moy >= 12) ? "Assez Bien" : (($moy >= 10) ? "Passable" : "Ajourné")));
    ?>
    <div class="mt-4 p-3 bg-light border-top border-primary text-center shadow-sm">
        <h4 class="text-uppercase mb-0">Mention : <span class="text-primary fw-bold"><?= $mention ?></span></h4>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
