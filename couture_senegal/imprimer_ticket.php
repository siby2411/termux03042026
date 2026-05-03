<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.mesures
    FROM commandes c 
    JOIN clients cl ON c.client_id = cl.id 
    WHERE c.id = ?
");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) die("Commande introuvable.");
$mesures = json_decode($c['mesures'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket <?= $c['numero_commande'] ?></title>
    <style>
        /* Configuration pour imprimante thermique 80mm */
        @page { margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 72mm; /* Un peu moins que 80 pour les marges */
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5mm 0; }
        .item { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .logo { font-size: 18px; margin-bottom: 2px; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Lancer l'impression
        </button>
        <button onclick="window.close()" style="padding: 10px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Fermer
        </button>
    </div>

    <div class="text-center">
        <div class="logo bold">OMEGA COUTURE</div>
        <div>Dakar, Sénégal</div>
        <div>Tél: +221 33 XXX XX XX</div>
        <div class="bold">BON DE COMMANDE</div>
    </div>

    <div class="line"></div>

    <div class="item"><span class="bold">N°:</span> <span><?= $c['numero_commande'] ?></span></div>
    <div class="item"><span>Date:</span> <span><?= date('d/m/Y', strtotime($c['date_commande'])) ?></span></div>
    <div class="item"><span>Livraison:</span> <span class="bold"><?= date('d/m/Y', strtotime($c['date_livraison'])) ?></span></div>

    <div class="line"></div>

    <div class="bold">CLIENT :</div>
    <div><?= strtoupper($c['nom']) ?> <?= $c['prenom'] ?></div>
    <div>Tel: <?= $c['telephone'] ?></div>

    <div class="line"></div>

    <div class="bold">MESURES PRINCIPALES :</div>
    <?php if ($mesures): ?>
        <?php foreach ($mesures as $label => $val): ?>
            <?php if($val > 0): ?>
                <div class="item"><span><?= ucfirst($label) ?>:</span> <span><?= $val ?> cm</span></div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div>Voir fiche atelier</div>
    <?php endif; ?>

    <div class="line"></div>

    <div class="item bold text-center" style="font-size: 14px;">
        <span>TOTAL :</span> 
        <span><?= number_format($c['total_ttc'], 0, ',', ' ') ?> F</span>
    </div>
    <div class="item">
        <span>Acompte:</span> 
        <span>- <?= number_format($c['acompte_verse'], 0, ',', ' ') ?> F</span>
    </div>
    <div class="item bold" style="border-top: 1px solid #000; padding-top: 2px;">
        <span>RESTE :</span> 
        <span><?= number_format($c['reste_a_payer'], 0, ',', ' ') ?> F</span>
    </div>

    <div class="line"></div>

    <?php if($c['notes']): ?>
        <div class="bold">NOTES :</div>
        <div style="font-style: italic;"><?= nl2br(htmlspecialchars($c['notes'])) ?></div>
        <div class="line"></div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 10px;">
        <p>Merci de votre confiance !<br>Apportez ce ticket pour le retrait.</p>
    </div>

</body>
</html>
