<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .rdv-card { transition: 0.2s; border-radius: 12px; overflow: hidden; margin-bottom: 15px; }
    .rdv-card:hover { transform: translateX(5px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .badge-planifie { background: #17a2b8; }
    .badge-effectue { background: #28a745; }
    .badge-annule { background: #dc3545; }
</style>

<h2><i class="fas fa-calendar-alt"></i> Agenda commercial – Prospection & Rendez-vous</h2>
<p class="text-muted">Planifiez vos visites, foires, salons, et suivez vos prospects sur le terrain (Dakar, Paris, régions).</p>

<?php
// Ajout / modification rendez-vous
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    
    if ($action === 'save') {
        $titre = $_POST['titre'];
        $desc = $_POST['description'];
        $date_rdv = $_POST['date_rdv'];
        $heure_rdv = $_POST['heure_rdv'];
        $duree = $_POST['duree'];
        $lieu = $_POST['lieu'];
        $type_rdv = $_POST['type_rdv'];
        $contact_nom = $_POST['contact_nom'];
        $contact_tel = $_POST['contact_telephone'];
        $contact_email = $_POST['contact_email'];
        $statut = $_POST['statut'];
        $materiel = $_POST['materiel_requis'];
        $notes = $_POST['notes_apres'];
        
        if ($id) {
            $stmt = $pdo->prepare("UPDATE agenda_rdv SET titre=?, description=?, date_rdv=?, heure_rdv=?, duree=?, lieu=?, type_rdv=?, contact_nom=?, contact_telephone=?, contact_email=?, statut=?, materiel_requis=?, notes_apres=? WHERE id=?");
            $stmt->execute([$titre, $desc, $date_rdv, $heure_rdv, $duree, $lieu, $type_rdv, $contact_nom, $contact_tel, $contact_email, $statut, $materiel, $notes, $id]);
            echo "<div class='alert alert-success'>✅ Rendez-vous modifié.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO agenda_rdv (titre, description, date_rdv, heure_rdv, duree, lieu, type_rdv, contact_nom, contact_telephone, contact_email, statut, materiel_requis, notes_apres) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$titre, $desc, $date_rdv, $heure_rdv, $duree, $lieu, $type_rdv, $contact_nom, $contact_tel, $contact_email, $statut, $materiel, $notes]);
            echo "<div class='alert alert-success'>✅ Rendez-vous ajouté.</div>";
        }
    }
    
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM agenda_rdv WHERE id=?")->execute([$id]);
        echo "<div class='alert alert-warning'>🗑️ Rendez-vous supprimé.</div>";
    }
}

// Filtres
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_statut = $_GET['statut'] ?? '';

$sql = "SELECT * FROM agenda_rdv WHERE date_rdv >= ?";
$params = [$filter_date];
if ($filter_statut) {
    $sql .= " AND statut = ?";
    $params[] = $filter_statut;
}
$sql .= " ORDER BY date_rdv ASC, heure_rdv ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rdvs = $stmt->fetchAll();

$lieux = $pdo->query("SELECT * FROM lieux_convergence ORDER BY type, nom")->fetchAll();
$rdv_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM agenda_rdv WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $rdv_edit = $stmt->fetch();
}
?>

<div class="row">
    <!-- Formulaire -->
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus-circle"></i> <?= $rdv_edit ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous / prospection' ?>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= $rdv_edit['id'] ?? '' ?>">
                    
                    <div class="mb-2"><input type="text" name="titre" class="form-control" placeholder="Titre (ex: Visite association ASF)" value="<?= htmlspecialchars($rdv_edit['titre'] ?? '') ?>" required></div>
                    <div class="mb-2"><textarea name="description" class="form-control" rows="2" placeholder="Objectif / description"><?= htmlspecialchars($rdv_edit['description'] ?? '') ?></textarea></div>
                    
                    <div class="row">
                        <div class="col-6"><input type="date" name="date_rdv" class="form-control mb-2" value="<?= htmlspecialchars($rdv_edit['date_rdv'] ?? date('Y-m-d')) ?>" required></div>
                        <div class="col-6"><input type="time" name="heure_rdv" class="form-control mb-2" value="<?= htmlspecialchars($rdv_edit['heure_rdv'] ?? '09:00') ?>"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6"><input type="number" name="duree" class="form-control mb-2" placeholder="Durée (min)" value="<?= $rdv_edit['duree'] ?? 60 ?>"></div>
                        <div class="col-6">
                            <select name="statut" class="form-select mb-2">
                                <option value="planifie" <?= ($rdv_edit['statut'] ?? '') == 'planifie' ? 'selected' : '' ?>>⏳ Planifié</option>
                                <option value="effectue" <?= ($rdv_edit['statut'] ?? '') == 'effectue' ? 'selected' : '' ?>>✅ Effectué</option>
                                <option value="annule" <?= ($rdv_edit['statut'] ?? '') == 'annule' ? 'selected' : '' ?>>❌ Annulé</option>
                                <option value="reporte" <?= ($rdv_edit['statut'] ?? '') == 'reporte' ? 'selected' : '' ?>>🔄 Reporté</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-2"><input type="text" name="lieu" class="form-control mb-2" placeholder="Lieu (adresse ou nom du lieu)" value="<?= htmlspecialchars($rdv_edit['lieu'] ?? '') ?>"></div>
                    
                    <div class="row">
                        <div class="col-4">
                            <select name="type_rdv" class="form-select mb-2">
                                <option value="prospect" <?= ($rdv_edit['type_rdv'] ?? '') == 'prospect' ? 'selected' : '' ?>>📌 Prospect</option>
                                <option value="client" <?= ($rdv_edit['type_rdv'] ?? '') == 'client' ? 'selected' : '' ?>>👥 Client</option>
                                <option value="partenaire" <?= ($rdv_edit['type_rdv'] ?? '') == 'partenaire' ? 'selected' : '' ?>>🤝 Partenaire</option>
                                <option value="foire" <?= ($rdv_edit['type_rdv'] ?? '') == 'foire' ? 'selected' : '' ?>>🎪 Foire/Salon</option>
                            </select>
                        </div>
                        <div class="col-8"><input type="text" name="contact_nom" class="form-control mb-2" placeholder="Nom du contact" value="<?= htmlspecialchars($rdv_edit['contact_nom'] ?? '') ?>"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6"><input type="tel" name="contact_telephone" class="form-control mb-2" placeholder="Téléphone" value="<?= htmlspecialchars($rdv_edit['contact_telephone'] ?? '') ?>"></div>
                        <div class="col-6"><input type="email" name="contact_email" class="form-control mb-2" placeholder="Email" value="<?= htmlspecialchars($rdv_edit['contact_email'] ?? '') ?>"></div>
                    </div>
                    
                    <div class="mb-2"><textarea name="materiel_requis" class="form-control mb-2" rows="2" placeholder="📦 Matériel à emporter (ex: tablette, brochures, échantillons, QR codes)"><?= htmlspecialchars($rdv_edit['materiel_requis'] ?? '') ?></textarea></div>
                    <div class="mb-2"><textarea name="notes_apres" class="form-control mb-2" rows="2" placeholder="📝 Notes après rendez-vous / compte-rendu"><?= htmlspecialchars($rdv_edit['notes_apres'] ?? '') ?></textarea></div>
                    
                    <button type="submit" class="btn btn-primary w-100"><?= $rdv_edit ? 'Mettre à jour' : 'Ajouter le rendez-vous' ?></button>
                    <?php if ($rdv_edit): ?>
                        <a href="agenda.php" class="btn btn-secondary w-100 mt-2">Annuler la modification</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Lieux de convergence prédéfinis -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-map-marker-alt"></i> Lieux de convergence (Sénégalais en France)
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($lieux as $l): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <strong><?= htmlspecialchars($l['nom']) ?></strong><br>
                        <span class="badge bg-secondary"><?= $l['type'] ?></span> <?= htmlspecialchars($l['ville']) ?><br>
                        <small><?= htmlspecialchars($l['adresse']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Liste des rendez-vous -->
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-week"></i> Prochains rendez-vous</span>
                <form method="get" class="d-flex gap-2">
                    <input type="date" name="date" value="<?= $filter_date ?>" class="form-control form-control-sm w-auto">
                    <select name="statut" class="form-select form-select-sm w-auto">
                        <option value="">Tous statuts</option>
                        <option value="planifie" <?= $filter_statut == 'planifie' ? 'selected' : '' ?>>Planifié</option>
                        <option value="effectue" <?= $filter_statut == 'effectue' ? 'selected' : '' ?>>Effectué</option>
                        <option value="annule" <?= $filter_statut == 'annule' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                </form>
            </div>
            <div class="card-body">
                <?php if (count($rdvs) == 0): ?>
                    <p class="text-muted">Aucun rendez-vous pour cette période.</p>
                <?php else: ?>
                    <?php foreach ($rdvs as $r): ?>
                        <div class="rdv-card card mb-2">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <strong><?= htmlspecialchars($r['titre']) ?></strong><br>
                                        <small><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($r['date_rdv'])) ?> à <?= substr($r['heure_rdv'], 0, 5) ?? '--:--' ?></small><br>
                                        <small><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['lieu']) ?></small>
                                    </div>
                                    <div class="col-md-4">
                                        <?php if ($r['contact_nom']): ?>
                                            <small><i class="fas fa-user"></i> <?= htmlspecialchars($r['contact_nom']) ?></small><br>
                                        <?php endif; ?>
                                        <span class="badge <?= $r['statut'] == 'planifie' ? 'badge-planifie' : ($r['statut'] == 'effectue' ? 'badge-effectue' : 'badge-annule') ?>">
                                            <?= $r['statut'] ?>
                                        </span>
                                        <?php if ($r['materiel_requis']): ?>
                                            <br><small><i class="fas fa-box"></i> <?= htmlspecialchars(substr($r['materiel_requis'], 0, 50)) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="agenda.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="agenda.php?delete=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce rendez-vous ?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                                <?php if ($r['notes_apres']): ?>
                                    <div class="mt-2 text-muted small border-top pt-2">📌 <?= htmlspecialchars($r['notes_apres']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Lien feuille de route export -->
        <div class="alert alert-success">
            <i class="fas fa-route"></i> <strong>Feuille de route de prospection</strong><br>
            Vous pouvez organiser vos tournées en utilisant les lieux de convergence ci-contre. <br>
            <a href="agenda_export.php" class="btn btn-sm btn-success mt-2">📎 Exporter le planning (CSV)</a>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
