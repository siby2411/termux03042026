#!/bin/bash
# ============================================================
# SCRIPT 05 — MODULES CRUD PHP COMPLETS
# Médicaments (Pharmacie) + Produits (Revendeur)
# ============================================================

BASE="$HOME/shared/htdocs/apachewsl2026"

# ─────────────────────────────────────────────────────────────
# 5.1 PHARMACIE — modules/medicaments/MedicamentModel.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/modules/medicaments/MedicamentModel.php"
<?php
/**
 * Modèle Médicaments — CRUD complet
 * PharmaSen | MariaDB PDO
 */

require_once dirname(__DIR__, 2) . '/core/Database.php';

class MedicamentModel
{
    // ── LISTE avec pagination et filtres ──────────────────────
    public static function getAll(array $filters = [], int $page = 1, int $per = 25): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = '(m.denomination LIKE ? OR m.nom_commercial LIKE ? OR m.code_barre LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s);
        }
        if (!empty($filters['categorie_id'])) {
            $where[]  = 'm.categorie_id = ?';
            $params[] = $filters['categorie_id'];
        }
        if (isset($filters['actif'])) {
            $where[]  = 'm.actif = ?';
            $params[] = (int)$filters['actif'];
        }
        if (!empty($filters['stock_critique'])) {
            $where[] = 'm.stock_actuel <= m.stock_min';
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $per;

        $total = Database::queryOne(
            "SELECT COUNT(*) AS n FROM medicaments m WHERE $whereStr", $params
        )['n'];

        $rows = Database::query(
            "SELECT m.*, c.libelle AS categorie, f.raison_sociale AS fournisseur
             FROM medicaments m
             LEFT JOIN categories_medicaments c ON c.id = m.categorie_id
             LEFT JOIN fournisseurs f ON f.id = m.fournisseur_id
             WHERE $whereStr
             ORDER BY m.denomination ASC
             LIMIT $per OFFSET $offset",
            $params
        );

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per' => $per];
    }

    // ── LIRE UN MÉDICAMENT ─────────────────────────────────────
    public static function getById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT m.*, c.libelle AS categorie, f.raison_sociale AS fournisseur
             FROM medicaments m
             LEFT JOIN categories_medicaments c ON c.id = m.categorie_id
             LEFT JOIN fournisseurs f ON f.id = m.fournisseur_id
             WHERE m.id = ?", [$id]
        );
    }

    // ── CRÉER ──────────────────────────────────────────────────
    public static function create(array $d): int
    {
        Database::execute(
            "INSERT INTO medicaments
             (code_barre, denomination, nom_commercial, categorie_id, fournisseur_id,
              forme, dosage, conditionnement, prix_achat_ht, prix_vente_ht,
              tva_taux, stock_actuel, stock_min, stock_max,
              ordonnance_obligatoire, substance_psychotrope, actif)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)",
            [
                $d['code_barre']       ?? null,
                $d['denomination'],
                $d['nom_commercial']   ?? null,
                $d['categorie_id']     ?? null,
                $d['fournisseur_id']   ?? null,
                $d['forme']            ?? 'autre',
                $d['dosage']           ?? null,
                $d['conditionnement']  ?? null,
                $d['prix_achat_ht']    ?? 0,
                $d['prix_vente_ht'],
                $d['tva_taux']         ?? 0,
                $d['stock_actuel']     ?? 0,
                $d['stock_min']        ?? 5,
                $d['stock_max']        ?? 500,
                (int)($d['ordonnance_obligatoire'] ?? 0),
                (int)($d['substance_psychotrope']  ?? 0),
            ]
        );
        return (int)Database::lastId();
    }

    // ── MODIFIER ───────────────────────────────────────────────
    public static function update(int $id, array $d): bool
    {
        $rows = Database::execute(
            "UPDATE medicaments SET
               code_barre=?, denomination=?, nom_commercial=?,
               categorie_id=?, fournisseur_id=?, forme=?, dosage=?,
               conditionnement=?, prix_achat_ht=?, prix_vente_ht=?,
               tva_taux=?, stock_min=?, stock_max=?,
               ordonnance_obligatoire=?, substance_psychotrope=?
             WHERE id=?",
            [
                $d['code_barre']       ?? null,
                $d['denomination'],
                $d['nom_commercial']   ?? null,
                $d['categorie_id']     ?? null,
                $d['fournisseur_id']   ?? null,
                $d['forme']            ?? 'autre',
                $d['dosage']           ?? null,
                $d['conditionnement']  ?? null,
                $d['prix_achat_ht']    ?? 0,
                $d['prix_vente_ht'],
                $d['tva_taux']         ?? 0,
                $d['stock_min']        ?? 5,
                $d['stock_max']        ?? 500,
                (int)($d['ordonnance_obligatoire'] ?? 0),
                (int)($d['substance_psychotrope']  ?? 0),
                $id
            ]
        );
        return $rows > 0;
    }

    // ── DÉSACTIVER (soft delete) ───────────────────────────────
    public static function delete(int $id): bool
    {
        return Database::execute(
            "UPDATE medicaments SET actif=0 WHERE id=?", [$id]
        ) > 0;
    }

    // ── AJUSTEMENT STOCK ──────────────────────────────────────
    public static function ajusterStock(int $id, int $quantite, string $type, string $notes = '', int $userId = 0): bool
    {
        $med = self::getById($id);
        if (!$med) return false;

        $newStock = $med['stock_actuel'] + $quantite;
        if ($newStock < 0) return false;

        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE medicaments SET stock_actuel = ? WHERE id = ?", [$newStock, $id]
            );
            Database::execute(
                "INSERT INTO mouvements_stock
                 (medicament_id, type_mouvement, quantite, stock_avant, stock_apres, notes, utilisateur_id)
                 VALUES (?,?,?,?,?,?,?)",
                [$id, $type, abs($quantite), $med['stock_actuel'], $newStock, $notes, $userId]
            );
            Database::commit();
            return true;
        } catch (Throwable $e) {
            Database::rollback();
            return false;
        }
    }

    // ── STOCK CRITIQUE ────────────────────────────────────────
    public static function getStockCritique(): array
    {
        return Database::query("SELECT * FROM v_stock_critique LIMIT 100");
    }

    // ── PÉREMPTIONS ───────────────────────────────────────────
    public static function getPeremptions(int $jours = 60): array
    {
        return Database::query("SELECT * FROM v_peremptions_proches WHERE jours_restants <= ?", [$jours]);
    }

    // ── RECHERCHE RAPIDE POS ──────────────────────────────────
    public static function searchPOS(string $q): array
    {
        return Database::query(
            "SELECT id, denomination, nom_commercial, code_barre,
                    prix_vente_ttc, stock_actuel, ordonnance_obligatoire
             FROM medicaments
             WHERE actif=1 AND stock_actuel > 0
               AND (denomination LIKE ? OR nom_commercial LIKE ? OR code_barre = ?)
             ORDER BY denomination LIMIT 20",
            ['%'.$q.'%', '%'.$q.'%', $q]
        );
    }
}
EOF

