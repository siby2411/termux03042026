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
