<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
require_once dirname(__DIR__, 2) . '/config/config.php';
Auth::check();

$medecins = Database::query("SELECT id, nom, specialite FROM medecins WHERE actif = 1 ORDER BY nom ASC") ?: [];
$clients = Database::query("SELECT id, prenom, nom FROM clients WHERE actif = 1 ORDER BY nom ASC") ?: [];
$ordonnances = Database::query("SELECT o.*, CONCAT(c.prenom,' ',c.nom) as client_nom, m.nom as medecin_nom FROM ordonnances o LEFT JOIN clients c ON o.client_id = c.id LEFT JOIN medecins m ON o.medecin_id = m.id ORDER BY o.created_at DESC") ?: [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordonnances — PharmaSen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --omega-green: #00713e; --omega-dark: #01291a; --sidebar-w: 260px; }
        body { background: #f4f7f6; font-family: 'Inter', sans-serif; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-w); background: var(--omega-dark); padding-top: 20px; }
        .sidebar-item { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: rgba(255,255,255,.7); text-decoration: none; }
        .sidebar-item.active { background: var(--omega-green); color: #fff; }
        .main-content { margin-left: var(--sidebar-w); padding: 30px; }
        .badge-nature { font-size: 0.7rem; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
<nav class="sidebar">
    <div class="text-center mb-4 text-white fw-bold">Ω OMEGA PHARMA</div>
    <a href="../../index.php" class="sidebar-item"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../medicaments/" class="sidebar-item"><i class="bi bi-capsule"></i> Médicaments</a>
    <a href="index.php" class="sidebar-item active"><i class="bi bi-file-medical"></i> Ordonnances</a>
</nav>

<div class="main-content">
    <div class="d-flex justify-content-between mb-4">
        <h2 class="h4 fw-bold">Gestion des Ordonnances</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalOrd"><i class="bi bi-plus-lg"></i> Nouvelle Saisie</button>
    </div>

    <div class="card border-0 shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Référence</th><th>Nature</th><th>Patient</th><th>Médecin</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($ordonnances as $o): ?>
                <tr>
                    <td class="fw-bold"><?= $o['numero_ordonnance'] ?></td>
                    <td><span class="badge-nature bg-info text-dark"><?= ucfirst($o['nature']) ?></span></td>
                    <td><?= htmlspecialchars($o['client_nom'] ?: 'Passant') ?></td>
                    <td>Dr. <?= htmlspecialchars($o['medecin_nom']) ?></td>
                    <td><a href="../caisse/pos.php?ord_id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">Servir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalOrd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white"><h5>Saisie Expert Ordonnance</h5></div>
            <form id="formOrd">
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label fw-bold">N° Ordonnance</label><input type="text" name="numero_ordonnance" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">Nature</label>
                        <select name="nature" class="form-select">
                            <option value="ordinaire text-primary">Ordinaire</option>
                            <option value="securisee text-danger">Sécurisée (Stupéfiants)</option>
                            <option value="chronique text-success">Chronique (ALD)</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-bold">Médecin</label>
                        <select name="medecin_id" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach($medecins as $m): ?><option value="<?= $m['id'] ?>">Dr. <?= htmlspecialchars($m['nom']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-bold">Patient</label>
                        <select name="client_id" class="form-select">
                            <option value="">-- Passage --</option>
                            <?php foreach($clients as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label fw-bold text-success">Conseils & Posologie</label><textarea name="conseils_pharmacien" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success w-100 fw-bold">ENREGISTRER L'ORDONNANCE</button></div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formOrd').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('ordonnances_api.php?action=create', {
        method: 'POST',
        body: JSON.stringify(Object.fromEntries(formData)),
        headers: {'Content-Type': 'application/json'}
    }).then(r => r.json()).then(res => {
        if(res.success) location.reload(); else alert(res.message);
    });
});
</script>
</body>
</html>
