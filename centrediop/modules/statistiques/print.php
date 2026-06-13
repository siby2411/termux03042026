<?php
// print.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <style>
        @media print {
            .no-print { display: none; }
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #333; padding: 10px; text-align: left; }
            .header { text-align: center; margin-bottom: 30px; }
        }
        .btn-print { padding: 10px 20px; background: #d9534f; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <button class="no-print btn-print" onclick="window.print()">Télécharger en PDF / Imprimer</button>
    
    <div class="header">
        <h1>Rapport Financier du Centre Mamadou Diop</h1>
        <p>Date d'édition : <?= date('d/m/Y') ?></p>
    </div>
    
    </body>
</html>
