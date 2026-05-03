<?php
require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$type = $_GET['type'] ?? 'eleves';
$search = $_GET['search'] ?? '';
$ecole = $_GET['ecole'] ?? '';
$bus = $_GET['bus'] ?? '';
$paiement = $_GET['paiement'] ?? '';

if($type == 'eleves') {
    $query = "SELECT e.id_eleve, e.nom_eleve, e.prenom_eleve, e.classe, 
                     ec.nom_ecole, ec.horaire_matin, ec.horaire_soir,
                     p.nom as parent_nom, p.telephone as parent_tel,
                     b.immatriculation,
                     (SELECT statut_paiement FROM paiements WHERE id_eleve = e.id_eleve AND mois_periode = DATE_FORMAT(CURDATE(), '%Y-%m-01') LIMIT 1) as statut_paiement
              FROM eleves e
              JOIN ecoles ec ON e.id_ecole = ec.id_ecole
              JOIN parents p ON e.id_parent = p.id_parent
              LEFT JOIN affectations a ON e.id_eleve = a.id_eleve AND a.date_affectation = CURDATE()
              LEFT JOIN bus b ON a.id_bus = b.id_bus
              WHERE 1=1";
    
    if($search) {
        $query .= " AND (e.nom_eleve LIKE '%$search%' OR e.prenom_eleve LIKE '%$search%' OR b.immatriculation LIKE '%$search%')";
    }
    if($ecole) $query .= " AND e.id_ecole = $ecole";
    if($bus) $query .= " AND b.id_bus = $bus";
    if($paiement) $query .= " AND (SELECT statut_paiement FROM paiements WHERE id_eleve = e.id_eleve AND mois_periode = DATE_FORMAT(CURDATE(), '%Y-%m-01') LIMIT 1) = '$paiement'";
    
    $results = $db->query($query);
    
    echo '<div class="row">';
    while($row = $results->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="col-md-4">';
        echo '<div class="card result-card">';
        echo '<div class="card-body">';
        echo '<h5>' . htmlspecialchars($row['prenom_eleve'] . ' ' . $row['nom_eleve']) . '</h5>';
        echo '<p><i class="fas fa-school"></i> ' . htmlspecialchars($row['nom_ecole']) . '<br>';
        echo '<i class="fas fa-clock"></i> Horaires: ' . $row['horaire_matin'] . ' / ' . $row['horaire_soir'] . '<br>';
        echo '<i class="fas fa-bus"></i> Bus: ' . ($row['immatriculation'] ?? 'Non affecté') . '<br>';
        echo '<i class="fas fa-user-friends"></i> Parent: ' . htmlspecialchars($row['parent_nom']) . ' - ' . $row['parent_tel'] . '<br>';
        echo '<span class="badge badge-paiement-' . ($row['statut_paiement'] ?? 'impaye') . '">' . ($row['statut_paiement'] ?? 'IMPAYÉ') . '</span>';
        echo '</p>';
        echo '<button class="btn btn-sm btn-info" onclick="voirDetailsEleve(' . $row['id_eleve'] . ')"><i class="fas fa-eye"></i> Détails</button>';
        echo '</div></div></div>';
    }
    echo '</div>';
} elseif($type == 'impayes') {
    $query = "SELECT b.immatriculation, COUNT(e.id_eleve) as total_eleves,
                     SUM(CASE WHEN p.statut_paiement = 'impaye' THEN 1 ELSE 0 END) as nb_impayes,
                     GROUP_CONCAT(CONCAT(e.nom_eleve, ' ', e.prenom_eleve) SEPARATOR ', ') as eleves_impayes
              FROM bus b
              JOIN affectations a ON b.id_bus = a.id_bus
              JOIN eleves e ON a.id_eleve = e.id_eleve
              LEFT JOIN paiements p ON e.id_eleve = p.id_eleve AND p.mois_periode = DATE_FORMAT(CURDATE(), '%Y-%m-01')
              GROUP BY b.id_bus
              HAVING nb_impayes > 0";
    
    $results = $db->query($query);
    echo '<table class="table table-striped">';
    echo '<thead><tr><th>Bus</th><th>Total élèves</th><th>Impayés</th><th>Élèves concernés</th><th>Action</th></tr></thead><tbody>';
    while($row = $results->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['immatriculation']}</strong></td>";
        echo "<td>{$row['total_eleves']}</td>";
        echo "<td class='text-danger'><strong>{$row['nb_impayes']}</strong></td>";
        echo "<td>" . htmlspecialchars(substr($row['eleves_impayes'], 0, 50)) . "...</td>";
        echo "<td><button class='btn btn-warning btn-sm' onclick='relancerPaiement(\"{$row['immatriculation']}\")'><i class='fas fa-bell'></i> Relancer</button></td>";
        echo "</tr>";
    }
    echo '</tbody></table>';
}

// JavaScript pour les actions
echo "<script>
function voirDetailsEleve(id) {
    window.location.href = '/transport/modules/eleves/fiche_eleve.php?id=' + id;
}
function relancerPaiement(bus) {
    if(confirm('Envoyer une notification de rappel de paiement pour le bus ' + bus + ' ?')) {
        alert('Notification envoyée aux parents concernés');
    }
}
</script>";
?>
