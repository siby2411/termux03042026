<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__ . '/../includes/db.php';
use TCPDF;

$stmt = $pdo->query("SELECT pd.intitule_compte,
SUM(CASE WHEN e.compte_debite_id=pd.compte_id THEN e.montant ELSE 0 END) AS debit,
SUM(CASE WHEN e.compte_credite_id=pd.compte_id THEN e.montant ELSE 0 END) AS credit
FROM PLAN_COMPTABLE_UEMOA pd
LEFT JOIN ECRITURES_COMPTABLES e 
  ON pd.compte_id IN (e.compte_debite_id, e.compte_credite_id)
GROUP BY pd.compte_id, pd.intitule_compte
ORDER BY pd.compte_id");
$balance = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$html = '<h3>Balance Comptable</h3><table border="1" cellpadding="4"><tr><th>Compte</th><th>Débit</th><th>Crédit</th><th>Solde</th></tr>';
foreach($balance as $b){
    $html .= '<tr>';
    $html .= '<td>'.$b['intitule_compte'].'</td>';
    $html .= '<td>'.$b['debit'].'</td>';
    $html .= '<td>'.$b['credit'].'</td>';
    $html .= '<td>'.($b['debit']-$b['credit']).'</td>';
    $html .= '</tr>';
}
$html .= '</table>';
$pdf->writeHTML($html,true,false,true,false,'');
$pdf->Output('balance.pdf', 'D');

