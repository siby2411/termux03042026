<?php
session_start();
require_once '../../config/database.php';
$pdo = getPDO();

if (!isset($_GET['consultation_id'])) die("Consultation non spécifiée.");

$id = $_GET['consultation_id'];
$consultation = $pdo->prepare("SELECT c.*, p.nom, p.prenom FROM consultations c JOIN patients p ON c.patient_id = p.id WHERE c.id = ?");
$consultation->execute([$id]);
$c = $consultation->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $montant = $_POST['montant_paye'];
    $mode = $_POST['mode_paiement'];
    
    // Insertion dans 'paiements' : statut 'paye' (selon DESCRIBE)
    $stmt = $pdo->prepare("INSERT INTO paiements (numero_facture, patient_id, consultation_id, caissier_id, montant_total, montant_paye, mode_paiement, statut, date_paiement) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'paye', NOW())");
    $stmt->execute(['FACT-'.time(), $c['patient_id'], $id, 1, $c['prix'], $montant, $mode]);
    
    // Mise à jour de 'consultations' : statut 'payee' (selon votre structure précédente)
    $pdo->prepare("UPDATE consultations SET statut = 'payee' WHERE id = ?")->execute([$id]);
    
    header('Location: index.php?success=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light p-4">
    <h3>Encaisser Patient : <?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></h3>
    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">Prix à payer : <strong><?= number_format($c['prix'], 0, ',', ' ') ?> FCFA</strong></div>
        <input type="number" name="montant_paye" class="form-control mb-3" value="<?= $c['prix'] ?>" required>
        <select name="mode_paiement" class="form-select mb-3">
            <option value="especes">Espèces</option>
            <option value="mobile_money">Mobile Money</option>
            <option value="carte">Carte Bancaire</option>
        </select>
        <button type="submit" class="btn btn-success">Valider Paiement</button>
    </form>
</body>
</html>
