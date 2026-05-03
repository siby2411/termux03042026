<?php
/**
 * API REST Médicaments — PharmaSen (Accès Direct Table)
 */

require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Helper.php';
require_once dirname(__DIR__, 2) . '/core/Database.php'; // On utilise Database en direct

header('Content-Type: application/json; charset=utf-8');
Auth::check();

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        // ── LISTE DIRECTE ─────────────────────────────────────
        case 'list':
            $search = Helper::sanitize($_GET['search'] ?? '');
            $cat_id = (int)($_GET['categorie_id'] ?? 0);
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $per    = 25;
            $offset = ($page - 1) * $per;

            $sql = "SELECT m.*, c.libelle AS categorie 
                    FROM medicaments m 
                    LEFT JOIN categories_medicaments c ON c.id = m.categorie_id 
                    WHERE m.actif = 1";
            $params = [];

            if ($search) {
                $sql .= " AND (m.denomination LIKE ? OR m.nom_commercial LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($cat_id > 0) {
                $sql .= " AND m.categorie_id = ?";
                $params[] = $cat_id;
            }

            $sql .= " ORDER BY m.denomination ASC LIMIT $per OFFSET $offset";
            
            $rows = Database::query($sql, $params);
            Helper::jsonResponse(true, ['rows' => $rows]);
            break;

        // ── RECHERCHE POS (POINT DE VENTE) ────────────────────
        case 'pos_search':
            $q = Helper::sanitize($_GET['q'] ?? '');
            if (strlen($q) < 2) Helper::jsonResponse(true, []);

            $sql = "SELECT id, denomination, prix_vente_ttc, stock_actuel 
                    FROM medicaments 
                    WHERE actif = 1 AND stock_actuel > 0 
                    AND (denomination LIKE ? OR code_barre = ?) 
                    LIMIT 20";
            
            $results = Database::query($sql, ["%$q%", $q]);
            Helper::jsonResponse(true, $results);
            break;

        // ── CRÉATION DIRECTE ──────────────────────────────────
        case 'create':
            Auth::requireRole('admin', 'pharmacien');
            if ($method !== 'POST') Helper::jsonResponse(false, null, 'Méthode invalide');
            
            $d = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $sql = "INSERT INTO medicaments (denomination, prix_achat_ht, prix_vente_ht, stock_actuel, categorie_id) 
                    VALUES (?, ?, ?, ?, ?)";
            
            Database::execute($sql, [
                $d['denomination'],
                $d['prix_achat_ht'] ?? 0,
                $d['prix_vente_ht'] ?? 0,
                $d['stock_actuel'] ?? 0,
                $d['categorie_id'] ?? null
            ]);
            
            Helper::jsonResponse(true, ['id' => Database::lastId()], 'Médicament ajouté');
            break;

        // ── VUES PRÉ-DÉFINIES (TRÈS PRATIQUE) ─────────────────
        case 'stock_critique':
            // Utilisation directe de ta vue MariaDB 'v_stock_critique'
            $data = Database::query("SELECT * FROM v_stock_critique");
            Helper::jsonResponse(true, $data);
            break;

        default:
            Helper::jsonResponse(false, null, 'Action non reconnue');
            break;
    }
} catch (Throwable $e) {
    Helper::jsonResponse(false, null, $e->getMessage());
}
