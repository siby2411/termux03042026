<?php
// =================================================================================
// MODULE 8 : POINT D'ENTRÉE UNIQUE POUR L'API EXTERNE
// =================================================================================

// 1. CONFIGURATION ET SÉCURITÉ DE BASE
header('Content-Type: application/json'); // Toutes les réponses seront en JSON
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/Database.php'; 

// Fonction utilitaire pour envoyer une réponse JSON
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Simuler la table des clés API (en production, utilisez une table BDD)
$API_KEYS_VALIDES = [
    'ACHAT-FOURN-X' => ['role' => 'Fournisseur_Achats', 'droits' => ['read:stock', 'write:prix']],
    'ANALYSE-BI-Y' => ['role' => 'BI_Externe', 'droits' => ['read:ventes']],
];

// Récupération de la clé API
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
$user_api = $API_KEYS_VALIDES[$api_key] ?? null;

// 2. VÉRIFICATION DE L'AUTHENTIFICATION
if (!$user_api) {
    sendResponse(['error' => 'Authentification échouée. Clé API invalide ou manquante.'], 401);
}

// 3. ROUTAGE DE LA REQUÊTE
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Suppression du chemin de base et du préfixe /api.php
$path = trim(str_replace('/api.php', '', $request_uri), '/'); 
$path_parts = explode('/', $path);

// Le premier segment définit la ressource (e.g., stock, ventes, commandes)
$resource = strtolower($path_parts[0] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// Exemple de routage minimal
if ($resource === 'stock' && $method === 'GET') {
    handleGetStock($pdo, $user_api, $path_parts);
} elseif ($resource === 'prix' && $method === 'POST') {
    handlePostPriceUpdate($pdo, $user_api);
} else {
    sendResponse(['error' => "Ressource ou méthode non supportée: {$method} /{$resource}"], 404);
}


// =================================================================================
// 4. FONCTIONS DE GESTION DES RESSOURCES
// =================================================================================

/**
 * Gère la récupération du stock par SKU pour les systèmes externes.
 */
function handleGetStock($pdo, $user_api, $path_parts) {
    // Vérification des droits spécifiques
    if (!in_array('read:stock', $user_api['droits'])) {
        sendResponse(['error' => 'Accès refusé. Droits de lecture du stock manquants.'], 403);
    }
    
    $sku = $path_parts[1] ?? null; // stock/SKU-A123
    if (!$sku) {
        sendResponse(['error' => 'Référence SKU manquante.'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                P.reference_sku, 
                COALESCE(S.quantite_actuelle, 0) AS stock_disponible,
                P.prix_vente
            FROM PIECES P
            LEFT JOIN STOCK S ON P.id_piece = S.id_piece
            WHERE P.reference_sku = ?
        ");
        $stmt->execute([$sku]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            sendResponse([
                'status' => 'success',
                'sku' => $data['reference_sku'],
                'stock' => (int)$data['stock_disponible'],
                'prix_vente_ref' => (float)$data['prix_vente']
            ]);
        } else {
            sendResponse(['error' => 'Pièce non trouvée.'], 404);
        }

    } catch (PDOException $e) {
        // Loggez l'erreur, ne pas la retourner telle quelle en production
        sendResponse(['error' => 'Erreur interne lors de la récupération des données.'], 500);
    }
}

/**
 * Gère la mise à jour des prix par un fournisseur via POST.
 */
function handlePostPriceUpdate($pdo, $user_api) {
    if (!in_array('write:prix', $user_api['droits'])) {
        sendResponse(['error' => 'Accès refusé. Droits d\'écriture sur les prix manquants.'], 403);
    }

    // Récupération et décodage du corps de la requête JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($data['sku']) || empty($data['nouveau_prix_achat'])) {
        sendResponse(['error' => 'Données JSON invalides ou champs requis manquants (sku, nouveau_prix_achat).'], 400);
    }

    $sku = $data['sku'];
    $nouveau_prix_achat = (float)$data['nouveau_prix_achat'];

    try {
        $stmt = $pdo->prepare("UPDATE PIECES SET prix_achat = ? WHERE reference_sku = ?");
        $stmt->execute([$nouveau_prix_achat, $sku]);
        
        if ($stmt->rowCount() > 0) {
            sendResponse([
                'status' => 'success',
                'message' => "Prix d'achat pour SKU {$sku} mis à jour à {$nouveau_prix_achat} €."
            ]);
        } else {
            sendResponse(['error' => 'Pièce non trouvée pour la mise à jour.'], 404);
        }

    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur interne lors de la mise à jour des données.'], 500);
    }
}

?>
