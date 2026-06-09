<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Import relevé bancaire";
$page_icon = "cloud-upload";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_releve'])) {
    $fichier = $_FILES['fichier_releve']['tmp_name'];
    $compte_bancaire = (int)$_POST['compte_bancaire'];
    $lignes_importees = 0;
    
    if (($handle = fopen($fichier, "r")) !== FALSE) {
        // Lecture CSV (format: date,libelle,montant,type)
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 3) {
                $date = date('Y-m-d', strtotime($data[0]));
                $libelle = $data[1];
                $montant = (float)$data[2];
                $type = $data[3] ?? ($montant > 0 ? 'CREDIT' : 'DEBIT');
                
                if ($montant > 0) {
                    // Opération crédit (encaissement)
                    $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'RAPPROCHEMENT')");
                    $stmt->execute([$date, $libelle, $compte_bancaire, 703, $montant, 'IMP-' . date('YmdHis')]);
                } else {
                    // Opération débit (décaissement)
                    $montant_abs = abs($montant);
                    $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'RAPPROCHEMENT')");
                    $stmt->execute([$date, $libelle, 601, $compte_bancaire, $montant_abs, 'IMP-' . date('YmdHis')]);
                }
                $lignes_importees++;
            }
        }
        fclose($handle);
        $message = "✅ $lignes_importees opérations importées avec succès depuis le relevé bancaire";
    } else {
        $error = "Impossible d'ouvrir le fichier";
    }
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-cloud-upload"></i> Import automatique des relevés bancaires</h5>
                <small>Format CSV attendu: date,libellé,montant,type</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Format du fichier CSV :</strong><br>
                    <code>2026-01-15,Virement client,1250000,CREDIT</code><br>
                    <code>2026-01-20,Paiement fournisseur,-500000,DEBIT</code>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label>Compte bancaire</label>
                        <select name="compte_bancaire" class="form-select" required>
                            <option value="521">521 - Banque générale</option>
                            <option value="5211">5211 - Banque CFA</option>
                            <option value="5212">5212 - Compte Euro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Fichier relevé (CSV)</label>
                        <input type="file" name="fichier_releve" class="form-control" accept=".csv" required>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn-omega">
                            <i class="bi bi-cloud-upload"></i> Importer et rapprocher
                        </button>
                    </div>
                </form>
                
                <div class="mt-4">
                    <h6><i class="bi bi-download"></i> Télécharger le modèle CSV</h6>
                    <a href="modele_releve.csv" class="btn btn-sm btn-outline-primary">📄 Modèle CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
