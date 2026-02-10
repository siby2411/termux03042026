<?php
// /var/www/piece_auto/public/modules/export_ventes.php
require_once __DIR__ . '/../../config/Database.php';

// Connexion
$database = new Database();
$db = $database->getConnection();

// Nom du fichier avec la date du jour
$filename = "Export_Ventes_" . date('Y-m-d_H-i') . ".csv";

// Entêtes HTTP pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Création du pointeur de fichier pour la sortie
$output = fopen('php://output', 'w');

// Ajout du BOM UTF-8 pour qu'Excel reconnaisse les accents immédiatement
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Entêtes des colonnes dans le fichier Excel
fputcsv($output, [
    'ID Vente', 
    'Date', 
    'Client', 
    'Email Client',
    'Montant Total (EUR)', 
    'Statut'
], ';');

try {
    // Requête pour récupérer toutes les ventes avec les infos clients
    $query = "SELECT cv.id_commande_vente, cv.date_commande, cv.total_commande, 
                     c.nom, c.prenom, c.email
              FROM COMMANDE_VENTE cv
              JOIN CLIENTS c ON cv.id_client = c.id_client
              ORDER BY cv.date_commande DESC";
    
    $stmt = $db->query($query);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id_commande_vente'],
            date('d/m/Y H:i', strtotime($row['date_commande'])),
            $row['prenom'] . ' ' . $row['nom'],
            $row['email'],
            number_format($row['total_commande'], 2, ',', ''), // Format numérique Excel
            'Payé' // À adapter selon vos futurs statuts
        ], ';');
    }

} catch (Exception $e) {
    // En cas d'erreur, on ne peut plus changer les headers, donc on arrête le script
    exit();
}

fclose($output);
exit();
