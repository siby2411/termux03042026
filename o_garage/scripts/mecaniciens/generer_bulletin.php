<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

$id = $_GET['id'] ?? null;
$periode = $_GET['periode'] ?? date('Y-m');

// 1. Infos du mécanicien
$stmt = $db->prepare("SELECT * FROM personnel WHERE id_personnel = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

// 2. Calcul du CA réalisé par ce mécanicien ce mois-ci
$sql_ca = "SELECT SUM(cout_main_doeuvre) as total_mo, COUNT(*) as nb 
            FROM fiches_intervention 
            WHERE (id_mec_1 = ? OR id_mec_2 = ?) 
            AND statut = 'Terminé' 
            AND date_entree LIKE ?";
$q_ca = $db->prepare($sql_ca);
$q_ca->execute([$id, $id, $periode . '%']);
$stats = $q_ca->fetch();

$ca_mo = $stats['total_mo'] ?? 0;
$commission = $ca_mo * 0.10; // 10% de commission
$salaire_fixe = 150000; // Base standard Dakar
$total_net = $salaire_fixe + $commission;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BULLETIN_<?= $m['code_interne'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .bulletin-sheet { background: white; max-width: 850px; margin: 30px auto; padding: 50px; border: 1px solid #ddd; }
        .table-paye th { background: #f1f1f1; }
        @media print { .no-print { display: none; } .bulletin-sheet { border: none; margin: 0; padding: 20px; } }
    </style>
</head>
<body>

<div class="container no-print mt-4 text-center">
    <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> Imprimer / Sauvegarder PDF</button>
    <a href="paie.php" class="btn btn-secondary btn-lg">Retour</a>
</div>

<div class="bulletin-sheet shadow-sm">
    <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h3 class="text-primary fw-bold">OMEGA TECH GARAGE</h3>
            <p class="small text-muted">Dakar, Sénégal<br>Service Ressources Humaines</p>
        </div>
        <div class="text-end">
            <h4 class="fw-bold">BULLETIN DE PAIE</h4>
            <p class="mb-0">Période : <?= $periode ?></p>
            <p class="small">Matricule : <?= $m['code_interne'] ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h6><strong>SALARIÉ :</strong></h6>
            <h5><?= $m['nom_complet'] ?></h5>
            <p class="text-muted small">Poste : <?= $m['role'] ?></p>
        </div>
    </div>

    <table class="table table-bordered table-paye">
        <thead>
            <tr>
                <th>Éléments de Rémunération</th>
                <th class="text-end">Base</th>
                <th class="text-end">Montant (F)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Salaire de Base Mensuel</td>
                <td class="text-end">1</td>
                <td class="text-end"><?= number_format($salaire_fixe, 0, ',', ' ') ?></td>
            </tr>
            <tr>
                <td>Commission sur Interventions (10%)<br><small class='text-muted'>Sur un CA MO de <?= number_format($ca_mo, 0, ',', ' ') ?> F (<?= $stats['nb'] ?> fiches)</small></td>
                <td class="text-end">10%</td>
                <td class="text-end"><?= number_format($commission, 0, ',', ' ') ?></td>
            </tr>
            <tr class="fw-bold table-active">
                <td colspan="2" class="text-end py-3">NET À PAYER (FCFA)</td>
                <td class="text-end py-3 fs-5 text-success"><?= number_format($total_net, 0, ',', ' ') ?> F</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-5 pt-5 d-flex justify-content-between text-center">
        <div style="width: 200px; border-top: 1px solid #000;">Signature Employeur</div>
        <div style="width: 200px; border-top: 1px solid #000;">Signature Salarié</div>
    </div>
</div>

</body>
</html>
