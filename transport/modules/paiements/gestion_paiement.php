<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Récupération du code depuis l'URL
$parent_code = $_GET['parent_code'] ?? $_POST['parent_code'] ?? '';
$eleve_code = $_GET['eleve_code'] ?? $_POST['eleve_code'] ?? '';
$message = '';
$parent_info = null;
$eleves_list = [];

// Charger les informations du parent si code fourni
if(!empty($parent_code)) {
    $stmt = $db->prepare("
        SELECT p.*, COUNT(e.id_eleve) as nb_eleves
        FROM parents p
        LEFT JOIN eleves e ON p.id_parent = e.id_parent
        WHERE p.code_parent = ?
        GROUP BY p.id_parent
    ");
    $stmt->execute([$parent_code]);
    $parent_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($parent_info) {
        $stmt2 = $db->prepare("
            SELECT e.*, ec.nom_ecole, ec.horaire_matin, ec.horaire_soir,
                   (SELECT statut_paiement FROM paiements 
                    WHERE id_eleve = e.id_eleve AND mois_periode = DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                    LIMIT 1) as statut_mois_courant
            FROM eleves e
            LEFT JOIN ecoles ec ON e.id_ecole = ec.id_ecole
            WHERE e.id_parent = ?
        ");
        $stmt2->execute([$parent_info['id_parent']]);
        $eleves_list = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Charger les informations de l'élève si code fourni
if(!empty($eleve_code)) {
    $stmt = $db->prepare("
        SELECT e.*, p.nom as parent_nom, p.prenom as parent_prenom, p.telephone as parent_telephone, p.code_parent,
               ec.nom_ecole, ec.horaire_matin, ec.horaire_soir
        FROM eleves e
        JOIN parents p ON e.id_parent = p.id_parent
        LEFT JOIN ecoles ec ON e.id_ecole = ec.id_ecole
        WHERE e.code_eleve = ?
    ");
    $stmt->execute([$eleve_code]);
    $eleve_info = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement du paiement
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_paiement') {
    $id_eleve = intval($_POST['id_eleve']);
    $montant = floatval($_POST['montant']);
    $mois_periode = $_POST['mois_periode'];
    $mode_paiement = $_POST['mode_paiement'];
    $numero_telephone = $_POST['numero_telephone'] ?? null;
    
    try {
        // Vérifier si un paiement existe déjà pour ce mois
        $check = $db->prepare("SELECT id_paiement FROM paiements WHERE id_eleve = ? AND mois_periode = ?");
        $check->execute([$id_eleve, $mois_periode . '-01']);
        
        if($check->rowCount() > 0) {
            $message = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Un paiement existe déjà pour cet élève pour le mois de ' . date('F Y', strtotime($mois_periode . '-01')) . '</div>';
        } else {
            $db->beginTransaction();
            
            $query = "INSERT INTO paiements (id_eleve, montant, mois_periode, mode_paiement, statut_paiement, id_secretaire) 
                      VALUES (?, ?, ?, ?, 'paye', 1)";
            $stmt = $db->prepare($query);
            $stmt->execute([$id_eleve, $montant, $mois_periode . '-01', $mode_paiement]);
            
            $reference = 'OMEGA_' . date('YmdHis') . '_' . $id_eleve;
            
            $db->commit();
            $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Paiement enregistré avec succès. Réf: ' . $reference . '</div>';
        }
    } catch(Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Erreur: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des paiements - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .page-header { background: linear-gradient(135deg, #003366 0%, #006699 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
        .payment-card { border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: transform 0.3s; margin-bottom: 20px; }
        .payment-card:hover { transform: translateY(-5px); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 20px; }
        .eleve-item { cursor: pointer; transition: background 0.2s; border-left: 3px solid transparent; }
        .eleve-item:hover { background: #f8f9fa; border-left-color: #ff9900; }
        .code-badge { font-family: monospace; background: #f0f0f0; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-credit-card"></i> Gestion des paiements</h2>
        <p class="mb-0">Enregistrement des paiements Wave, Orange Money, Espèces</p>
    </div>
</div>

<div class="container mb-5">
    <?php echo $message; ?>
    
    <!-- Formulaire de recherche par code -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-search"></i> Rechercher un parent ou un élève</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Code parent</label>
                    <div class="input-group">
                        <input type="text" id="parentCodeInput" class="form-control" placeholder="Ex: P7812345677174" value="<?php echo htmlspecialchars($parent_code); ?>">
                        <button class="btn btn-primary" onclick="chargerParent()">
                            <i class="fas fa-user"></i> Charger parent
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label>Code élève</label>
                    <div class="input-group">
                        <input type="text" id="eleveCodeInput" class="form-control" placeholder="Ex: E7812345676336" value="<?php echo htmlspecialchars($eleve_code); ?>">
                        <button class="btn btn-info" onclick="chargerEleve()">
                            <i class="fas fa-child"></i> Charger élève
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Affichage parent chargé -->
    <?php if($parent_info): ?>
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5><i class="fas fa-user-check"></i> Parent chargé</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($parent_info['prenom'] . ' ' . $parent_info['nom']); ?></p>
                    <p><strong>Téléphone:</strong> <?php echo $parent_info['telephone']; ?></p>
                    <p><strong>Code parent:</strong> <code class="code-badge"><?php echo $parent_info['code_parent']; ?></code></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?php echo $parent_info['email'] ?? 'Non renseigné'; ?></p>
                    <p><strong>Adresse:</strong> <?php echo $parent_info['adresse_complete'] ?? 'Non renseignée'; ?></p>
                    <p><strong>Nombre d'enfants:</strong> <?php echo $parent_info['nb_eleves']; ?></p>
                </div>
            </div>
            
            <hr>
            <h5><i class="fas fa-child"></i> Liste des enfants</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Code élève</th><th>Nom complet</th><th>Classe</th><th>École</th><th>Statut mois courant</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($eleves_list as $eleve): ?>
                        <tr class="eleve-item" onclick="selectionnerEleve(<?php echo $eleve['id_eleve']; ?>, '<?php echo addslashes($eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve']); ?>', '<?php echo $parent_info['telephone']; ?>')">
                            <td><code><?php echo $eleve['code_eleve']; ?></code></td>
                            <td><strong><?php echo htmlspecialchars($eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve']); ?></strong></td>
                            <td><?php echo $eleve['classe'] ?? '-'; ?></td>
                            <td><?php echo $eleve['nom_ecole'] ?? '-'; ?></td>
                            <td>
                                <?php if($eleve['statut_mois_courant'] == 'paye'): ?>
                                    <span class="badge bg-success">Payé</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Impayé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); selectionnerEleve(<?php echo $eleve['id_eleve']; ?>, '<?php echo addslashes($eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve']); ?>', '<?php echo $parent_info['telephone']; ?>')">
                                    <i class="fas fa-credit-card"></i> Payer
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Formulaire de paiement -->
    <div id="paymentForm" style="display: none;" class="card shadow">
        <div class="card-header bg-success text-white">
            <h5><i class="fas fa-money-bill-wave"></i> Enregistrer un paiement</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="paiementForm">
                <input type="hidden" name="action" value="process_paiement">
                <input type="hidden" name="id_eleve" id="id_eleve">
                
                <div class="row">
                    <div class="col-md-6">
                        <label>Élève</label>
                        <input type="text" id="eleve_nom" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Téléphone parent</label>
                        <input type="text" id="parent_telephone" class="form-control" readonly>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Montant (FCFA)</label>
                        <input type="number" name="montant" id="montant" class="form-control" required step="1000" value="15000">
                    </div>
                    <div class="col-md-4">
                        <label>Période (mois)</label>
                        <input type="month" name="mois_periode" class="form-control" required value="<?php echo date('Y-m'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Mode de paiement</label>
                        <select name="mode_paiement" id="mode_paiement" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <option value="wave">Wave</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="especes">Espèces</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>
                </div>
                
                <div id="telephoneField" class="row mt-3" style="display: none;">
                    <div class="col-md-6">
                        <label>Numéro de téléphone (Wave/Orange)</label>
                        <input type="tel" name="numero_telephone" class="form-control" placeholder="77 123 45 67">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle"></i> Valider le paiement
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg" onclick="$('#paymentForm').hide();">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function chargerParent() {
    let code = $('#parentCodeInput').val().trim();
    if(code) {
        window.location.href = '/modules/paiements/gestion_paiement.php?parent_code=' + encodeURIComponent(code);
    } else {
        alert('Veuillez entrer un code parent');
    }
}

function chargerEleve() {
    let code = $('#eleveCodeInput').val().trim();
    if(code) {
        window.location.href = '/modules/paiements/gestion_paiement.php?eleve_code=' + encodeURIComponent(code);
    } else {
        alert('Veuillez entrer un code élève');
    }
}

function selectionnerEleve(id, nom, telephone) {
    $('#id_eleve').val(id);
    $('#eleve_nom').val(nom);
    $('#parent_telephone').val(telephone);
    $('#paymentForm').show();
    $('html, body').animate({ scrollTop: $('#paymentForm').offset().top - 100 }, 500);
}

$('#mode_paiement').on('change', function() {
    if($(this).val() === 'wave' || $(this).val() === 'orange_money') {
        $('#telephoneField').show();
    } else {
        $('#telephoneField').hide();
    }
});

<?php if($eleve_info): ?>
// Auto-sélection de l'élève si code fourni
selectionnerEleve(<?php echo $eleve_info['id_eleve']; ?>, '<?php echo addslashes($eleve_info['prenom_eleve'] . ' ' . $eleve_info['nom_eleve']); ?>', '<?php echo $eleve_info['parent_telephone']; ?>');
<?php endif; ?>
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
