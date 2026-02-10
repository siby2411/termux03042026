<?php
include 'includes/db.php';
include 'includes/header.php';

$message = "";

// --- 1. TRAITEMENT DE L'UPLOAD (Code inchangé ou presque) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fichier'])) {
    $serviceId = $_POST['service_id'];
    
    // Récupération du nom du service
    $stmtService = $pdo->prepare("SELECT nom_service FROM services WHERE id = ?");
    $stmtService->execute([$serviceId]);
    $nomService = $stmtService->fetchColumn();

    if ($nomService) {
        // Création du dossier
        $nomDossier = str_replace(' ', '_', $nomService);
        $nomDossier = preg_replace('/[^A-Za-z0-9_]/', '', $nomDossier);
        
        $baseUploadDir = 'uploads/';
        $targetDir = $baseUploadDir . $nomDossier . '/';

        if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

        $fileName = basename($_FILES['fichier']['name']);
        $targetPath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $interdit = ['php', 'exe', 'sh', 'js', 'sql'];
        
        if (in_array($fileType, $interdit)) {
            $message = "<div class='alert alert-danger'>Fichier interdit.</div>";
        } else {
            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $targetPath)) {
                $sql = "INSERT INTO documents (nom_fichier, chemin_fichier, type_mime, service_cible_id) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fileName, $targetPath, $fileType, $serviceId]);
                $message = "<div class='alert alert-success'>Fichier ajouté au dossier <strong>$nomDossier</strong> !</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur upload.</div>";
            }
        }
    }
}

// --- 2. GESTION DU FILTRE PAR URL ---
// On vérifie si un ID est passé dans l'url (ex: documents.php?service_id=1)
$filter_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$filter_title = "Tous les documents";

if ($filter_service_id > 0) {
    // Si on filtre, on change le titre et la requête SQL
    $stmtName = $pdo->prepare("SELECT nom_service FROM services WHERE id = ?");
    $stmtName->execute([$filter_service_id]);
    $sName = $stmtName->fetchColumn();
    $filter_title = "Dossier : " . $sName;

    // Requête filtrée
    $sqlDocs = "SELECT d.*, s.nom_service FROM documents d LEFT JOIN services s ON d.service_cible_id = s.id WHERE d.service_cible_id = ? ORDER BY d.date_upload DESC";
    $stmtDocs = $pdo->prepare($sqlDocs);
    $stmtDocs->execute([$filter_service_id]);
    $docs = $stmtDocs->fetchAll();
} else {
    // Pas de filtre : on affiche tout
    $docs = $pdo->query("SELECT d.*, s.nom_service FROM documents d LEFT JOIN services s ON d.service_cible_id = s.id ORDER BY d.date_upload DESC")->fetchAll();
}

// Récupération des services pour le menu déroulant
$services = $pdo->query("SELECT * FROM services")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center border-bottom pb-2">
    <h1 class="h2"><?= htmlspecialchars($filter_title) ?></h1>
    <?php if($filter_service_id > 0): ?>
        <a href="documents.php" class="btn btn-outline-secondary btn-sm">Voir tout</a>
    <?php endif; ?>
</div>

<?= $message ?>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Ajouter un fichier</div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="fichier" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <select name="service_id" class="form-select" required>
                            <option value="" disabled <?= ($filter_service_id == 0) ? 'selected' : '' ?>>Choisir dossier...</option>
                            <?php foreach($services as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($filter_service_id == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nom_service']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Uploader</button>
                </form>
            </div>
        </div>
        
        <div class="list-group">
            <a href="documents.php" class="list-group-item list-group-item-action active bg-secondary border-secondary">Navigation Rapide</a>
            <?php foreach($services as $s): ?>
                <a href="documents.php?service_id=<?= $s['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <i class="fas fa-folder text-warning"></i> <?= $s['nom_service'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Contenu du répertoire</div>
            <div class="card-body">
                <?php if(count($docs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nom du fichier</th>
                                <?php if($filter_service_id == 0): ?><th>Service</th><?php endif; ?>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($docs as $doc): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-file-alt text-primary me-2"></i>
                                    <a href="<?= $doc['chemin_fichier'] ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                        <?= htmlspecialchars($doc['nom_fichier']) ?>
                                    </a>
                                </td>
                                <?php if($filter_service_id == 0): ?>
                                    <td><span class="badge bg-secondary"><?= $doc['nom_service'] ?></span></td>
                                <?php endif; ?>
                                <td><small><?= date('d/m/Y', strtotime($doc['date_upload'])) ?></small></td>
                                <td><a href="<?= $doc['chemin_fichier'] ?>" class="btn btn-sm btn-outline-primary" download><i class="fas fa-download"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                        Ce dossier est vide pour le moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
