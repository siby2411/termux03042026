<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer tous les parents avec leurs élèves
$query = "SELECT 
    p.id_parent,
    p.nom,
    p.prenom,
    p.telephone,
    p.email,
    p.code_parent,
    p.statut_compte,
    p.date_inscription,
    COUNT(e.id_eleve) as nb_enfants,
    GROUP_CONCAT(CONCAT(e.prenom_eleve, ' ', e.nom_eleve, ' (', e.code_eleve, ')') SEPARATOR ' | ') as liste_enfants
FROM parents p
LEFT JOIN eleves e ON p.id_parent = e.id_parent
GROUP BY p.id_parent
ORDER BY p.date_inscription DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des parents - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, #003366 0%, #006699 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .code-badge {
            font-family: monospace;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        .copy-btn {
            cursor: pointer;
            color: #006699;
            margin-left: 5px;
        }
        .copy-btn:hover { color: #ff9900; }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h2><i class="fas fa-users"></i> Liste des parents d'élèves</h2>
        <p class="mb-0">Codes uniques générés automatiquement pour chaque parent</p>
    </div>
</div>

<div class="container mb-5">
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table id="parentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Code Parent</th>
                            <th>Enfants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($parents as $parent): ?>
                        <tr>
                            <td><?php echo $parent['id_parent']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($parent['prenom'] . ' ' . $parent['nom']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($parent['telephone']); ?></td>
                            <td><?php echo htmlspecialchars($parent['email'] ?? '-'); ?></td>
                            <td>
                                <code class="code-badge" id="code_<?php echo $parent['id_parent']; ?>">
                                    <?php echo $parent['code_parent']; ?>
                                </code>
                                <i class="fas fa-copy copy-btn" onclick="copyToClipboard('code_<?php echo $parent['id_parent']; ?>')" title="Copier le code"></i>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $parent['nb_enfants']; ?> enfant(s)</span>
                                <small class="d-block text-muted"><?php echo htmlspecialchars(substr($parent['liste_enfants'] ?? '', 0, 60)); ?></small>
                            </td>
                            <td>
                                <span class="badge <?php echo $parent['statut_compte'] == 'actif' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $parent['statut_compte']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="voirParent('<?php echo $parent['code_parent']; ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="paiementParent('<?php echo $parent['code_parent']; ?>')">
                                    <i class="fas fa-credit-card"></i>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#parentsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        pageLength: 25
    });
});

function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text);
    alert('Code copié: ' + text);
}

function voirParent(code) {
    window.location.href = '/modules/recherche/recherche_par_code.php?code=' + code;
}

function paiementParent(code) {
    window.location.href = '/modules/paiements/gestion_paiement.php?parent_code=' + code;
}
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
