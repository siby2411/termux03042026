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
