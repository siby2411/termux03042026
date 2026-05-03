<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Recherche par nom si fourni
$search_nom = $_GET['nom'] ?? '';
$resultats_nom = [];

if(!empty($search_nom)) {
    $stmt = $db->prepare("
        SELECT p.id_parent, p.nom, p.prenom, p.telephone, p.code_parent, p.email,
               COUNT(e.id_eleve) as nb_enfants,
               GROUP_CONCAT(CONCAT(e.prenom_eleve, ' ', e.nom_eleve, ' (', e.code_eleve, ')') SEPARATOR ' | ') as enfants
        FROM parents p
        LEFT JOIN eleves e ON p.id_parent = e.id_parent
        WHERE p.nom LIKE ? OR p.prenom LIKE ? OR CONCAT(p.prenom, ' ', p.nom) LIKE ?
        GROUP BY p.id_parent
        LIMIT 20
    ");
    $search_param = "%$search_nom%";
    $stmt->execute([$search_param, $search_param, $search_param]);
    $resultats_nom = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .search-container { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 0; }
        .search-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .result-card { background: white; border-radius: 15px; padding: 20px; margin-top: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .nav-tabs .nav-link { font-weight: 500; }
        .nav-tabs .nav-link.active { color: #003366; border-bottom-color: #ff9900; }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="search-container">
    <div class="container">
        <div class="search-card">
            <ul class="nav nav-tabs mb-4" id="searchTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="code-tab" data-bs-toggle="tab" data-bs-target="#codeSearch" type="button" role="tab">
                        <i class="fas fa-qrcode"></i> Recherche par code
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nom-tab" data-bs-toggle="tab" data-bs-target="#nomSearch" type="button" role="tab">
                        <i class="fas fa-user"></i> Recherche par nom
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Onglet Recherche par code -->
                <div class="tab-pane fade show active" id="codeSearch" role="tabpanel">
                    <div class="input-group mb-3">
                        <input type="text" id="codeInput" class="form-control form-control-lg" 
                               placeholder="Code parent (PXXXXXXXXXXXXX) ou code élève (EXXXXXXXXXXXXX)">
                        <button class="btn btn-primary btn-lg" onclick="rechercherParCode()">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                    <div id="codeResultat"></div>
                </div>
                
                <!-- Onglet Recherche par nom -->
                <div class="tab-pane fade" id="nomSearch" role="tabpanel">
                    <form method="GET" action="">
                        <div class="input-group mb-3">
                            <input type="text" name="nom" class="form-control form-control-lg" 
                                   placeholder="Nom du parent: Diop, Ndiaye, Fall, TEST_CODE..."
                                   value="<?php echo htmlspecialchars($search_nom); ?>">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                    
                    <?php if(!empty($search_nom) && !empty($resultats_nom)): ?>
                    <div class="mt-3">
                        <h5><i class="fas fa-users"></i> Résultats pour "<?php echo htmlspecialchars($search_nom); ?>"</h5>
                        <?php foreach($resultats_nom as $parent): ?>
                        <div class="result-card">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4><?php echo htmlspecialchars($parent['prenom'] . ' ' . $parent['nom']); ?></h4>
                                    <p><i class="fas fa-phone"></i> <?php echo $parent['telephone']; ?></p>
                                    <p><i class="fas fa-envelope"></i> <?php echo $parent['email'] ?? 'Non renseigné'; ?></p>
                                    <p><i class="fas fa-id-card"></i> Code: <code><?php echo $parent['code_parent']; ?></code></p>
                                    <p><i class="fas fa-child"></i> Enfants: <?php echo $parent['nb_enfants']; ?></p>
                                    <small class="text-muted"><?php echo htmlspecialchars($parent['enfants'] ?? ''); ?></small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button class="btn btn-primary mb-2 w-100" onclick="voirParent('<?php echo $parent['code_parent']; ?>')">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <button class="btn btn-success w-100" onclick="paiementParent('<?php echo $parent['code_parent']; ?>')">
                                        <i class="fas fa-credit-card"></i> Paiement
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif(!empty($search_nom)): ?>
                    <div class="alert alert-warning mt-3">Aucun parent trouvé pour "<?php echo htmlspecialchars($search_nom); ?>"</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function rechercherParCode() {
    let code = $('#codeInput').val().trim();
    if(code.length < 5) {
        alert('Entrez un code valide (14 caractères minimum)');
        return;
    }
    
    $('#codeResultat').html('<div class="text-center mt-3"><div class="spinner-border text-primary"></div><p>Recherche en cours...</p></div>');
    
    $.ajax({
        url: '/api/search_by_code.php?code=' + encodeURIComponent(code),
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                if(response.type === 'parent') {
                    let parent = response.data;
                    let eleves = response.eleves || [];
                    
                    let html = '<div class="result-card mt-3">';
                    html += '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Parent trouvé</div>';
                    html += '<h4>' + (parent.prenom || '') + ' ' + (parent.nom || '') + '</h4>';
                    html += '<p><i class="fas fa-phone"></i> Téléphone: ' + parent.telephone + '</p>';
                    html += '<p><i class="fas fa-id-card"></i> Code: <code>' + parent.code_parent + '</code></p>';
                    html += '<hr><h5>Enfants (' + eleves.length + ')</h5>';
                    
                    if(eleves.length > 0) {
                        html += '<div class="table-responsive"><table class="table table-sm">';
                        for(let e of eleves) {
                            html += '<tr><td><code>' + (e.code_eleve || '-') + '</code></td>';
                            html += '<td>' + (e.prenom_eleve || '') + ' ' + (e.nom_eleve || '') + '</td>';
                            html += '<td>' + (e.classe || '-') + '</td>';
                            html += '<td><button class="btn btn-sm btn-success" onclick="paiementEleve(\'' + e.code_eleve + '\')">Paiement</button></td></tr>';
                        }
                        html += '</table></div>';
                    }
                    html += '</div>';
                    $('#codeResultat').html(html);
                } else {
                    let eleve = response.data;
                    let html = '<div class="result-card mt-3">';
                    html += '<div class="alert alert-info"><i class="fas fa-check-circle"></i> Élève trouvé</div>';
                    html += '<h4>' + (eleve.prenom_eleve || '') + ' ' + (eleve.nom_eleve || '') + '</h4>';
                    html += '<p><i class="fas fa-school"></i> Classe: ' + (eleve.classe || '-') + '</p>';
                    html += '<p><i class="fas fa-id-card"></i> Code élève: <code>' + eleve.code_eleve + '</code></p>';
                    html += '<hr><p><strong>Parent:</strong> ' + (eleve.parent_prenom || '') + ' ' + (eleve.parent_nom || '') + '</p>';
                    html += '<p><i class="fas fa-phone"></i> Téléphone parent: ' + (eleve.parent_telephone || '-') + '</p>';
                    html += '<p><i class="fas fa-id-card"></i> Code parent: <code>' + (eleve.code_parent || '-') + '</code></p>';
                    html += '<div class="mt-3"><button class="btn btn-success" onclick="paiementEleve(\'' + eleve.code_eleve + '\')"><i class="fas fa-credit-card"></i> Effectuer un paiement</button></div>';
                    html += '</div>';
                    $('#codeResultat').html(html);
                }
            } else {
                $('#codeResultat').html('<div class="alert alert-danger mt-3">' + (response.error || 'Code non trouvé') + '</div>');
            }
        },
        error: function() {
            $('#codeResultat').html('<div class="alert alert-danger mt-3">Erreur de connexion</div>');
        }
    });
}

function voirParent(code) {
    window.location.href = '/modules/recherche/recherche_par_code.php?code=' + code;
}

function paiementParent(code) {
    window.location.href = '/modules/paiements/gestion_paiement.php?parent_code=' + code;
}

function paiementEleve(code) {
    window.location.href = '/modules/paiements/gestion_paiement.php?eleve_code=' + code;
}

// Recherche auto si paramètre dans l'URL
const urlParams = new URLSearchParams(window.location.search);
const codeParam = urlParams.get('code');
if(codeParam) {
    document.getElementById('codeInput').value = codeParam;
    rechercherParCode();
    document.getElementById('code-tab').click();
}
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
