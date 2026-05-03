<?php
// modules/paiements/recherche_par_code.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/codes_manager.php';

$database = new Database();
$db = $database->getConnection();

$recherche_code = $_GET['code'] ?? '';
$resultat = null;

if(!empty($recherche_code)) {
    $type = validateCode($recherche_code);
    
    if($type === 'parent') {
        $resultat = findParentByCode($recherche_code, $db);
        if($resultat) {
            // Récupérer les élèves de ce parent
            $stmt = $db->prepare("SELECT * FROM eleves WHERE id_parent = ?");
            $stmt->execute([$resultat['id_parent']]);
            $resultat['eleves'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } elseif($type === 'eleve') {
        $resultat = findEleveByCode($recherche_code, $db);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche par code unique - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-qrcode"></i> Recherche par code unique</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="code" class="form-control form-control-lg" 
                                   placeholder="Entrez le code parent (PXXXXXXXXXXXXX) ou code élève (EXXXXXXXXXXXXX)"
                                   value="<?php echo htmlspecialchars($recherche_code); ?>">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                        <small class="text-muted">Format: P + 9 chiffres téléphone + 4 chiffres ou E + 9 chiffres téléphone parent + 4 chiffres</small>
                    </form>
                    
                    <?php if($resultat): ?>
                        <?php if(isset($resultat['code_parent'])): ?>
                            <!-- Résultat parent -->
                            <div class="alert alert-success">
                                <h5><i class="fas fa-user-check"></i> Parent trouvé</h5>
                                <p><strong>Code:</strong> <?php echo $resultat['code_parent']; ?></p>
                                <p><strong>Nom:</strong> <?php echo $resultat['prenom'] . ' ' . $resultat['nom']; ?></p>
                                <p><strong>Téléphone:</strong> <?php echo $resultat['telephone']; ?></p>
                                <hr>
                                <h6>Élèves inscrits:</h6>
                                <ul>
                                <?php foreach($resultat['eleves'] as $eleve): ?>
                                    <li><?php echo $eleve['prenom_eleve'] . ' ' . $eleve['nom_eleve']; ?> 
                                        (Code: <?php echo $eleve['code_eleve']; ?>)
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                                <a href="gestion_paiement.php?parent_code=<?php echo $resultat['code_parent']; ?>" 
                                   class="btn btn-success">Effectuer un paiement</a>
                            </div>
                        <?php else: ?>
                            <!-- Résultat élève -->
                            <div class="alert alert-info">
                                <h5><i class="fas fa-child"></i> Élève trouvé</h5>
                                <p><strong>Code élève:</strong> <?php echo $resultat['code_eleve']; ?></p>
                                <p><strong>Nom:</strong> <?php echo $resultat['prenom_eleve'] . ' ' . $resultat['nom_eleve']; ?></p>
                                <p><strong>Parent:</strong> <?php echo $resultat['prenom'] . ' ' . $resultat['nom']; ?></p>
                                <p><strong>Téléphone parent:</strong> <?php echo $resultat['telephone']; ?></p>
                                <p><strong>Code parent:</strong> <?php echo $resultat['code_parent']; ?></p>
                                <a href="gestion_paiement.php?eleve_code=<?php echo $resultat['code_eleve']; ?>" 
                                   class="btn btn-success">Effectuer un paiement pour cet élève</a>
                            </div>
                        <?php endif; ?>
                    <?php elseif($recherche_code): ?>
                        <div class="alert alert-danger">Aucun résultat trouvé pour le code: <?php echo htmlspecialchars($recherche_code); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
