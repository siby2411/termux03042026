<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Rapprochement bancaire automatique";
$page_icon = "cloud-upload";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['releve'])) {
    $fichier = $_FILES['releve']['tmp_name'];
    $format = $_POST['format'];
    
    if (($handle = fopen($fichier, "r")) !== FALSE) {
        $lignes_importees = 0;
        $ecarts = 0;
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 4) {
                $date = date('Y-m-d', strtotime($data[0]));
                $libelle = $data[1];
                $montant = (float)str_replace(',', '.', $data[2]);
                $reference = $data[3];
                
                // Vérifier si déjà importé
                $check = $pdo->prepare("SELECT id FROM ECRITURES_COMPTABLES WHERE reference_piece = ? AND compte_credite_id = 521");
                $check->execute([$reference]);
                if($check->rowCount() == 0) {
                    if($montant > 0) {
                        $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, 701, ?, ?, 'RAPPROCHEMENT')");
                        $stmt->execute([$date, $libelle, $montant, $reference]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 601, 521, ?, ?, 'RAPPROCHEMENT')");
                        $stmt->execute([$date, $libelle, abs($montant), $reference]);
                    }
                    $lignes_importees++;
                } else {
                    $ecarts++;
                }
            }
        }
        fclose($handle);
        $message = "✅ Import terminé : $lignes_importees lignes importées, $ecarts doublons ignorés.";
    } else {
        $erreurs[] = "Impossible d'ouvrir le fichier";
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-cloud-upload"></i> Import automatique de relevé bancaire</h5>
                <small>Formats supportés : CSV, MT940 (bientôt)</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <strong>📄 Format CSV attendu :</strong><br>
                    date, libellé, montant, référence<br>
                    Exemple : <code>2026-01-15,Virement client,1250000,REF001</code>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-4">
                        <label>Format du fichier</label>
                        <select name="format" class="form-select">
                            <option value="CSV">CSV (Standard)</option>
                            <option value="MT940">MT940 (Bientôt disponible)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Fichier relevé</label>
                        <input type="file" name="releve" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn-omega mt-4">Importer</button>
                    </div>
                </form>
                
                <div class="alert alert-secondary mt-3">
                    <i class="bi bi-download"></i>
                    <a href="modele_releve.csv" class="text-decoration-none">📥 Télécharger le modèle CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
