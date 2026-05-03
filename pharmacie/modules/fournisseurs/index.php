<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

$page_title = "Gestion Fournisseurs";
$active_menu = "fournisseurs";
include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';

$fournisseurs = Database::query("SELECT * FROM fournisseurs WHERE actif = 1 ORDER BY nom ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Répertoire Fournisseurs</h3>
    <button class="btn-omega" data-bs-toggle="modal" data-bs-target="#modalFournisseur">
        <i class="bi bi-plus-lg"></i> Nouveau Fournisseur
    </button>
</div>

<div class="omega-card shadow-sm">
    <div class="omega-card-head blue-head">PARTENAIRES COMMERCIAUX</div>
    <div class="bg-white">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom / Raison Sociale</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php foreach($fournisseurs as $f): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($f['nom']) ?></td>
                    <td><?= $f['telephone'] ?></td>
                    <td><?= $f['email'] ?></td>
                    <td><?= $f['adresse'] ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalFournisseur" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Nouveau Partenaire</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFournisseur">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nom / Raison Sociale *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Adresse</label>
                        <textarea name="adresse" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-omega">Enregistrer le fournisseur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('formFournisseur').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('fournisseurs_api.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            location.reload();
        } else {
            alert('Erreur : ' + res.message);
        }
    });
});
</script>

<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
