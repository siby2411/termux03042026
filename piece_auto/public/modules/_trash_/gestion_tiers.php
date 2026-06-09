<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des Tiers - Clients & Fournisseurs";
$page_icon = "people";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Ajout d'un tiers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_tiers') {
        $code = trim($_POST['code']);
        $type = $_POST['type'];
        $raison_sociale = trim($_POST['raison_sociale']);
        $adresse = trim($_POST['adresse']);
        $telephone = trim($_POST['telephone']);
        $email = trim($_POST['email']);
        $identifiant_fiscal = trim($_POST['identifiant_fiscal']);
        $registre_commerce = trim($_POST['registre_commerce']);
        
        $numero_compte = ($type == 'CLIENT') ? 411 : 401;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO TIERS (code, type, raison_sociale, adresse, telephone, email, numero_compte, identifiant_fiscal, registre_commerce) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $type, $raison_sociale, $adresse, $telephone, $email, $numero_compte, $identifiant_fiscal, $registre_commerce]);
            $message = "✅ Tiers ajouté avec succès";
        } catch (Exception $e) {
            $error = "❌ Erreur: " . $e->getMessage();
        }
    }
    
    // Modification
    if ($_POST['action'] === 'edit_tiers') {
        $stmt = $pdo->prepare("UPDATE TIERS SET code=?, raison_sociale=?, adresse=?, telephone=?, email=?, identifiant_fiscal=? WHERE id=?");
        $stmt->execute([$_POST['code'], $_POST['raison_sociale'], $_POST['adresse'], $_POST['telephone'], $_POST['email'], $_POST['identifiant_fiscal'], $_POST['id']]);
        $message = "✅ Tiers modifié avec succès";
    }
    
    // Suppression
    if ($_POST['action'] === 'delete_tiers') {
        $stmt = $pdo->prepare("DELETE FROM TIERS WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $message = "✅ Tiers supprimé";
    }
}

