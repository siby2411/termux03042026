<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des Tiers - Clients/Fournisseurs";
$page_icon = "people";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Ajout d'un tiers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $code = trim($_POST['code']);
    $type = $_POST['type'];
    $raison_sociale = trim($_POST['raison_sociale']);
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $numero_compte = ($type == 'CLIENT') ? 411 : 401;
    $identifiant_fiscal = trim($_POST['identifiant_fiscal']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO TIERS (code, type, raison_sociale, adresse, telephone, email, numero_compte, identifiant_fiscal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $raison_sociale, $adresse, $telephone, $email, $numero_compte, $identifiant_fiscal]);
        $message = "✅ Tiers ajouté avec succès";
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// Récupération des tiers
$tiers = $pdo->query("SELECT * FROM TIERS ORDER BY type, raison_sociale")->fetchAll();
$clients = count(array_filter($tiers, fn($t) => $t['type'] == 'CLIENT'));
$fournisseurs = count(array_filter($tiers, fn($t) => $t['type'] == 'FOURNISSEUR'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people"></i> Gestion des Tiers</h5>
                <small>Clients, Fournisseurs et autres partenaires</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h3><?= count($tiers) ?></h3>
                                <small>Total Tiers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h3><?= $clients ?></h3>
                                <small>Clients</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h3><?= $fournisseurs ?></h3>
                                <small>Fournisseurs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white text-center">
                            <div class="card-body">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="bi bi-plus-lg"></i> Nouveau
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des tiers -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Code</th>
                                <th>Type</th>
                                <th>Raison sociale</th>
                                <th>Adresse</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>NIF</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tiers as $t): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($t['code']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $t['type'] == 'CLIENT' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $t['type'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($t['raison_sociale']) ?></td>
                                <td><?= htmlspecialchars($t['adresse'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['telephone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['identifiant_fiscal'] ?? '-') ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary" onclick="editTier(<?= $t['id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="facturation.php?tier_id=<?= $t['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-file-invoice"></i>
                                    </a>
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

<!-- Modal Ajout -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Ajouter un tiers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Code</label>
                            <input type="text" name="code" class="form-control" placeholder="CLI001 / FOUR001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Type</label>
                            <select name="type" class="form-select" required>
                                <option value="CLIENT">Client</option>
                                <option value="FOURNISSEUR">Fournisseur</option>
                                <option value="BANQUE">Banque</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Raison sociale</label>
                            <input type="text" name="raison_sociale" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Téléphone</label>
                            <input type="tel" name="telephone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Identifiant Fiscal (NIF)</label>
                            <input type="text" name="identifiant_fiscal" class="form-control" placeholder="Ex: 123456789A">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTier(id) {
    alert("Fonction d'édition à implémenter pour l'ID: " + id);
}
</script>

<?php include 'inc_footer.php'; ?>
