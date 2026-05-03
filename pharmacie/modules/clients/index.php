<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Helper.php';
require_once dirname(__DIR__,2).'/core/Database.php';
require_once dirname(__DIR__,2).'/config/config.php';
Auth::check();

$s = trim($_GET['s']??'');
$clients = $s
    ? Database::query("SELECT * FROM clients WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ? OR cni LIKE ? ORDER BY nom",['%'.$s.'%','%'.$s.'%','%'.$s.'%','%'.$s.'%'])
    : Database::query("SELECT * FROM clients ORDER BY nom LIMIT 100");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clients — Omega Pharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --omega-green: #00713e; --omega-dark: #01291a; --omega-gold: #f5a800; --sidebar-w: 260px; }
        body { background: #f0f5f2; font-family: 'Inter', sans-serif; margin: 0; }
        
        /* Layout */
        .main-wrapper { margin-left: var(--sidebar-w); padding: 30px; min-height: 100vh; }
        .omega-topbar { 
            background: white; padding: 15px 30px; margin-left: var(--sidebar-w); 
            display: flex; justify-content: space-between; align-items: center; 
            border-bottom: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        /* Components */
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e9ecef; }
        .btn-omega { background: var(--omega-green); color: white; border-radius: 8px; font-weight: 600; border: none; }
        .btn-omega:hover { background: var(--omega-dark); color: var(--omega-gold); }
        .badge-mutuelle { background: #e3f2fd; color: #0d6efd; border: 1px solid #cfe2ff; font-weight: 600; font-size: 0.75rem; }
        
        .search-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .table thead th { background: #f8faf9; color: #495057; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-top: none; }
    </style>
</head>
<body>

<?php include dirname(__DIR__,2).'/templates/partials/sidebar.php'; ?>

<div class="omega-topbar">
    <h4 class="fw-bold m-0" style="font-family: 'Montserrat', sans-serif;">
        <i class="bi bi-people-fill text-success me-2"></i>GESTION CLIENTS
    </h4>
    <div class="d-flex gap-2">
        <button class="btn btn-dark px-4 fw-bold shadow-sm" style="border-radius: 10px;" onclick="newClient()">
            <i class="bi bi-plus-lg me-2"></i>NOUVEAU CLIENT
        </button>
    </div>
</div>

<main class="main-wrapper">
    <div class="card search-card p-3 mb-4">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="s" 
                           value="<?=htmlspecialchars($s)?>" placeholder="Rechercher par Nom, Téléphone ou CNI...">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-omega w-100" type="submit">FILTRER</button>
            </div>
            <?php if($s): ?>
            <div class="col-md-1">
                <a href="?" class="btn btn-light w-100 border text-muted"><i class="bi bi-x-lg"></i></a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="text-muted small fw-bold"><i class="bi bi-info-circle me-1"></i> <?=count($clients)?> CLIENTS ENREGISTRÉS</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Exporter</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                </ul>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Identité / Adresse</th>
                        <th>Contact & CNI</th>
                        <th>Mutuelle</th>
                        <th class="text-end">Crédit Max</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $c): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?=htmlspecialchars($c['nom'].' '.$c['prenom'])?></div>
                            <div class="small text-muted text-truncate" style="max-width: 200px;">
                                <i class="bi bi-geo-alt"></i> <?=htmlspecialchars($c['adresse']??'Dakar, Sénégal')?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-success small"><i class="bi bi-phone"></i> <?=htmlspecialchars($c['telephone'])?></div>
                            <div class="text-muted small" style="font-family: monospace;">CNI: <?=htmlspecialchars($c['cni']??'-')?></div>
                        </td>
                        <td>
                            <span class="badge badge-mutuelle"><?=htmlspecialchars($c['mutuelle']??'AUCUNE')?></span>
                        </td>
                        <td class="text-end fw-bold text-primary">
                            <?=Helper::fcfa($c['credit_autorise'])?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light border shadow-sm" 
                                    onclick='editClient(<?=json_encode($c)?>)' title="Modifier">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(!$clients): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-people style='font-size: 3rem;' d-block mb-3"></i>
                            Aucun client trouvé pour cette recherche.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-dark text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold" id="mtitle">Nouveau Client</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="cliForm" class="row g-3">
                    <input type="hidden" id="cli_id">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">NOM *</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="cli_nom" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">PRÉNOM</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="cli_prenom">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">TÉLÉPHONE *</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="cli_tel" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">CNI</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="cli_cni">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">ADRESSE</label>
                        <textarea class="form-control" id="cli_adresse" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">MUTUELLE / IPM</label>
                        <select class="form-select form-control-lg fs-6" id="cli_mutuelle">
                            <option value="">— AUCUNE —</option>
                            <option value="IPM">IPM</option>
                            <option value="IPRES">IPRES</option>
                            <option value="CSS">CSS</option>
                            <option value="MSAS">MSAS</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">CRÉDIT AUTORISÉ (FCFA)</label>
                        <input type="number" class="form-control form-control-lg fs-6 text-primary fw-bold" id="cli_credit" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">ANNULER</button>
                <button type="button" class="btn btn-success px-4 fw-bold shadow" onclick="saveCli()">ENREGISTRER</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let modalInstance = new bootstrap.Modal(document.getElementById('clientModal'));

function newClient() {
    document.getElementById('cliForm').reset();
    document.getElementById('cli_id').value = '';
    document.getElementById('mtitle').textContent = 'AJOUTER UN CLIENT';
    modalInstance.show();
}

function editClient(c) {
    document.getElementById('cli_id').value = c.id;
    document.getElementById('cli_nom').value = c.nom;
    document.getElementById('cli_prenom').value = c.prenom;
    document.getElementById('cli_tel').value = c.telephone;
    document.getElementById('cli_cni').value = c.cni;
    document.getElementById('cli_adresse').value = c.adresse;
    document.getElementById('cli_mutuelle').value = c.mutuelle;
    document.getElementById('cli_credit').value = c.credit_autorise;
    document.getElementById('mtitle').textContent = 'MODIFIER LE CLIENT';
    modalInstance.show();
}

async function saveCli() {
    const id = document.getElementById('cli_id').value;
    const data = {
        nom: document.getElementById('cli_nom').value,
        prenom: document.getElementById('cli_prenom').value,
        telephone: document.getElementById('cli_tel').value,
        cni: document.getElementById('cli_cni').value,
        adresse: document.getElementById('cli_adresse').value,
        mutuelle: document.getElementById('cli_mutuelle').value,
        credit_autorise: parseFloat(document.getElementById('cli_credit').value) || 0
    };
    
    const url = id ? `clients_api.php?action=update&id=${id}` : 'clients_api.php?action=create';
    
    try {
        const r = await fetch(url, { 
            method: 'POST', 
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data) 
        });
        const res = await r.json();
        if(res.success) location.reload(); else alert("Erreur : " + res.message);
    } catch(e) {
        alert("Erreur réseau lors de l'enregistrement");
    }
}
</script>
</body>
</html>