# ─────────────────────────────────────────────────────────────
# 5.2 PHARMACIE — modules/medicaments/medicaments_api.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/modules/medicaments/medicaments_api.php"
<?php
/**
 * API REST Médicaments — PharmaSen
 * GET    /medicaments_api.php?action=list
 * GET    /medicaments_api.php?action=get&id=X
 * POST   /medicaments_api.php?action=create  (JSON body)
 * POST   /medicaments_api.php?action=update&id=X
 * POST   /medicaments_api.php?action=delete&id=X
 * GET    /medicaments_api.php?action=pos_search&q=paracet
 */

require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Helper.php';
require_once __DIR__ . '/MedicamentModel.php';

header('Content-Type: application/json; charset=utf-8');
Auth::check();

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        // ── LISTE ─────────────────────────────────────────────
        case 'list':
            $filters = [
                'search'       => Helper::sanitize($_GET['search'] ?? ''),
                'categorie_id' => (int)($_GET['categorie_id'] ?? 0) ?: null,
                'actif'        => isset($_GET['actif']) ? (int)$_GET['actif'] : 1,
                'stock_critique'=> !empty($_GET['stock_critique']),
            ];
            $page = max(1, (int)($_GET['page'] ?? 1));
            $data = MedicamentModel::getAll($filters, $page);
            Helper::jsonResponse(true, $data);

        // ── LIRE UN ───────────────────────────────────────────
        case 'get':
            $id  = (int)($_GET['id'] ?? 0);
            $med = MedicamentModel::getById($id);
            if (!$med) Helper::jsonResponse(false, null, 'Médicament introuvable');
            Helper::jsonResponse(true, $med);

        // ── CRÉER ─────────────────────────────────────────────
        case 'create':
            Auth::requireRole('admin', 'pharmacien');
            if ($method !== 'POST') Helper::jsonResponse(false, null, 'Méthode invalide');
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (empty($body['denomination'])) Helper::jsonResponse(false, null, 'Dénomination obligatoire');
            $id = MedicamentModel::create($body);
            Helper::jsonResponse(true, ['id' => $id], 'Médicament créé avec succès');

        // ── MODIFIER ──────────────────────────────────────────
        case 'update':
            Auth::requireRole('admin', 'pharmacien');
            $id   = (int)($_GET['id'] ?? 0);
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            if (!MedicamentModel::update($id, $body))
                Helper::jsonResponse(false, null, 'Mise à jour impossible');
            Helper::jsonResponse(true, null, 'Médicament modifié');

        // ── SUPPRIMER ─────────────────────────────────────────
        case 'delete':
            Auth::requireRole('admin');
            $id = (int)($_GET['id'] ?? 0);
            MedicamentModel::delete($id);
            Helper::jsonResponse(true, null, 'Médicament désactivé');

        // ── POS SEARCH ────────────────────────────────────────
        case 'pos_search':
            $q = Helper::sanitize($_GET['q'] ?? '');
            if (strlen($q) < 2) Helper::jsonResponse(true, []);
            Helper::jsonResponse(true, MedicamentModel::searchPOS($q));

        // ── STOCK CRITIQUE ────────────────────────────────────
        case 'stock_critique':
            Helper::jsonResponse(true, MedicamentModel::getStockCritique());

        // ── PÉREMPTIONS ───────────────────────────────────────
        case 'peremptions':
            $j = (int)($_GET['jours'] ?? 60);
            Helper::jsonResponse(true, MedicamentModel::getPeremptions($j));

        // ── AJUSTEMENT STOCK ──────────────────────────────────
        case 'ajuster_stock':
            Auth::requireRole('admin', 'pharmacien', 'magasinier');
            $body  = json_decode(file_get_contents('php://input'), true) ?? [];
            $id    = (int)($body['medicament_id'] ?? 0);
            $qty   = (int)($body['quantite']       ?? 0);
            $type  = $body['type']  ?? 'ajustement';
            $notes = $body['notes'] ?? '';
            $user  = Auth::getUser();
            $ok    = MedicamentModel::ajusterStock($id, $qty, $type, $notes, $user['id']);
            Helper::jsonResponse($ok, null, $ok ? 'Stock ajusté' : 'Erreur ajustement stock');

        default:
            http_response_code(404);
            Helper::jsonResponse(false, null, 'Action inconnue');
    }
} catch (Throwable $e) {
    Helper::log('API Error: ' . $e->getMessage(), 'ERROR');
    http_response_code(500);
    Helper::jsonResponse(false, null, 'Erreur serveur');
}
EOF

