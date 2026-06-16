<?php
require_once 'db_connect_ecole.php';
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

$conn = db_connect_ecole();
$paiement_id = $_GET['id'] ?? 0;
$res = $conn->query("SELECT p.*, e.nom, e.prenom, e.code_etudiant FROM paiements_scolarite p JOIN etudiants e ON p.etudiant_id = e.id WHERE p.id = $paiement_id")->fetch_assoc();

$url_verification = "http://127.0.0.1:8000/verifier_recu.php?id=" . $res['id'];
$qr_code_url = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=" . urlencode($url_verification);

$html = "
<style>
    body { font-family: 'Arial', sans-serif; }
    .header { border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
    .title { color: #2c3e50; font-size: 20px; font-weight: bold; }
    .amount { font-size: 22px; color: #2980b9; font-weight: bold; }
</style>
<div class='header'>
    <div style='float:left;'><img src='{$qr_code_url}' width='80'></div>
    <div class='title' style='margin-left: 100px;'>OMEGA INFORMATIQUE CONSULTING</div>
    <p style='margin-left: 100px;'>REÇU N°: REC-2026-{$res['id']} | Date: {$res['date_paiement']}</p>
</div>
<br>
<table width='100%'>
    <tr><td><strong>Étudiant :</strong> {$res['nom']} {$res['prenom']}</td></tr>
    <tr><td><strong>Code :</strong> {$res['code_etudiant']}</td></tr>
    <tr><td><br><strong>Montant Payé :</strong> <span class='amount'>" . number_format($res['montant_verse'], 0, ' ', ' ') . " FCFA</span></td></tr>
</table>
<div style='margin-top:50px; text-align:right;'>Signature de l'Agent Comptable</div>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Recu_OMEGA_{$res['id']}.pdf");
