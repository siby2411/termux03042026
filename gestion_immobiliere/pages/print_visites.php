<?php
$date_jour = date('Y-m-d');
$visites = $pdo->prepare("
    SELECT r.*, p.nom as p_nom, p.telephone as p_tel, i.titre as i_titre, z.nom as zone_nom
    FROM rendez_vous r 
    JOIN prospects p ON r.prospect_id = p.id 
    JOIN immeubles i ON r.immeuble_id = i.id 
    JOIN zones_geographiques z ON i.zone_id = z.id
    WHERE DATE(r.date_heure) = ?
    ORDER BY r.date_heure ASC
");
$visites->execute([$date_jour]);
$data = $visites->fetchAll();
?>

<div class="no-print" style="margin-bottom: 20px;">
    <button onclick="window.print()" class="btn btn-primary">🖨️ Lancer l'impression</button>
    <a href="?page=visites" class="btn">Retour</a>
</div>

<div class="print-area" style="background: white; padding: 40px; border: 1px solid #eee;">
    <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <div>
            <h1 style="margin:0; color: #d4af37;">OMEGA IMMO</h1>
            <p>Dakar, Sénégal</p>
        </div>
        <div style="text-align: right;">
            <h2 style="margin:0;">FEUILLE DE ROUTE</h2>
            <p>Date : <b><?= date('d/m/Y') ?></b></p>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">
        <thead>
            <tr style="background: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Heure</th>
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Prospect / Tel</th>
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Bien / Zone</th>
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Notes / Check</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $v): ?>
            <tr>
                <td style="border: 1px solid #ddd; padding: 12px;"><b><?= date('H:i', strtotime($v['date_heure'])) ?></b></td>
                <td style="border: 1px solid #ddd; padding: 12px;"><?= $v['p_nom'] ?><br><small><?= $v['p_tel'] ?></small></td>
                <td style="border: 1px solid #ddd; padding: 12px;"><?= $v['i_titre'] ?><br><small><?= $v['zone_nom'] ?></small></td>
                <td style="border: 1px solid #ddd; padding: 12px; height: 50px;">[ ]</td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($data)) echo "<tr><td colspan='4' style='text-align:center; padding:20px;'>Aucune visite prévue aujourd'hui.</td></tr>"; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 50px; font-size: 10px; color: #888;">
        Généré par OMEGA IMMO System - <?= date('H:i:s') ?>
    </div>
</div>

<style>
@media print {
    .no-print, .sidebar, .topbar { display: none !important; }
    .main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    body { background: white !important; }
    .print-area { border: none !important; padding: 0 !important; }
}
</style>