echo "✅ Module médicaments pharmacie créé"

# ─────────────────────────────────────────────────────────────
# 5.3 PHARMACIE — modules/ventes/VenteModel.php (POS)
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/modules/ventes/VenteModel.php"
<?php
/**
 * Modèle Ventes POS — PharmaSen
 */

require_once dirname(__DIR__, 2) . '/core/Database.php';

class VenteModel
{
    /** Créer une vente avec ses lignes (transaction) */
    public static function creerVente(array $vente, array $lignes): int
    {
        Database::beginTransaction();
        try {
            // Insérer l'en-tête (la référence est générée par trigger)
            Database::execute(
                "INSERT INTO ventes
                 (client_id, ordonnance_id, utilisateur_id, mode_paiement,
                  montant_recu, remise_pct, statut, notes)
                 VALUES (?,?,?,?,?,?,'brouillon',?)",
                [
                    $vente['client_id']      ?? null,
                    $vente['ordonnance_id']  ?? null,
                    $vente['utilisateur_id'],
                    $vente['mode_paiement']  ?? 'especes',
                    $vente['montant_recu']   ?? 0,
                    $vente['remise_pct']     ?? 0,
                    $vente['notes']          ?? null,
                ]
            );
            $venteId = (int)Database::lastId();

            // Insérer les lignes
            foreach ($lignes as $l) {
                $montant = ($l['quantite'] * $l['prix_unitaire'])
                           * (1 - ($l['remise_pct'] ?? 0) / 100);
                Database::execute(
                    "INSERT INTO vente_lignes
                     (vente_id, medicament_id, lot_id, quantite, prix_unitaire,
                      tva_taux, remise_pct, montant_ligne)
                     VALUES (?,?,?,?,?,?,?,?)",
                    [
                        $venteId,
                        $l['medicament_id'],
                        $l['lot_id']       ?? null,
                        $l['quantite'],
                        $l['prix_unitaire'],
                        $l['tva_taux']     ?? 0,
                        $l['remise_pct']   ?? 0,
                        round($montant, 2),
                    ]
                );
            }

            // Valider et recalculer totaux via trigger
            Database::execute(
                "UPDATE ventes SET statut='validee', remise_pct=? WHERE id=?",
                [$vente['remise_pct'] ?? 0, $venteId]
            );

            Database::commit();
            return $venteId;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    /** Récupérer une vente avec ses lignes */
    public static function getById(int $id): ?array
    {
        $vente = Database::queryOne(
            "SELECT v.*,
                    CONCAT(c.prenom,' ',c.nom) AS client_nom, c.telephone AS client_tel,
                    u.nom AS caissier
             FROM ventes v
             LEFT JOIN clients c ON c.id = v.client_id
             JOIN utilisateurs u ON u.id = v.utilisateur_id
             WHERE v.id = ?", [$id]
        );
        if (!$vente) return null;

        $vente['lignes'] = Database::query(
            "SELECT vl.*, m.denomination, m.nom_commercial, m.code_barre
             FROM vente_lignes vl
             JOIN medicaments m ON m.id = vl.medicament_id
             WHERE vl.vente_id = ?", [$id]
        );
        return $vente;
    }

    /** Liste des ventes du jour */
    public static function getVentesJour(string $date = ''): array
    {
        if (!$date) $date = date('Y-m-d');
        return Database::query(
            "SELECT v.*,
                    CONCAT(c.prenom,' ',c.nom) AS client_nom,
                    u.nom AS caissier
             FROM ventes v
             LEFT JOIN clients c ON c.id = v.client_id
             JOIN utilisateurs u ON u.id = v.utilisateur_id
             WHERE DATE(v.date_vente) = ? AND v.statut = 'validee'
             ORDER BY v.date_vente DESC", [$date]
        );
    }

    /** Annuler une vente — créer avoir */
    public static function annuler(int $id): bool
    {
        $vente = self::getById($id);
        if (!$vente || $vente['statut'] !== 'validee') return false;

        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE ventes SET statut='annulee' WHERE id=?", [$id]
            );
            // Remettre le stock
            foreach ($vente['lignes'] as $l) {
                Database::execute(
                    "UPDATE medicaments SET stock_actuel = stock_actuel + ? WHERE id=?",
                    [$l['quantite'], $l['medicament_id']]
                );
            }
            Database::commit();
            return true;
        } catch (Throwable $e) {
            Database::rollback();
            return false;
        }
    }

