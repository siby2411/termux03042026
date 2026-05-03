<?php
include 'includes/db.php';
include 'includes/header.php';

$message = "";

// --- TRAITEMENT DE L'UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fichier'])) {
    $serviceId = $_POST['service_id'];
    
    // Récupération du nom du service pour le dossier
    $stmtService = $pdo->prepare("SELECT nom_service FROM services WHERE id = ?");
    $stmtService->execute([$serviceId]);
    $nomService = $stmtService->fetchColumn();

    if ($nomService) {
        $nomDossier = str_replace(' ', '_', $nomService);
        $targetDir = "uploads/" . $nomDossier . "/";

        // Créer le dossier s'il n'existe pas
        if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

        $fileName = basename($_FILES['fichier']['name']);
        $targetPath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        
        // Liste des extensions AUTORISÉES (plus simple que 'interdit')
        $autorise = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png', 'txt', 'zip'];
        
        if (!in_array($fileType, $autorise)) {
            $message = "<div class='alert alert-danger'>Fichier interdit (. $fileType). Utilisez PDF, Office ou Image.</div>";
        } else {
            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $targetPath)) {
                $sql = "INSERT INTO documents (nom_fichier, chemin_fichier, type_mime, service_cible_id) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fileName, $targetPath, $fileType, $serviceId]);
                $message = "<div class='alert alert-success'>Fichier ajouté avec succès dans <strong>$nomService</strong> !</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur lors du transfert du fichier.</div>";
            }
        }
    }
}

// --- AFFICHAGE ---
$filter_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$query = "SELECT d.*, s.nom_service FROM documents d LEFT JOIN services s ON d.service_cible_id = s.id";
if ($filter_service_id > 0) {
    $query .= " WHERE d.service_cible_id = $filter_service_id";
}
$docs = $pdo->query($query . " ORDER BY d.date_upload DESC")->fetchAll();
$services = $pdo->query("SELECT * FROM services")->fetchAll();
?>

<div class="container-fluid">
    <h2 class="mb-4">Gestion Documentaire</h2>
    <?= $message ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">Upload par Département</div>
                <div class="card-body">
                    <form action="documents.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">1. Choisir le fichier</label>
                            <input type="file" name="fichier" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">2. Département de destination</label>
                            <select name="service_id" class="form-select" required>
                                <option value="">--- Sélectionner ---</option>
                                <?php foreach($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= $s['nom_service'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Envoyer le document</button>
                    </form>
                </div>
            </div>

            <div class="list-group shadow-sm">
                <a href="documents.php" class="list-group-item list-group-item-action active">Tous les services</a>
                <?php foreach($services as $s): ?>
                    <a href="documents.php?service_id=<?= $s['id'] ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder me-2 text-warning"></i> <?= $s['nom_service'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Contenu du répertoire</div>
                <div class="card-body">
                    <?php if(count($docs) > 0): ?>
                        <table class="table table-hover">
                            <thead><tr><th>Fichier</th><th>Service</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach($docs as $d): ?>
                                <tr>
                                    <td><a href="<?= $d['chemin_fichier'] ?>" target="_blank"><?= $d['nom_fichier'] ?></a></td>
                                    <td><span class="badge bg-secondary"><?= $d['nom_service'] ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($d['date_upload'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">Dossier vide.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
