<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $numero_licence = 'OM-' . date('Y') . '-' . rand(1000, 9999);
    $query = "INSERT INTO adherents (numero_licence, nom, prenom, email, telephone, date_naissance, adresse, discipline_principale) 
              VALUES (:num, :nom, :prenom, :email, :tel, :naissance, :adresse, :discipline)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':num' => $numero_licence,
        ':nom' => $_POST['nom'],
        ':prenom' => $_POST['prenom'],
        ':email' => $_POST['email'],
        ':tel' => $_POST['telephone'],
        ':naissance' => $_POST['date_naissance'],
        ':adresse' => $_POST['adresse'],
        ':discipline' => $_POST['discipline_principale']
    ]);
    $success = "Adhérent ajouté! Licence: " . $numero_licence;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM adherents WHERE statut='actif' ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$adherents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_query = "SELECT COUNT(*) as total FROM adherents WHERE statut='actif'";
$total = $db->query($total_query)->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-users"></i> Gestion des Adhérents</h3>
        </div>
        <div class="card-body">
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addAdherentModal">
                <i class="fas fa-plus"></i> Nouvel Adhérent
            </button>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Licence</th><th>Nom & Prénom</th><th>Email</th><th>Téléphone</th><th>Discipline</th><th>Inscription</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($adherents as $a): ?>
                        <tr>
                            <td><strong><?= $a['numero_licence'] ?></strong></td>
                            <td><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= $a['telephone'] ?></td>
                            <td><span class="badge bg-info"><?= $a['discipline_principale'] ?></span></td>
                            <td><?= date('d/m/Y', strtotime($a['date_inscription'])) ?></td>
                            <td>
                                <a href="adherent_details.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($total_pages > 1): ?>
            <nav><ul class="pagination"><?php for($i=1;$i<=$total_pages;$i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?></ul></nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="addAdherentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-user-plus"></i> Nouvel Adhérent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="prenom" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Téléphone</label><input type="tel" name="telephone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Date Naissance</label><input type="date" name="date_naissance" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Discipline Principale</label>
                            <select name="discipline_principale" class="form-control">
                                <option>Boxe Anglaise</option><option>Karaté</option><option>Jiu-Jitsu Brésilien</option>
                                <option>Muay Thai</option><option>CrossFit</option><option>Yoga</option><option>Kickboxing</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
