<?php
header('Content-Type: application/json');
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();
$q = $_GET['q'] ?? '';
$res = [];

if(strlen($q) >= 2) {
    $term = "%$q%";

    // 1. Pièces (OMG-...)
    $s = $db->prepare("SELECT * FROM pieces_detachees WHERE libelle LIKE ? OR reference LIKE ? LIMIT 5");
    $s->execute([$term, $term]);
    while($r = $s->fetch()) $res[] = ['type'=>'PIÈCE', 'label'=>$r['libelle'], 'extra'=>$r['reference'].' | '.$r['stock_actuel'].' en stock', 'link'=>'/scripts/pieces/liste_pieces.php', 'class'=>'bg-success'];

    // 2. Clients
    $s = $db->prepare("SELECT * FROM clients WHERE nom LIKE ? OR telephone LIKE ? LIMIT 5");
    $s->execute([$term, $term]);
    while($r = $s->fetch()) $res[] = ['type'=>'CLIENT', 'label'=>$r['prenom'].' '.$r['nom'], 'extra'=>$r['telephone'], 'link'=>'/scripts/clients/liste_clients.php', 'class'=>'bg-primary'];

    // 3. Véhicules
    $s = $db->prepare("SELECT * FROM vehicules WHERE immatriculation LIKE ? OR marque LIKE ? LIMIT 5");
    $s->execute([$term, $term]);
    while($r = $s->fetch()) $res[] = ['type'=>'VÉHICULE', 'label'=>$r['marque'].' '.$r['modele'], 'extra'=>$r['immatriculation'], 'link'=>'/scripts/vehicules/liste_vehicules.php', 'class'=>'bg-warning text-dark'];
    
    // 4. Factures
    $s = $db->prepare("SELECT * FROM factures WHERE numero_facture LIKE ? LIMIT 5");
    $s->execute([$term]);
    while($r = $s->fetch()) $res[] = ['type'=>'FACTURE', 'label'=>$r['numero_facture'], 'extra'=>$r['montant_total'].' FCFA', 'link'=>'/scripts/factures/liste_factures.php', 'class'=>'bg-dark'];
}
echo json_encode($res);
