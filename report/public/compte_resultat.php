<?php
session_start();
// Connexion à la BDD via le fichier centralisé
require_once __DIR__ . '/../includes/db.php';

// Optionnel : chargement des bibliothèques si présentes
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $vendor_ready = true;
} else {
    $vendor_ready = false;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Protection de session
if(!isset($_SESSION['user_id']) && !isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

$periode = $_GET['periode'] ?? date('Y-m-d');

// ---------------------------
// LOGIQUE EXPERT : Récupération groupée par Nature
// ---------------------------
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN e.compte_credite_id LIKE '7%' THEN e.montant ELSE 0 END) as total_produits,
        SUM(CASE WHEN e.compte_debite_id LIKE '6%' THEN e.montant ELSE 0 END) as total_charges,
        SUM(CASE WHEN e.compte_credite_id LIKE '8%' THEN e.montant ELSE 0 END) as produits_hao,
        SUM(CASE WHEN e.compte_debite_id LIKE '8%' THEN e.montant ELSE 0 END) as charges_hao,
        p.classe, p.nature_resultat
    FROM ECRITURES_COMPTABLES e
    JOIN PLAN_COMPTABLE_UEMOA p ON (e.compte_debite_id = p.compte_id OR e.compte_credite_id = p.compte_id)
    WHERE e.date_ecriture <= :periode
    GROUP BY p.classe, p.nature_resultat
");
$stmt->execute(['periode' => $periode]);
$flux = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialisation des rubriques SIG
$chiffre_affaires = 0;
$charges_exploitation = 0;
$produits_financiers = 0;
$charges_financieres = 0;
$resultat_hao = 0;

foreach($flux as $f){
    // Exploitation (Classe 6 et 7 standard)
    if($f['classe'] == 7 && $f['nature_resultat'] == 'EXP') $chiffre_affaires += $f['total_produits'];
    if($f['classe'] == 6 && $f['nature_resultat'] == 'EXP') $charges_exploitation += $f['total_charges'];
    
    // Financier (Nature FIN définie en SQL)
    if($f['nature_resultat'] == 'FIN') {
        $produits_financiers += $f['total_produits'];
        $charges_financieres += $f['total_charges'];
    }
    
    // Hors Activités Ordinaires (Classe 8)
    if($f['classe'] == 8) {
        $resultat_hao += ($f['produits_hao'] - $f['charges_hao']);
    }
}

// Calculs des soldes intermédiaires
$ebe = $chiffre_affaires - $charges_exploitation;
$resultat_financier = $produits_financiers - $charges_financieres;
$resultat_net = $ebe + $resultat_financier + $resultat_hao;

// ---------------------------
// GESTION EXPORT EXCEL
// ---------------------------
if(isset($_GET['export_excel']) && $vendor_ready){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1','RUBRIQUE');
    $sheet->setCellValue('B1','MONTANT');
    
    $rows = [
        ['Chiffre d\'Affaires (Ventes)', $chiffre_affaires],
        ['Charges d\'Exploitation', $charges_exploitation],
        ['EXCÉDENT BRUT D\'EXPLOITATION (EBE)', $ebe],
        ['Résultat Financier', $resultat_financier],
        ['Résultat H.A.O', $resultat_hao],
        ['RÉSULTAT NET DE L\'EXERCICE', $resultat_net]
    ];
    $sheet->fromArray($rows, NULL, 'A2');
    
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Compte_Resultat_Omega.xlsx"');
    $writer->save('php://output');
    exit;
}

include "header.php"; // Inclut le design et la sidebar OMEGA
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class='bx bx-trending-up'></i> Compte de Résultat (SIG)</h2>
        <span class="badge bg-info p-2">Arrêté au : <?= date('d/m/Y', strtotime($periode)) ?></span>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th>Rubriques de Gestion</th><th class="text-end">Montant (F CFA)</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Chiffre d'Affaires</td><td class="text-end"><?= number_format($chiffre_affaires,0,'.',' ') ?></td></tr>
                            <tr class="text-danger"><td>Charges d'Exploitation</td><td class="text-end">- <?= number_format($charges_exploitation,0,'.',' ') ?></td></tr>
                            <tr class="table-primary fw-bold">
                                <td>EXCÉDENT BRUT D'EXPLOITATION (EBE)</td>
                                <td class="text-end"><?= number_format($ebe,0,'.',' ') ?></td>
                            </tr>
                            <tr><td>Résultat Financier</td><td class="text-end"><?= number_format($resultat_financier,0,'.',' ') ?></td></tr>
                            <tr><td>Résultat H.A.O (Exceptionnel)</td><td class="text-end"><?= number_format($resultat_hao,0,'.',' ') ?></td></tr>
                            <tr class="table-success fw-bold" style="font-size: 1.2rem;">
                                <td>RÉSULTAT NET DE L'EXERCICE</td>
                                <td class="text-end"><?= number_format($resultat_net,0,'.',' ') ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        <?php if($vendor_ready): ?>
                            <a href="?export_excel=1&periode=<?= $periode ?>" class="btn btn-success"><i class='bx bx-spreadsheet'></i> Excel</a>
                            <a href="?export_pdf=1&periode=<?= $periode ?>" class="btn btn-danger"><i class='bx bxs-file-pdf'></i> PDF</a>
                        <?php else: ?>
                            <div class="alert alert-warning small">L'exportation (Excel/PDF) nécessite l'installation des dépendances Composer.</div>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-secondary"><i class='bx bx-printer'></i> Imprimer</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5>Note de l'Expert</h5>
                    <p class="small text-muted">Ce compte de résultat est généré dynamiquement à partir des écritures comptables des classes 6, 7 et 8.</p>
                    <hr>
                    <div class="mb-2">
                        <label class="form-label">Changer la période :</label>
                        <form method="GET">
                            <input type="date" name="periode" class="form-control mb-2" value="<?= $periode ?>">
                            <button type="submit" class="btn btn-dark w-100">Calculer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
