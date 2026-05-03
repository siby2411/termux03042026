<?php
// Version ultra-légère sans dépendances
$html = file_get_contents('offre.html');

// Ajout des styles pour l'impression
$print_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @media print {
            body { margin: 0; padding: 1cm; }
            .nav, .category-filter, .category-badge { display: none; }
            .app-card { break-inside: avoid; page-break-inside: avoid; }
            .section { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>' . $html . '</body>
</html>';

file_put_contents('omega_offre_print.html', $print_html);
echo "✅ Fichier HTML prêt pour impression : omega_offre_print.html\n";
echo "📌 Ouvrez ce fichier dans votre navigateur et faites Ctrl+P > Enregistrer en PDF\n";
?>
