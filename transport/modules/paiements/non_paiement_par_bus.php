<?php
// Commande CAT / EOF pour extraction rapide (via terminal)
// Mais en PHP :
$sql = "SELECT b.immatriculation, COUNT(e.id_eleve) as total_eleves, 
        SUM(CASE WHEN p.statut_paiement = 'impaye' THEN 1 ELSE 0 END) as impayes
        FROM bus b
        JOIN affectations a ON b.id_bus = a.id_bus
        JOIN eleves e ON a.id_eleve = e.id_eleve
        LEFT JOIN paiements p ON e.id_eleve = p.id_eleve AND p.mois_periode = LAST_DAY(CURRENT_DATE - INTERVAL 1 MONTH)
        GROUP BY b.id_bus
        HAVING impayes > 0";
        
// Pour extraction rapide en CLI (dans Termux) :
// echo "SELECT ..." | mysql -u root -p transport_omega > non_paiement.csv
?>
