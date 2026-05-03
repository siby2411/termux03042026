<?php
// Recherche multi-critères : élève, école, bus, statut paiement
$sql = "SELECT e.nom_eleve, e.prenom_eleve, ec.nom_ecole, b.immatriculation, p.montant, p.statut_paiement
        FROM eleves e
        JOIN ecoles ec ON e.id_ecole = ec.id_ecole
        LEFT JOIN affectations a ON e.id_eleve = a.id_eleve
        LEFT JOIN bus b ON a.id_bus = b.id_bus
        LEFT JOIN paiements p ON e.id_eleve = p.id_eleve
        WHERE 1=1";
        
if(!empty($_GET['nom_eleve'])) $sql .= " AND e.nom_eleve LIKE '%".$_GET['nom_eleve']."%'";
if(!empty($_GET['id_ecole'])) $sql .= " AND e.id_ecole = ".intval($_GET['id_ecole']);
if(!empty($_GET['statut_paiement'])) $sql .= " AND p.statut_paiement = '".$_GET['statut_paiement']."'";
if(!empty($_GET['bus_id'])) $sql .= " AND b.id_bus = ".intval($_GET['bus_id']);
?>
