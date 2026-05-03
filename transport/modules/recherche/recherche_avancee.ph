<?php
session_start();
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Récupération des filtres
$type_recherche = $_GET['type'] ?? 'eleves';
$search_term = $_GET['search'] ?? '';
$filtre_ecole = $_GET['ecole'] ?? '';
$filtre_bus = $_GET['bus'] ?? '';
$filtre_paiement = $_GET['paiement'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche avancée - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .search-container { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; margin-bottom: 30px; }
        .result-card { transition: transform 0.3s; margin-bottom: 20px; }
        .result-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .badge-paiement-paye { background: #4CAF50; }
        .badge-paiement-impaye { background: #f44336; }
        .autocomplete-suggestions { position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 1000; }
        .autocomplete-suggestion { padding: 10px; cursor: pointer; }
        .autocomplete-suggestion:hover { background: #f0f0f0; }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-search"></i> Recherche avancée multicritères</h2>
            <hr>
        </div>
    </div>
    
    <!-- Formulaire recherche -->
    <div class="search-container">
        <div class="row">
            <div class="col-md-3">
                <label style="color: white;">Type de recherche</label>
                <select id="typeRecherche" class="form-control">
                    <option value="eleves" <?php echo $type_recherche == 'eleves' ? 'selected' : ''; ?>>Élèves</option>
                    <option value="parents" <?php echo $type_recherche == 'parents' ? 'selected' : ''; ?>>Parents d'élèves</option>
                    <option value="chauffeurs" <?php echo $type_recherche == 'chauffeurs' ? 'selected' : ''; ?>>Chauffeurs</option>
                    <option value="impayes" <?php echo $type_recherche == 'impayes' ? 'selected' : ''; ?>>Impayés par bus</option>
                </select>
            </div>
            <div class="col-md-6">
                <label style="color: white;">Recherche</label>
                <div style="position: relative;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Nom, prénom, téléphone, immatriculation..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" autocomplete="off">
                    <div id="autocompleteResults" class="autocomplete-suggestions" style="display: none;"></div>
                </div>
            </div>
            <div class="col-md-3">
                <label style="color: white;">&nbsp;</label>
                <button id="btnRechercher" class="btn btn-light btn-block">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
        </div>
        
        <div class="row mt-3" id="filtresSupplementaires" style="display: none;">
            <div class="col-md-4">
                <label style="color: white;">Filtrer par école</label>
                <select id="filtreEcole" class="form-control">
                    <option value="">Toutes</option>
                    <?php
                    $ecoles = $db->query("SELECT id_ecole, nom_ecole FROM ecoles");
                    while($ecole = $ecoles->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$ecole['id_ecole']}'>{$ecole['nom_ecole']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label style="color: white;">Filtrer par bus</label>
                <select id="filtreBus" class="form-control">
                    <option value="">Tous</option>
                    <?php
                    $buses = $db->query("SELECT id_bus, immatriculation FROM bus");
                    while($bus = $buses->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$bus['id_bus']}'>{$bus['immatriculation']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label style="color: white;">Statut paiement</label>
                <select id="filtrePaiement" class="form-control">
                    <option value="">Tous</option>
                    <option value="paye">Payé</option>
                    <option value="impaye">Impayé</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Résultats -->
    <div id="resultats" class="row">
        <div class="col-md-12 text-center">
            <div class="spinner-border" role="status" style="display: none;" id="loading">
                <span class="sr-only">Chargement...</span>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Autocomplétion
    let typingTimer;
    $('#searchInput').on('input', function() {
        clearTimeout(typingTimer);
        let term = $(this).val();
        if(term.length >= 2) {
            typingTimer = setTimeout(function() {
                $.ajax({
                    url: 'ajax_autocomplete.php',
                    method: 'GET',
                    data: {q: term, type: $('#typeRecherche').val()},
                    success: function(data) {
                        let suggestions = JSON.parse(data);
                        let html = '';
                        suggestions.forEach(function(item) {
                            html += `<div class="autocomplete-suggestion" onclick="$('#searchInput').val('${item.label}'); $('#autocompleteResults').hide(); $('#btnRechercher').click();">
                                        ${item.label} <small class="text-muted">${item.info}</small>
                                     </div>`;
                        });
                        $('#autocompleteResults').html(html).show();
                    }
                });
            }, 300);
        } else {
            $('#autocompleteResults').hide();
        }
    });
    
    // Cacher autocomplete en cliquant ailleurs
    $(document).on('click', function(e) {
        if(!$(e.target).closest('#searchInput').length) {
            $('#autocompleteResults').hide();
        }
    });
    
    // Affichage filtres selon type
    $('#typeRecherche').on('change', function() {
        if($(this).val() === 'eleves' || $(this).val() === 'impayes') {
            $('#filtresSupplementaires').show();
        } else {
            $('#filtresSupplementaires').hide();
        }
        $('#btnRechercher').click();
    });
    
    // Recherche
    $('#btnRechercher').on('click', function() {
        let params = {
            type: $('#typeRecherche').val(),
            search: $('#searchInput').val(),
            ecole: $('#filtreEcole').val(),
            bus: $('#filtreBus').val(),
            paiement: $('#filtrePaiement').val()
        };
        
        $('#loading').show();
        $('#resultats').html('');
        
        $.ajax({
            url: 'ajax_recherche_results.php',
            method: 'GET',
            data: params,
            success: function(data) {
                $('#resultats').html(data);
                $('#loading').hide();
            }
        });
    });
    
    // Premier chargement
    $('#btnRechercher').click();
});
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