    /** CA journalier */
    public static function getCAJournalier(): array
    {
        return Database::query(
            "SELECT * FROM v_ca_journalier LIMIT 30"
        );
    }
}
EOF

echo "✅ Module ventes POS créé"

# ─────────────────────────────────────────────────────────────
# 5.4 REVENDEUR — modules/produits/ProduitModel.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/revendeur_medical/modules/produits/ProduitModel.php"
<?php
/**
 * Modèle Produits Médicaux — MedEquip Pro
 * CRUD complet matériel médical
 */

require_once dirname(__DIR__, 2) . '/core/Database.php';

class ProduitModel
{
    public static function getAll(array $filters = [], int $page = 1, int $per = 25): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(p.designation LIKE ? OR p.reference LIKE ? OR p.marque LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            array_push($params, $s, $s, $s);
        }
        if (!empty($filters['categorie_id'])) {
            $where[]  = 'p.categorie_id = ?';
            $params[] = $filters['categorie_id'];
        }
        if (!empty($filters['fournisseur_id'])) {
            $where[]  = 'p.fournisseur_id = ?';
            $params[] = $filters['fournisseur_id'];
        }
        if (isset($filters['actif'])) {
            $where[]  = 'p.actif = ?';
            $params[] = (int)$filters['actif'];
        }

