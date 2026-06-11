<?php
$css_path = '../../assets/css/style.css';
echo "Chemin théorique du CSS : " . realpath($css_path) . "<br>";
echo "Le fichier existe-t-il ? " . (file_exists($css_path) ? "OUI" : "NON");
?>
