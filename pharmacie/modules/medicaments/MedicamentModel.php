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
