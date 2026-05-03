<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Récupération des écoles
$ecoles = $db->query("SELECT id_ecole, nom_ecole, adresse_ecole, horaire_matin, horaire_soir FROM ecoles")->fetchAll();

// Modification des horaires
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ecole = $_POST['id_ecole'];
    $horaire_matin = $_POST['horaire_matin'];
    $horaire_soir = $_POST['horaire_soir'];
    
    $stmt = $db->prepare("UPDATE ecoles SET horaire_matin = ?, horaire_soir = ? WHERE id_ecole = ?");
    if($stmt->execute([$horaire_matin, $horaire_soir, $id_ecole])) {
        $message = '<div class="alert alert-success">Horaires mis à jour avec succès</div>';
        header('Refresh:2');
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de la mise à jour</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des horaires - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-clock"></i> Gestion des horaires</h2>
            <hr>
        </div>
    </div>
    
    <?php echo $message; ?>
    
    <div class="row">
        <!-- Formulaire modification horaires -->
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-edit"></i> Modifier les horaires des écoles</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>École *</label>
                            <select name="id_ecole" id="id_ecole" class="form-control" required onchange="chargerHoraires()">
                                <option value="">Sélectionner une école</option>
                                <?php foreach($ecoles as $ecole): ?>
                                <option value="<?php echo $ecole['id_ecole']; ?>" 
                                        data-matin="<?php echo $ecole['horaire_matin']; ?>"
                                        data-soir="<?php echo $ecole['horaire_soir']; ?>">
                                    <?php echo htmlspecialchars($ecole['nom_ecole']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Horaire matin (départ domicile → école)</label>
                            <input type="time" name="horaire_matin" id="horaire_matin" class="form-control" required>
                            <small class="text-muted">Heure de prise en charge à domicile</small>
                        </div>
                        
                        <div class="mb-3">
                            <label>Horaire soir (départ école → domicile)</label>
                            <input type="time" name="horaire_soir" id="horaire_soir" class="form-control" required>
                            <small class="text-muted">Heure de départ de l'école</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save"></i> Enregistrer les horaires
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Aperçu calendrier -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-calendar-alt"></i> Aperçu des horaires</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des horaires -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5><i class="fas fa-list"></i> Liste des horaires par école</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>École</th><th>Horaire matin</th><th>Horaire soir</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($ecoles as $ecole): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ecole['nom_ecole']); ?></td>
                                    <td><i class="fas fa-sun"></i> <?php echo $ecole['horaire_matin']; ?></td>
                                    <td><i class="fas fa-moon"></i> <?php echo $ecole['horaire_soir']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="modifierEcole(<?php echo $ecole['id_ecole']; ?>, '<?php echo $ecole['horaire_matin']; ?>', '<?php echo $ecole['horaire_soir']; ?>')">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },
        events: [
            <?php foreach($ecoles as $ecole): ?>
            {
                title: 'Matin - <?php echo addslashes($ecole['nom_ecole']); ?>',
                start: '2025-04-16T<?php echo $ecole['horaire_matin']; ?>:00',
                end: '2025-04-16T<?php echo date('H:i', strtotime($ecole['horaire_matin'] . ' +30 minutes')); ?>:00',
                backgroundColor: '#2196F3',
                extendedProps: { ecole: '<?php echo addslashes($ecole['nom_ecole']); ?>' }
            },
            {
                title: 'Soir - <?php echo addslashes($ecole['nom_ecole']); ?>',
                start: '2025-04-16T<?php echo $ecole['horaire_soir']; ?>:00',
                end: '2025-04-16T<?php echo date('H:i', strtotime($ecole['horaire_soir'] . ' +30 minutes')); ?>:00',
                backgroundColor: '#FF9800',
                extendedProps: { ecole: '<?php echo addslashes($ecole['nom_ecole']); ?>' }
            },
            <?php endforeach; ?>
        ]
    });
    calendar.render();
});

function chargerHoraires() {
    var select = document.getElementById('id_ecole');
    var option = select.options[select.selectedIndex];
    document.getElementById('horaire_matin').value = option.getAttribute('data-matin');
    document.getElementById('horaire_soir').value = option.getAttribute('data-soir');
}

function modifierEcole(id, matin, soir) {
    document.getElementById('id_ecole').value = id;
    document.getElementById('horaire_matin').value = matin;
    document.getElementById('horaire_soir').value = soir;
    chargerHoraires();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
