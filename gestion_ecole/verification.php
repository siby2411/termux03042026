<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$id = intval($_GET['id'] ?? 0);

// 1. Récupération des infos étudiant
$query = "SELECT e.*, c.nom_class, c.montant_scolarite 
          FROM etudiants e 
          JOIN classes c ON e.classe_id = c.id WHERE e.id = $id";
$res = $conn->query($query);
$e = $res->fetch_assoc();

if (!$e) { die("Étudiant introuvable."); }

$code = $e['code_etudiant'];
$scolarite_mensuelle = $e['montant_scolarite'];

// 2. Calcul financier (Ex: Inscription en Octobre = 9 mois, Nov = 8 mois...)
$mois_inscription = (int)date('m', strtotime($e['date_inscription'] ?? '2025-10-01'));
$mois_actuel = 6; // Juin
$nb_mois_du = ($mois_actuel >= $mois_inscription) ? (9 - ($mois_inscription - 10)) : 9;
$total_du = $nb_mois_du * $scolarite_mensuelle;

// 3. Totaux versés
$versement_inscription = $conn->query("SELECT SUM(montant_verse) FROM paiements_inscription WHERE code_etudiant='$code'")->fetch_row()[0] ?? 0;
$versement_scolarite = $conn->query("SELECT SUM(montant_verse) FROM paiements_scolarite WHERE etudiant_id=$id")->fetch_row()[0] ?? 0;
$total_verse = $versement_inscription + $versement_scolarite;
$reste_a_payer = $total_du - $total_verse;

include 'header_ecole.php';
?>

<div class="container mt-4">
    <div class="p-4 mb-4 bg-dark text-white rounded shadow-sm text-center">
        <h2 class="text-warning">ESPACE PARENT & SUIVI ÉLÈVE</h2>
        <p class="mb-0"><?= $e['nom'].' '.$e['prenom'] ?> | Code: <?= $code ?></p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Traçabilité Financière</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Total Dû (<?= $nb_mois_du ?> mois)</td><td class="text-end fw-bold"><?= number_format($total_du, 0) ?> FCFA</td></tr>
                        <tr><td>Total Versé</td><td class="text-end text-success fw-bold"><?= number_format($total_verse, 0) ?> FCFA</td></tr>
                        <tr class="table-danger"><td>Reste à payer</td><td class="text-end fw-bold"><?= number_format($reste_a_payer, 0) ?> FCFA</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">Corps Enseignant</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php 
                        $profs = $conn->query("SELECT DISTINCT p.nom, p.prenom, p.telephone FROM affectations a JOIN professeurs p ON a.prof_id = p.id_prof WHERE a.classe_id = {$e['classe_id']}");
                        while($p = $profs->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?= $p['nom'].' '.$p['prenom'] ?> <span class="badge bg-info"><i class="bi bi-telephone"></i> <?= $p['telephone'] ?? 'N/A' ?></span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="bulletin.php?code_etudiant=<?= $code ?>" class="btn btn-warning btn-lg"><i class="bi bi-file-earmark-pdf"></i> Consulter le Bulletin Complet</a>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
