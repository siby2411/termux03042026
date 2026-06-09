<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Recherche de comptes par classe – SYSCOHADA UEMOA";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';

// Récupérer la classe sélectionnée
$classe_choisie = isset($_GET['classe']) ? trim($_GET['classe']) : '';
$comptes = [];
$classe_info = [
    '1' => ['titre' => 'CLASSE 1 – Capitaux propres', 'couleur' => 'primary', 'icon' => 'bi-bank', 'description' => 'Capitaux propres, emprunts, provisions, subventions'],
    '2' => ['titre' => 'CLASSE 2 – Immobilisations', 'couleur' => 'info', 'icon' => 'bi-building', 'description' => 'Immobilisations incorporelles, corporelles et financières'],
    '3' => ['titre' => 'CLASSE 3 – Stocks', 'couleur' => 'success', 'icon' => 'bi-box-seam', 'description' => 'Stocks, matières premières, produits finis, marchandises'],
    '4' => ['titre' => 'CLASSE 4 – Tiers', 'couleur' => 'warning', 'icon' => 'bi-people', 'description' => 'Clients, fournisseurs, personnel, État, organismes sociaux'],
    '5' => ['titre' => 'CLASSE 5 – Trésorerie', 'couleur' => 'danger', 'icon' => 'bi-cash-stack', 'description' => 'Banques, caisse, VMP, régies d’avances'],
    '6' => ['titre' => 'CLASSE 6 – Charges', 'couleur' => 'secondary', 'icon' => 'bi-cart', 'description' => 'Achats, services, impôts, charges de personnel, financières'],
    '7' => ['titre' => 'CLASSE 7 – Produits', 'couleur' => 'dark', 'icon' => 'bi-graph-up', 'description' => 'Ventes, prestations, subventions, produits financiers'],
    '8' => ['titre' => 'CLASSE 8 – Comptes spéciaux', 'couleur' => 'secondary', 'icon' => 'bi-files', 'description' => 'Amortissements, provisions, dépréciations'],
    '9' => ['titre' => 'CLASSE 9 – Comptabilité analytique', 'couleur' => 'light', 'icon' => 'bi-pie-chart', 'description' => 'Coûts par nature, centre d’analyse, résultats analytiques']
];

if ($classe_choisie) {
    $stmt = $pdo->prepare("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA WHERE LEFT(compte_id, 1) = :classe ORDER BY compte_id");
    $stmt->execute(['classe' => $classe_choisie]);
    $comptes = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .class-card { transition: 0.2s; cursor: pointer; border-left: 5px solid transparent; }
        .class-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .class-card-selected { border-left-color: #0d6efd; background-color: #e8f4fd; }
        .compte-item { transition: 0.1s; cursor: pointer; }
        .compte-item:hover { background-color: #e3f2fd; transform: translateX(3px); }
        .search-highlight { background-color: #fff3cd; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-search"></i> Recherche de comptes par classe – SYSCOHADA UEMOA</h2>
                    <p>Parcourez le plan comptable par classe | 422 comptes disponibles</p>
                </div>
                <div class="card-body">

                    <!-- Grille des classes -->
                    <div class="row mb-4">
                        <?php foreach ($classe_info as $code => $info): ?>
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card class-card <?= ($classe_choisie == $code) ? 'class-card-selected' : '' ?>" onclick="window.location.href='?classe=<?= $code ?>'">
                                <div class="card-body text-center">
                                    <i class="<?= $info['icon'] ?> fs-1 text-<?= $info['couleur'] ?>"></i>
                                    <h5 class="mt-2"><?= $info['titre'] ?></h5>
                                    <small class="text-muted"><?= $info['description'] ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Résultats de la recherche -->
                    <?php if ($classe_choisie): ?>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="bi bi-list-ul"></i> <?= $classe_info[$classe_choisie]['titre'] ?></h4>
                            <div class="d-flex gap-2">
                                <input type="text" id="searchInput" class="form-control" placeholder="Filtrer les comptes..." style="width: 250px;">
                                <span class="badge bg-secondary p-2"><?= count($comptes) ?> comptes</span>
                                <button class="btn btn-sm btn-outline-success" onclick="exporterCSV()"><i class="bi bi-file-earmark-excel"></i> Export CSV</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="comptesTable">
                                <thead class="table-dark">
                                    <tr><th>N° de compte</th><th>Intitulé du compte</th><th>Niveau</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comptes as $c): 
                                        $niveau = strlen(preg_replace('/[^0-9]/', '', $c['compte_id']));
                                        $niveau_label = $niveau <= 2 ? 'Général' : ($niveau <= 4 ? 'Détaillé' : 'Analytique');
                                    ?>
                                    <tr class="compte-item" onclick="copierCompte('<?= htmlspecialchars($c['compte_id']) ?>')">
                                        <td><code class="fw-bold"><?= htmlspecialchars($c['compte_id']) ?></code></td>
                                        <td><?= htmlspecialchars($c['intitule_compte']) ?></td>
                                        <td><span class="badge bg-secondary"><?= $niveau_label ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); copierCompte('<?= htmlspecialchars($c['compte_id']) ?>')">
                                                <i class="bi bi-clipboard"></i> Copier
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center mt-4">
                        <i class="bi bi-info-circle fs-1"></i>
                        <h5>Sélectionnez une classe dans le menu ci-dessus</h5>
                        <p>Parcourez les comptes par catégorie pour retrouver facilement le numéro et l'intitulé d'un compte SYSCOHADA</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Filtrage dynamique
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    let searchTerm = this.value.toLowerCase();
    let rows = document.querySelectorAll('#comptesTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Copier un compte
function copierCompte(compte) {
    navigator.clipboard.writeText(compte).then(() => {
        let btn = event?.target?.closest('tr')?.querySelector('.btn-outline-primary');
        if (btn) {
            let originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copié !';
            setTimeout(() => btn.innerHTML = originalText, 1500);
        } else {
            alert('Compte ' + compte + ' copié dans le presse-papier');
        }
    });
}

// Export CSV
function exporterCSV() {
    let rows = document.querySelectorAll('#comptesTable tbody tr');
    let csv = "N° compte;Intitulé;Niveau\n";
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            let cols = row.querySelectorAll('td');
            csv += `"${cols[0].innerText.trim()}";"${cols[1].innerText.trim()}";"${cols[2].innerText.trim()}"\n`;
        }
    });
    let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    let link = document.createElement('a');
    let url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', 'plan_comptable_classe.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>
<?php include 'inc_footer.php'; ?>
