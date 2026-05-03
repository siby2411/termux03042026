<?php
// api/search_by_code.php - API pour rechercher par code unique
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$code = $_GET['code'] ?? $_POST['code'] ?? '';

// Fonction pour nettoyer et valider le code
function validateCodeFormat($code) {
    if(preg_match('/^P[0-9]{13}$/', $code)) {
        return 'parent';
    } elseif(preg_match('/^E[0-9]{13}$/', $code)) {
        return 'eleve';
    }
    return false;
}

if(empty($code)) {
    echo json_encode([
        'success' => false,
        'error' => 'Code requis',
        'message' => 'Veuillez fournir un code parent (PXXXXXXXXXXXXX) ou code élève (EXXXXXXXXXXXXX)'
    ]);
    exit();
}

$type = validateCodeFormat($code);

if(!$type) {
    echo json_encode([
        'success' => false,
        'error' => 'Format de code invalide',
        'message' => 'Le code doit être au format PXXXXXXXXXXXXX (13 chiffres après P) ou EXXXXXXXXXXXXX (13 chiffres après E)',
        'code_fourni' => $code
    ]);
    exit();
}

if($type === 'parent') {
    // Recherche parent avec ses élèves
    $stmt = $db->prepare("
        SELECT 
            p.id_parent,
            p.nom,
            p.prenom,
            p.telephone,
            p.code_parent,
            p.email,
            p.adresse_complete,
            p.statut_compte,
            p.date_inscription,
            (SELECT COUNT(*) FROM eleves WHERE id_parent = p.id_parent) as nb_eleves
        FROM parents p
        WHERE p.code_parent = ?
    ");
    $stmt->execute([$code]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($parent) {
        // Récupérer les élèves du parent
        $stmt2 = $db->prepare("
            SELECT 
                e.id_eleve,
                e.nom_eleve,
                e.prenom_eleve,
                e.code_eleve,
                e.classe,
                e.statut_inscription,
                e.point_prise_en_charge,
                ec.nom_ecole,
                ec.horaire_matin,
                ec.horaire_soir,
                (SELECT statut_paiement FROM paiements 
                 WHERE id_eleve = e.id_eleve 
                 ORDER BY date_paiement DESC LIMIT 1) as dernier_paiement_statut
            FROM eleves e
            LEFT JOIN ecoles ec ON e.id_ecole = ec.id_ecole
            WHERE e.id_parent = ?
        ");
        $stmt2->execute([$parent['id_parent']]);
        $eleves = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'type' => 'parent',
            'data' => $parent,
            'eleves' => $eleves,
            'message' => 'Parent trouvé avec ' . count($eleves) . ' élève(s)'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Parent non trouvé',
            'message' => 'Aucun parent ne correspond à ce code',
            'code_recherche' => $code
        ]);
    }
    
} elseif($type === 'eleve') {
    // Recherche élève avec ses informations complètes
    $stmt = $db->prepare("
        SELECT 
            e.id_eleve,
            e.nom_eleve,
            e.prenom_eleve,
            e.code_eleve,
            e.classe,
            e.statut_inscription,
            e.point_prise_en_charge,
            e.date_naissance,
            e.photo_url,
            p.id_parent,
            p.nom as parent_nom,
            p.prenom as parent_prenom,
            p.telephone as parent_telephone,
            p.code_parent,
            p.email as parent_email,
            ec.id_ecole,
            ec.nom_ecole,
            ec.adresse_ecole,
            ec.horaire_matin,
            ec.horaire_soir,
            (SELECT statut_paiement FROM paiements 
             WHERE id_eleve = e.id_eleve 
             ORDER BY date_paiement DESC LIMIT 1) as dernier_paiement_statut,
            (SELECT montant FROM paiements 
             WHERE id_eleve = e.id_eleve 
             ORDER BY date_paiement DESC LIMIT 1) as dernier_paiement_montant,
            (SELECT date_paiement FROM paiements 
             WHERE id_eleve = e.id_eleve 
             ORDER BY date_paiement DESC LIMIT 1) as dernier_paiement_date
        FROM eleves e
        JOIN parents p ON e.id_parent = p.id_parent
        LEFT JOIN ecoles ec ON e.id_ecole = ec.id_ecole
        WHERE e.code_eleve = ?
    ");
    $stmt->execute([$code]);
    $eleve = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($eleve) {
        // Récupérer l'historique des paiements
        $stmt2 = $db->prepare("
            SELECT id_paiement, montant, mois_periode, date_paiement, mode_paiement, statut_paiement
            FROM paiements
            WHERE id_eleve = ?
            ORDER BY date_paiement DESC
            LIMIT 10
        ");
        $stmt2->execute([$eleve['id_eleve']]);
        $historique_paiements = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'type' => 'eleve',
            'data' => $eleve,
            'historique_paiements' => $historique_paiements,
            'message' => 'Élève trouvé'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Élève non trouvé',
            'message' => 'Aucun élève ne correspond à ce code',
            'code_recherche' => $code
        ]);
    }
}
?>
