<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Recherche de formules financières";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';

// Définir les domaines avec leurs tables et champs associés
$domaines = [
    'Analyse financière (SIG, ratios)' => [
        'description' => 'Indicateurs issus du SIG et des ratios financiers.',
        'tables' => [
            'RATIOS_FINANCIERS' => [
                'label' => 'Ratios financiers (liquidité, rentabilité, endettement)',
                'champs' => ['rentabilite_economique', 'rentabilite_financiere', 'rentabilite_commerciale', 'ratio_liquidite_generale', 'ratio_autonomie_financiere', 'besoin_fonds_roulement', 'fonds_roulement', 'tresorerie_nette', 'capacite_autofinancement']
            ],
            'ANALYSES_FINANCIERES' => [
                'label' => 'Soldes intermédiaires de gestion (SIG)',
                'champs' => ['indicateur', 'valeur']
            ]
        ]
    ],
    'Évaluation VAN / TRI' => [
        'description' => 'Calculs de rentabilité de projets (VAN, TRI, indice de profitabilité).',
        'tables' => [
            'CALCULS_VAN_TRI' => [
                'label' => 'VAN / TRI par projet',
                'champs' => ['projet_nom', 'investissement_initial', 'duree_vie', 'taux_actualisation', 'van_calculee', 'tri_calcule', 'indice_rentabilite']
            ]
        ]
    ],
    'Coût du capital (WACC, MEDAF)' => [
        'description' => 'Coût des capitaux propres, coût de la dette, WACC.',
        'tables' => [
            'WACC_CALCULS' => [
                'label' => 'WACC (coût moyen pondéré du capital)',
                'champs' => ['taux_sans_risque', 'prime_risque', 'beta', 'cout_capitaux_propres', 'cout_dette', 'wacc']
            ],
            'COUT_CAPITAL' => [
                'label' => 'Coût du capital détaillé',
                'champs' => ['taux_sans_risque', 'prime_risque', 'beta', 'cout_capitaux_propres', 'cout_dette', 'wacc']
            ]
        ]
    ],
    'Effet de levier' => [
        'description' => 'Relation entre rentabilité économique et financière.',
        'tables' => [
            'EFFET_LEVIER' => [
                'label' => 'Effet de levier financier',
                'champs' => ['rentabilite_economique', 'rentabilite_financiere', 'taux_endettement', 'effet_leverage', 'interpretation']
            ]
        ]
    ],
    'Évaluation d’entreprise' => [
        'description' => 'Modèles d’évaluation (Gordon-Shapiro, Modigliani-Miller).',
        'tables' => [
            'GORDON_SHAPIRO' => [
                'label' => 'Modèle de Gordon-Shapiro',
                'champs' => ['dividende_actuel', 'taux_croissance', 'cout_capitaux_propres', 'valeur_entreprise', 'valeur_action', 'nombre_actions']
            ],
            'MODIGLIANI_MILLER' => [
                'label' => 'Théorèmes de Modigliani-Miller',
                'champs' => ['taux_sans_risque', 'prime_risque_economique', 'prime_risque_financier', 'cout_capitaux_propres', 'cout_dette', 'ratio_endettement', 'wacc_sans_dette', 'wacc_avec_dette', 'valeur_entreprise_sans_dette', 'valeur_entreprise_avec_dette']
            ]
        ]
    ]
];

$domaine_choisi = isset($_GET['domaine']) ? $_GET['domaine'] : 'Analyse financière (SIG, ratios)';
$table_choisie = isset($_GET['table']) ? $_GET['table'] : null;
$exercice = isset($_GET['exercice']) ? intval($_GET['exercice']) : date('Y');
$resultat = null;

if ($table_choisie && isset($domaines[$domaine_choisi]['tables'][$table_choisie])) {
    $infos = $domaines[$domaine_choisi]['tables'][$table_choisie];
    $sql = "SELECT * FROM $table_choisie";
    $conditions = [];
    // Ajout de filtre par exercice si la colonne existe
    $stmtTest = $pdo->query("SHOW COLUMNS FROM $table_choisie LIKE 'exercice'");
    if ($stmtTest->rowCount() > 0) {
        $conditions[] = "exercice = $exercice";
    }
    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .formule-card { border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; }
        .table-responsive th { background-color: #e9ecef; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">Domaines</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($domaines as $dom => $infos): ?>
                    <a href="?domaine=<?= urlencode($dom) ?>" class="list-group-item list-group-item-action <?= $dom == $domaine_choisi ? 'active' : '' ?>">
                        <?= htmlspecialchars($dom) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3><i class="bi bi-calculator"></i> <?= htmlspecialchars($domaine_choisi) ?></h3>
                    <p><?= $domaines[$domaine_choisi]['description'] ?? '' ?></p>
                </div>
                <div class="card-body">
                    <!-- Sélecteur de table (formule) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Choisissez une table / indicateur :</label>
                        <div class="row">
                            <?php foreach ($domaines[$domaine_choisi]['tables'] as $table => $infosTable): ?>
                            <div class="col-md-6 mb-2">
                                <a href="?domaine=<?= urlencode($domaine_choisi) ?>&table=<?= urlencode($table) ?>&exercice=<?= $exercice ?>" class="btn btn-outline-primary w-100 text-start">
                                    <?= htmlspecialchars($infosTable['label']) ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Filtre exercice (si applicable) -->
                    <div class="mb-3">
                        <form method="get" class="row g-2 align-items-end">
                            <input type="hidden" name="domaine" value="<?= htmlspecialchars($domaine_choisi) ?>">
                            <input type="hidden" name="table" value="<?= htmlspecialchars($table_choisie) ?>">
                            <div class="col-auto">
                                <label class="form-label">Exercice :</label>
                                <input type="number" name="exercice" class="form-control" value="<?= $exercice ?>" style="width: 100px;">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-secondary">Filtrer</button>
                            </div>
                        </form>
                    </div>

                    <!-- Résultat de la requête -->
                    <?php if ($table_choisie && $resultat !== null): ?>
                        <h5 class="mt-3">Données extraites de la table <code><?= htmlspecialchars($table_choisie) ?></code></h5>
                        <?php if (count($resultat) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <?php foreach (array_keys($resultat[0]) as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultat as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $val): ?>
                                            <td><?= htmlspecialchars($val) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">Aucune donnée trouvée pour l'exercice <?= $exercice ?>.</div>
                        <?php endif; ?>
                    <?php elseif ($table_choisie): ?>
                        <div class="alert alert-info">Sélectionnez une table ou un indicateur ci-dessus pour afficher les données.</div>
                    <?php else: ?>
                        <div class="alert alert-secondary">Choisissez une rubrique, puis une table pour visualiser les formules et les valeurs issues de la base de données.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
