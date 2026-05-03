<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer tous les cours
$query = "SELECT c.*, d.nom as discipline, d.tarif_mensuel, 
          CONCAT(f.prenom,' ',f.nom) as formateur, f.specialite
          FROM cours c 
          JOIN disciplines d ON c.discipline_id=d.id 
          JOIN formateurs f ON c.formateur_id=f.id 
          WHERE c.actif=1 
          ORDER BY FIELD(c.jour, 'LUNDI','MARDI','MERCREDI','JEUDI','VENDREDI','SAMEDI'), c.heure_debut";
$cours = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

$jours = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI'];
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt"></i> Calendrier des Formations - Arts Martiaux & Fitness</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-dark text-white">
                        <tr><th>Horaire</th><?php foreach($jours as $jour): ?><th><?= ucfirst(strtolower($jour)) ?></th><?php endforeach; ?></tr>
                    </thead>
                    <tbody>
                        <?php
                        $horaires = ['06:00', '08:00', '10:00', '14:00', '16:00', '18:00', '19:00', '20:00'];
                        foreach($horaires as $horaire):
                        ?>
                        <tr>
                            <td class="bg-light"><strong><?= $horaire ?></strong></td>
                            <?php foreach($jours as $jour):
                                $cours_trouve = null;
                                foreach($cours as $c) {
                                    if($c['jour'] == $jour && date('H:i', strtotime($c['heure_debut'])) <= $horaire && date('H:i', strtotime($c['heure_fin'])) > $horaire) {
                                        $cours_trouve = $c;
                                        break;
                                    }
                                }
                            ?>
                            <td style="background: <?= $cours_trouve ? 'linear-gradient(135deg, #667eea, #764ba2)' : '#f8f9fa' ?>">
                                <?php if($cours_trouve): ?>
                                    <div class="p-2">
                                        <strong><?= htmlspecialchars($cours_trouve['discipline']) ?></strong><br>
                                        <small><?= htmlspecialchars($cours_trouve['formateur']) ?></small><br>
                                        <small class="text-white-50"><?= date('H:i', strtotime($cours_trouve['heure_debut'])) ?> - <?= date('H:i', strtotime($cours_trouve['heure_fin'])) ?></small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
