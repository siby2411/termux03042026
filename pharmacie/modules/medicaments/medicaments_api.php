<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';

// Vérification de sécurité
Auth::check();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'create') {
        // Récupération des données JSON envoyées par le JS
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['denomination'])) {
            echo json_encode(['success' => false, 'message' => 'Données invalides ou dénomination manquante.']);
            exit;
        }

        // Préparation de l'insertion
        $sql = "INSERT INTO medicaments (
                    denomination, code_barre, categorie_id, forme, 
                    dosage, prix_vente_ttc, stock_actuel, stock_min, actif
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $params = [
            $data['denomination'],
            $data['code_barre'] ?? null,
            !empty($data['categorie_id']) ? (int)$data['categorie_id'] : null,
            $data['forme'] ?? 'comprimé',
            $data['dosage'] ?? '',
            (float)($data['prix_vente_ttc'] ?? 0),
            (int)($data['stock_actuel'] ?? 0),
            (int)($data['stock_min'] ?? 5)
        ];

        Database::query($sql, $params);

        echo json_encode(['success' => true, 'message' => 'Médicament ajouté avec succès !']);
    } 
    
    elseif ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            // On fait un "soft delete" en passant actif à 0
            Database::query("UPDATE medicaments SET actif = 0 WHERE id = ?", [$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID invalide.']);
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
