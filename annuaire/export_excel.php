<?php
require('config/db.php');

// Définition des headers pour forcer le téléchargement en Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Historique_Offres_Omega_'.date('m_Y').'.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Récupération des données
$stmt = $pdo->query("SELECT * FROM historique_offres ORDER BY date_generation DESC");
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création du tableau HTML que Excel interprète parfaitement
echo '<table border="1">';
echo '<tr>
        <th style="background-color: #003366; color: white;">ID</th>
        <th style="background-color: #003366; color: white;">CLIENT</th>
        <th style="background-color: #003366; color: white;">TELEPHONE</th>
        <th style="background-color: #003366; color: white;">CATEGORIE</th>
        <th style="background-color: #003366; color: white;">DATE GENERATION</th>
      </tr>';

foreach ($offres as $row) {
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . utf8_decode($row['nom_client']) . '</td>';
    echo '<td>' . $row['telephone_client'] . '</td>';
    echo '<td>' . $row['categorie_client'] . '</td>';
    echo '<td>' . date('d/m/Y H:i', strtotime($row['date_generation'])) . '</td>';
    echo '</tr>';
}
echo '</table>';