// Récupération des tiers
$clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT' ORDER BY raison_sociale")->fetchAll();
$fournisseurs = $pdo->query("SELECT * FROM TIERS WHERE type = 'FOURNISSEUR' ORDER BY raison_sociale")->fetchAll();
$tous_tiers = $pdo->query("SELECT * FROM TIERS ORDER BY type, raison_sociale")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people"></i> Gestion des Tiers - Clients & Fournisseurs</h5>
                <small>Base essentielle pour la facturation et les effets de commerce</small>
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
                    <div class="col-md-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-people fs-2"></i>
                                <h3><?= count($tous_tiers) ?></h3>
                                <small>Total Tiers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-person-badge fs-2"></i>
                                <h3><?= count($clients) ?></h3>
                                <small>Clients</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <i class="bi bi-truck fs-2"></i>
                                <h3><?= count($fournisseurs) ?></h3>
                                <small>Fournisseurs</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Onglets -->
                <ul class="nav nav-tabs" id="tiersTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#liste">📋 Liste des tiers</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#clients">👥 Clients</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#fournisseurs">🏭 Fournisseurs</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouveau">➕ Nouveau tiers</button></li>
                </ul>
                
                <div class="tab-content mt-3">
                    <!-- Liste tous tiers -->
                    <div class="tab-pane fade show active" id="liste">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr class="text-center">
                                        <th>Code</th><th>Raison sociale</th><th>Type</th><th>Adresse</th><th>Téléphone</th><th>Email</th><th>NIF</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($tous_tiers as $t): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($t['code']) ?></td>
                                        <td><?= htmlspecialchars($t['raison_sociale']) ?></td>
                                        <td class="text-center"><span class="badge <?= $t['type'] == 'CLIENT' ? 'bg-success' : 'bg-warning' ?>"><?= $t['type'] ?></span></td>
                                        <td><?= htmlspecialchars($t['adresse'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($t['telephone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($t['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($t['identifiant_fiscal'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary" onclick="editTier(<?= htmlspecialchars(json_encode($t)) ?>)"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteTier(<?= $t['id'] ?>)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Clients -->
                    <div class="tab-pane fade" id="clients">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark"><tr><th>Code</th><th>Raison sociale</th><th>Adresse</th><th>Téléphone</th><th>Email</th><th>NIF</th></tr></thead>
                                <tbody>
                                    <?php foreach($clients as $c): ?>
                                    <tr><td><?= htmlspecialchars($c['code']) ?></td><td><?= htmlspecialchars($c['raison_sociale']) ?></td><td><?= htmlspecialchars($c['adresse'] ?? '-') ?></td><td><?= htmlspecialchars($c['telephone'] ?? '-') ?></td><td><?= htmlspecialchars($c['email'] ?? '-') ?></td><td><?= htmlspecialchars($c['identifiant_fiscal'] ?? '-') ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Fournisseurs -->
                    <div class="tab-pane fade" id="fournisseurs">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark"><tr><th>Code</th><th>Raison sociale</th><th>Adresse</th><th>Téléphone</th><th>Email</th><th>NIF</th></tr></thead>
                                <tbody>
                                    <?php foreach($fournisseurs as $f): ?>
                                    <tr><td><?= htmlspecialchars($f['code']) ?></td><td><?= htmlspecialchars($f['raison_sociale']) ?></td><td><?= htmlspecialchars($f['adresse'] ?? '-') ?></td><td><?= htmlspecialchars($f['telephone'] ?? '-') ?></td><td><?= htmlspecialchars($f['email'] ?? '-') ?></td><td><?= htmlspecialchars($f['identifiant_fiscal'] ?? '-') ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Nouveau tiers -->
                    <div class="tab-pane fade" id="nouveau">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="add_tiers">
                                    <div class="col-md-3"><label>Code *</label><input type="text" name="code" class="form-control" placeholder="CLI001 / FOUR001" required></div>
                                    <div class="col-md-3"><label>Type *</label><select name="type" class="form-select" required><option value="CLIENT">Client</option><option value="FOURNISSEUR">Fournisseur</option></select></div>
                                    <div class="col-md-6"><label>Raison sociale *</label><input type="text" name="raison_sociale" class="form-control" required></div>
                                    <div class="col-md-12"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"></textarea></div>
                                    <div class="col-md-4"><label>Téléphone</label><input type="tel" name="telephone" class="form-control"></div>
                                    <div class="col-md-4"><label>Email</label><input type="email" name="email" class="form-control"></div>
                                    <div class="col-md-4"><label>Identifiant fiscal (NIF)</label><input type="text" name="identifiant_fiscal" class="form-control"></div>
                                    <div class="col-md-4"><label>Registre commerce (RCCM)</label><input type="text" name="registre_commerce" class="form-control"></div>
                                    <div class="col-12 text-center"><button type="submit" class="btn-omega">Enregistrer le tiers</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modification -->
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5>✏️ Modifier un tiers</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="edit_tiers"><input type="hidden" name="id" id="edit_id">
    <div class="mb-2"><label>Code</label><input type="text" name="code" id="edit_code" class="form-control" required></div>
    <div class="mb-2"><label>Raison sociale</label><input type="text" name="raison_sociale" id="edit_raison" class="form-control" required></div>
    <div class="mb-2"><label>Adresse</label><textarea name="adresse" id="edit_adresse" class="form-control" rows="2"></textarea></div>
    <div class="mb-2"><label>Téléphone</label><input type="text" name="telephone" id="edit_telephone" class="form-control"></div>
    <div class="mb-2"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
    <div class="mb-2"><label>NIF</label><input type="text" name="identifiant_fiscal" id="edit_nif" class="form-control"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Modifier</button></div></form></div></div></div>

<script>
function editTier(t) {
    document.getElementById('edit_id').value = t.id;
    document.getElementById('edit_code').value = t.code;
    document.getElementById('edit_raison').value = t.raison_sociale;
    document.getElementById('edit_adresse').value = t.adresse || '';
    document.getElementById('edit_telephone').value = t.telephone || '';
    document.getElementById('edit_email').value = t.email || '';
    document.getElementById('edit_nif').value = t.identifiant_fiscal || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteTier(id) {
    if(confirm('Confirmer la suppression ?')) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete_tiers"><input type="hidden" name="id" value="'+id+'">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'inc_footer.php'; ?>
