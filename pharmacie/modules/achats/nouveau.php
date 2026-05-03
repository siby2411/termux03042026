<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

$page_title = "Nouvel Arrivage";
$active_menu = "achats";
include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';

$fournisseurs = Database::query("SELECT id, nom FROM fournisseurs WHERE actif = 1 ORDER BY nom ASC");
$medicaments = Database::query("SELECT id, denomination, forme, prix_vente_ttc FROM medicaments WHERE actif = 1 ORDER BY denomination ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-box-seam me-2"></i>Saisie d'un Arrivage</h3>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">Retour à l'historique</a>
</div>

<form id="formAchat">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="omega-card shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Informations Générales</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Fournisseur *</label>
                    <select name="fournisseur_id" class="form-select" required>
                        <option value="">Choisir un fournisseur...</option>
                        <?php foreach($fournisseurs as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Référence Facture</label>
                    <input type="text" name="reference_facture" class="form-control" placeholder="ex: FAC-2026-001">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Date de Réception</label>
                    <input type="date" name="date_achat" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="omega-card shadow-sm overflow-hidden">
                <div class="omega-card-head blue-head d-flex justify-content-between align-items-center">
                    <span>CONTENU DE LA COMMANDE</span>
                    <button type="button" class="btn btn-sm btn-light" onclick="ajouterLigne()"><i class="bi bi-plus-circle"></i> Ajouter un produit</button>
                </div>
                <div class="bg-white p-0">
                    <table class="table table-hover align-middle mb-0" id="tableAchats">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Médicament</th>
                                <th>Quantité</th>
                                <th>P.U Achat (F)</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="lignesAchats">
                            </tbody>
                    </table>
                    <div class="p-4 text-end border-top bg-light">
                        <h4 class="fw-bold">Total Général : <span id="totalGeneral" class="text-success">0</span> F</h4>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <button type="submit" class="btn-omega px-5 py-3 shadow">
                    <i class="bi bi-check-all me-2"></i> VALIDER L'ENTRÉE EN STOCK
                </button>
            </div>
        </div>
    </div>
</form>

<script>
let listeMedics = <?= json_encode($medicaments) ?>;

function ajouterLigne() {
    const tbody = document.getElementById('lignesAchats');
    const row = document.createElement('tr');
    
    let options = '<option value="">Sélectionner...</option>';
    listeMedics.forEach(m => {
        options += `<option value="${m.id}">${m.denomination} (${m.forme})</option>`;
    });

    row.innerHTML = `
        <td><select name="medicament_id[]" class="form-select select-medic" required onchange="calculerTotalLigne(this)">${options}</select></td>
        <td><input type="number" name="quantite[]" class="form-control qte" min="1" value="1" oninput="calculerTotalLigne(this)"></td>
        <td><input type="number" name="prix_unitaire[]" class="form-control pu" value="0" oninput="calculerTotalLigne(this)"></td>
        <td class="fw-bold total-ligne">0 F</td>
        <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); majTotalGeneral();"><i class="bi bi-x"></i></button></td>
    `;
    tbody.appendChild(row);
}

function calculerTotalLigne(input) {
    const row = input.closest('tr');
    const qte = row.querySelector('.qte').value || 0;
    const pu = row.querySelector('.pu').value || 0;
    const total = qte * pu;
    row.querySelector('.total-ligne').textContent = total.toLocaleString() + ' F';
    majTotalGeneral();
}

function majTotalGeneral() {
    let total = 0;
    document.querySelectorAll('.total-ligne').forEach(td => {
        total += parseInt(td.textContent.replace(/[^0-9]/g, '')) || 0;
    });
    document.getElementById('totalGeneral').textContent = total.toLocaleString();
}

document.getElementById('formAchat').addEventListener('submit', function(e) {
    e.preventDefault();
    if(document.querySelectorAll('#lignesAchats tr').length === 0) {
        alert('Veuillez ajouter au moins un produit.');
        return;
    }

    const formData = new FormData(this);
    fetch('achats_api.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            alert('Achat enregistré et stock mis à jour !');
            window.location.href = 'index.php';
        } else {
            alert('Erreur: ' + res.message);
        }
    });
});

// Ajouter une ligne par défaut au chargement
ajouterLigne();
</script>

<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
