<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$consultation_id = filter_var($_GET['consultation_id'], FILTER_VALIDATE_INT);

// 1. Récupérer le patient, la consultation et vérifier s'il existe déjà une facture
$sql = "SELECT c.id, p.nom, p.prenom 
        FROM consultations c 
        JOIN patients p ON c.patient_id = p.id 
        WHERE c.id = ? AND c.statut = 'terminee'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consultation_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $patient = $res->fetch_assoc();
    $nom_complet = $patient['nom'] . ' ' . $patient['prenom'];

    // 2. Calcul du montant
    $calc = $conn->prepare("SELECT SUM(a.prix_consultation) as total 
                            FROM consultation_actes ca 
                            JOIN actes_medicaux a ON ca.acte_id = a.id 
                            WHERE ca.consultation_id = ?");
    $calc->bind_param("i", $consultation_id);
    $calc->execute();
    $montant = $calc->get_result()->fetch_assoc()['total'] ?? 5000.00;

    // 3. Insertion dans 'factures' avec les bons noms de colonnes
    $ins = $conn->prepare("INSERT INTO factures (consultation_id, nom_patient, montant) VALUES (?, ?, ?)");
    $ins->bind_param("isd", $consultation_id, $nom_complet, $montant);
    
    if ($ins->execute()) {
        header("Location: ../consultation/index.php?status=success");
    } else {
        echo "Erreur lors de l'insertion : " . $conn->error;
    }
} else {
    echo "Consultation introuvable ou non terminée.";
}
?>
