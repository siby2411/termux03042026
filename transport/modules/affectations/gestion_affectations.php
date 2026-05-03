<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Récupération des listes
$bus = $db->query("SELECT id_bus, immatriculation, modele, capacite_max FROM bus WHERE statut_bus = 'operationnel'")->fetchAll();
$chauffeurs = $db->query("SELECT id_chauffeur, nom, prenom, telephone FROM chauffeurs WHERE statut_chauffeur = 'actif'")->fetchAll();
$eleves = $db->query("SELECT e.id_eleve, e.nom_eleve, e.prenom_eleve, e.classe, ec.nom_ecole 
                      FROM eleves e 
                      LEFT JOIN ecoles ec ON e.id_ecole = ec.id_ecole 
                      WHERE e.statut_inscription = 'valide' 
                      AND e.id_eleve NOT IN (SELECT id_eleve FROM affectations WHERE date_affectation = CURDATE())")->fetchAll();

// Traitement affectation
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_eleve = $_POST['id_eleve'];
    $id_bus = $_POST['id_bus'];
    $sens_trajet = $_POST['sens_trajet'];
    
    // Vérifier capacité bus
    $check = $db->prepare("SELECT COUNT(*) as count FROM affectations WHERE id_bus = ? AND sens_trajet = ? AND date_affectation = CURDATE()");
    $check->execute([$id_bus, $sens_trajet]);
    $count = $check->fetch(PDO::FETCH_ASSOC)['count'];
    
    if($count >= 20) {
        $message = '<div class="alert alert-danger">Ce bus a déjà atteint sa capacité maximale de 20 élèves pour ce trajet</div>';
    } else {
        $stmt = $db->prepare("INSERT INTO affectations (id_eleve, id_bus, sens_trajet, date_affectation) VALUES (?, ?, ?, CURDATE())");
        if($stmt->execute([$id_eleve, $id_bus, $sens_trajet])) {
            $message = '<div class="alert alert-success">Affectation réalisée avec succès</div>';
            header('Refresh:2');
        } else {
            $message = '<div class="alert alert-danger">Erreur lors de l\'affectation</div>';
        }
    }
}

// Liste des affectations du jour
$affectations = $db->query("
    SELECT a.id_affectation, a.sens_trajet, 
           e.nom_eleve, e.prenom_eleve, e.classe,
           b.immatriculation, b.modele,
           c.nom as chauffeur_nom, c.prenom as chauffeur_prenom
    FROM affectations a
    JOIN eleves e ON a.id_eleve = e.id_eleve
    JOIN bus b ON a.id_bus = b.id_bus
    LEFT JOIN itineraire i ON b.id_bus = i.id_bus
    LEFT JOIN chauffeurs c ON i.id_chauffeur = c.id_chauffeur
    WHERE a.date_affectation = CURDATE()
    ORDER BY a.sens_trajet, e.nom_eleve
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des affectations - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-exchange-alt"></i> Affectation bus / chauffeur / élève</h2>
            <hr>
        </div>
    </div>
    
    <?php echo $message; ?>
    
    <div class="row">
        <!-- Formulaire d'affectation -->
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-plus-circle"></i> Nouvelle affectation</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Élève *</label>
                            <select name="id_eleve" class="form-control" required>
                                <option value="">Sélectionner un élève</option>
                                <?php foreach($eleves as $eleve): ?>
                                <option value="<?php echo $eleve['id_eleve']; ?>">
                                    <?php echo htmlspecialchars($eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve'] . ' - ' . $eleve['classe'] . ' - ' . $eleve['nom_ecole']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Bus *</label>
                            <select name="id_bus" class="form-control" required>
                                <option value="">Sélectionner un bus</option>
                                <?php foreach($bus as $b): ?>
                                <option value="<?php echo $b['id_bus']; ?>">
                                    <?php echo $b['immatriculation'] . ' - ' . $b['modele'] . ' (Capacité: ' . $b['capacite_max'] . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Chauffeur *</label>
                            <select name="id_chauffeur" class="form-control" required>
                                <option value="">Sélectionner un chauffeur</option>
                                <?php foreach($chauffeurs as $c): ?>
                                <option value="<?php echo $c['id_chauffeur']; ?>">
                                    <?php echo htmlspecialchars($c['prenom'] . ' ' . $c['nom'] . ' - ' . $c['telephone']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Sens du trajet *</label>
                            <select name="sens_trajet" class="form-control" required>
                                <option value="matin_ecole">Matin (Domicile → École)</option>
                                <option value="soir_domicile">Soir (École → Domicile)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save"></i> Affecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Liste des affectations du jour -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-list"></i> Affectations du jour</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Élève</th><th>Bus</th><th>Chauffeur</th><th>Trajet</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($affectations as $aff): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($aff['prenom_eleve'] . ' ' . $aff['nom_eleve']); ?><br><small><?php echo $aff['classe']; ?></small></td>
                                    <td><?php echo $aff['immatriculation']; ?><br><small><?php echo $aff['modele']; ?></small></td>
                                    <td><?php echo htmlspecialchars($aff['chauffeur_prenom'] . ' ' . $aff['chauffeur_nom']); ?></td>
                                    <td><?php echo $aff['sens_trajet'] == 'matin_ecole' ? '🏫 Matin' : '🏠 Soir'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="supprimer(<?php echo $aff['id_affectation']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(count($affectations) == 0): ?>
                                <tr><td colspan="5" class="text-center">Aucune affectation pour aujourd'hui</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function supprimer(id) {
    if(confirm('Supprimer cette affectation ?')) {
        window.location.href = 'supprimer_affectation.php?id=' + id;
    }
}
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