        $w      = implode(' AND ', $where);
        $offset = ($page - 1) * $per;
        $total  = Database::queryOne("SELECT COUNT(*) n FROM produits p WHERE $w", $params)['n'];

        $rows = Database::query(
            "SELECT p.*, c.libelle AS categorie, f.raison_sociale AS fournisseur
             FROM produits p
             LEFT JOIN categories_produits c  ON c.id = p.categorie_id
             LEFT JOIN fournisseurs f         ON f.id = p.fournisseur_id
             WHERE $w ORDER BY p.designation ASC LIMIT $per OFFSET $offset", $params
        );
        return ['rows' => $rows, 'total' => $total, 'page' => $page];
    }

    public static function getById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT p.*, c.libelle AS categorie, f.raison_sociale AS fournisseur
             FROM produits p
             LEFT JOIN categories_produits c ON c.id = p.categorie_id
             LEFT JOIN fournisseurs f        ON f.id = p.fournisseur_id
             WHERE p.id = ?", [$id]
        );
    }

    public static function create(array $d): int
    {
        Database::execute(
            "INSERT INTO produits
             (reference, code_barre, designation, marque, modele,
              categorie_id, fournisseur_id, description, specifications,
              homologation_oms, ce_marking, prix_achat_ht, prix_vente_ht,
              tva_taux, garantie_mois, stock_actuel, stock_min, necessite_sav)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $d['reference'],
                $d['code_barre']       ?? null,
                $d['designation'],
                $d['marque']           ?? null,
                $d['modele']           ?? null,
                $d['categorie_id']     ?? null,
                $d['fournisseur_id']   ?? null,
                $d['description']      ?? null,
                isset($d['specifications']) ? json_encode($d['specifications']) : null,
                (int)($d['homologation_oms'] ?? 0),
                (int)($d['ce_marking']       ?? 0),
                $d['prix_achat_ht']    ?? 0,
                $d['prix_vente_ht'],
                $d['tva_taux']         ?? 18.00,
                $d['garantie_mois']    ?? 12,
                $d['stock_actuel']     ?? 0,
                $d['stock_min']        ?? 1,
                (int)($d['necessite_sav'] ?? 1),
            ]
        );
        return (int)Database::lastId();
    }

    public static function update(int $id, array $d): bool
    {
        return Database::execute(
            "UPDATE produits SET
               reference=?, designation=?, marque=?, modele=?,
               categorie_id=?, fournisseur_id=?, description=?,
               prix_achat_ht=?, prix_vente_ht=?, tva_taux=?,
               garantie_mois=?, stock_min=?, homologation_oms=?, ce_marking=?
             WHERE id=?",
            [
                $d['reference'], $d['designation'],
                $d['marque'] ?? null, $d['modele'] ?? null,
                $d['categorie_id'] ?? null, $d['fournisseur_id'] ?? null,
                $d['description'] ?? null,
                $d['prix_achat_ht'] ?? 0, $d['prix_vente_ht'],
                $d['tva_taux'] ?? 18.00, $d['garantie_mois'] ?? 12,
                $d['stock_min'] ?? 1,
                (int)($d['homologation_oms'] ?? 0),
                (int)($d['ce_marking'] ?? 0),
                $id
            ]
        ) > 0;
    }

    public static function delete(int $id): bool
    {
        return Database::execute("UPDATE produits SET actif=0 WHERE id=?", [$id]) > 0;
    }
}
EOF

echo "✅ Module produits revendeur médical créé"
