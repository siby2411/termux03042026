<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Générer numéro de facture
        $query = "SELECT COUNT(*) as count FROM factures WHERE YEAR(date_facture) = YEAR(CURDATE())";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] + 1;
        $numero_facture = 'FACT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Gérer la consultation (peut être NULL)
        $id_consultation = !empty($_POST['id_consultation']) ? $_POST['id_consultation'] : null;
        
        // Commencer une transaction
        $db->beginTransaction();
        
        // Insérer la facture
        $query = "INSERT INTO factures (numero_facture, id_patient, id_consultation, montant_total, statut, mode_paiement, notes) 
                  VALUES (:numero_facture, :id_patient, :id_consultation, :montant_total, :statut, :mode_paiement, :notes)";
        
        $stmt = $db->prepare($query);
        $montant_total = floatval($_POST['montant_total']);
        
        $stmt->bindParam(':numero_facture', $numero_facture);
        $stmt->bindParam(':id_patient', $_POST['id_patient']);
        $stmt->bindParam(':id_consultation', $id_consultation);
        $stmt->bindParam(':montant_total', $montant_total);
        $stmt->bindParam(':statut', $_POST['statut']);
        $stmt->bindParam(':mode_paiement', $_POST['mode_paiement']);
        $stmt->bindParam(':notes', $_POST['notes']);
        
        $stmt->execute();
        $facture_id = $db->lastInsertId();
        
        // Insérer les lignes de facture
        if (isset($_POST['designation']) && is_array($_POST['designation'])) {
            $query = "INSERT INTO lignes_facture (id_facture, designation, quantite, prix_unitaire) 
                      VALUES (:id_facture, :designation, :quantite, :prix_unitaire)";
            $stmt = $db->prepare($query);
            
            for ($i = 0; $i < count($_POST['designation']); $i++) {
                if (!empty($_POST['designation'][$i])) {
                    $quantite = floatval($_POST['quantite'][$i]);
                    $prix_unitaire = floatval($_POST['prix_unitaire'][$i]);
                    
                    $stmt->bindParam(':id_facture', $facture_id);
                    $stmt->bindParam(':designation', $_POST['designation'][$i]);
                    $stmt->bindParam(':quantite', $quantite);
                    $stmt->bindParam(':prix_unitaire', $prix_unitaire);
                    $stmt->execute();
                }
            }
        }
        
        $db->commit();
        
        header("Location: list.php?success=Facture créée avec succès");
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de la création de la facture: " . $e->getMessage();
    }
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les patients
$query = "SELECT id, code_patient, nom, prenom FROM patients ORDER BY nom, prenom";
$stmt = $db->prepare($query);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les consultations (uniquement celles sans facture)
$query = "SELECT c.id, p.nom, p.prenom, c.date_consultation 
          FROM consultations c 
          JOIN patients p ON c.id_patient = p.id 
          LEFT JOIN factures f ON c.id = f.id_consultation 
          WHERE f.id IS NULL 
          ORDER BY c.date_consultation DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

