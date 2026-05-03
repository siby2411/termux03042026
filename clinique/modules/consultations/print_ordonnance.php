<?php
include '../../config/database.php';
$id = $_GET['id'] ?? null;
$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, p.nom, p.prenom, p.sexe, p.date_naissance, pers.nom as med_nom, pers.prenom as med_prenom 
          FROM consultations c 
          JOIN patients p ON c.id_patient = p.id 
          JOIN personnel pers ON c.id_medecin = pers.id 
          WHERE c.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$c = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordonnance - <?= $c['nom'] ?></title>
    <style>
        body { font-family: 'Courier', monospace; padding: 50px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
        .content { margin-top: 20px; min-height: 300px; }
        .footer { margin-top: 50px; text-align: right; }
        .stamp { margin-top: 20px; border: 1px solid #ccc; width: 150px; height: 80px; float: right; text-align: center; font-size: 0.8em; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Imprimer</button> <a href="../rendezvous/list.php">Retour</a></div>
    
    <div class="header">
        <h1>OMÉGA CLINIQUE</h1>
        <p>Service de Médecine Générale | Tel: +221 33 000 00 00</p>
    </div>

    <p style="text-align: right;">Dakar, le <?= date('d/m/Y', strtotime($c['date_creation'])) ?></p>
    
    <p><strong>PATIENT :</strong> <?= strtoupper($c['nom']) ?> <?= $c['prenom'] ?></p>
    <hr>
    
    <div class="content">
        <h3 style="text-decoration: underline;">ORDONNANCE</h3>
        <p style="white-space: pre-line; font-size: 1.2em; line-height: 1.6;">
            <?= htmlspecialchars($c['prescriptions']) ?>
        </p>
    </div>

    <div class="footer">
        <p><strong>Dr. <?= $c['med_prenom'] ?> <?= $c['med_nom'] ?></strong></p>
        <div class="stamp"><br>Cachet et Signature</div>
    </div>
</body>
</html>
