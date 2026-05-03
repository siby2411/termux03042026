<?php
/**
 * Modèle Devis — MedEquip Pro
 */

require_once dirname(__DIR__, 2) . '/core/Database.php';

class DevisModel
{
    public static function creer(array $devis, array $lignes): int
    {
        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO devis (client_id, commercial_id, date_devis, date_validite,
                  remise_pct, notes)
                 VALUES (?,?,?,?,?,?)",
                [
                    $devis['client_id'],
                    $devis['commercial_id'],
                    $devis['date_devis'],
                    $devis['date_validite'] ?? date('Y-m-d', strtotime('+30 days')),
                    $devis['remise_pct']    ?? 0,
                    $devis['notes']         ?? null,
                ]
            );
            $devisId = (int)Database::lastId();

            $montantHT = 0;
            foreach ($lignes as $l) {
                $ht     = $l['quantite'] * $l['prix_unitaire'] * (1 - ($l['remise_pct'] ?? 0) / 100);
                $tva    = $ht * ($l['tva_taux'] ?? 18) / 100;
                $total  = $ht + $tva;
                $montantHT += $ht;

                Database::execute(
                    "INSERT INTO devis_lignes
                     (devis_id, produit_id, designation, quantite, prix_unitaire,
                      tva_taux, remise_pct, montant_ligne)
                     VALUES (?,?,?,?,?,?,?,?)",
                    [
                        $devisId,
                        $l['produit_id'],
                        $l['designation'] ?? '',
                        $l['quantite'],
                        $l['prix_unitaire'],
                        $l['tva_taux']   ?? 18.00,
                        $l['remise_pct'] ?? 0,
                        round($total, 2),
                    ]
                );
            }

            $tvaTotal = $montantHT * 0.18;
            $ttc      = $montantHT + $tvaTotal;
            $remise   = $ttc * ($devis['remise_pct'] ?? 0) / 100;
            $net      = $ttc - $remise;

            Database::execute(
                "UPDATE devis SET montant_ht=?, tva_montant=?, montant_ttc=?, net_a_payer=? WHERE id=?",
                [round($montantHT,2), round($tvaTotal,2), round($ttc,2), round($net,2), $devisId]
            );

            Database::commit();
            return $devisId;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function getById(int $id): ?array
    {
        $devis = Database::queryOne(
            "SELECT d.*, cl.raison_sociale AS client, cl.adresse AS client_adresse,
                    cl.telephone AS client_tel, cl.ninea AS client_ninea,
                    CONCAT(u.prenom,' ',u.nom) AS commercial
             FROM devis d
             JOIN clients cl     ON cl.id = d.client_id
             JOIN utilisateurs u ON u.id  = d.commercial_id
             WHERE d.id = ?", [$id]
        );
        if (!$devis) return null;
        $devis['lignes'] = Database::query(
            "SELECT dl.*, p.reference AS ref_produit, p.marque
             FROM devis_lignes dl
             LEFT JOIN produits p ON p.id = dl.produit_id
             WHERE dl.devis_id = ?", [$id]
        );
        return $devis;
    }

    public static function convertirEnCommande(int $devisId, int $commercialId): int
    {
        $devis = self::getById($devisId);
        if (!$devis || $devis['statut'] !== 'accepte')
            throw new RuntimeException('Devis non accepté ou introuvable');

        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO commandes
                 (devis_id, client_id, commercial_id, date_commande, net_a_payer, statut)
                 VALUES (?,?,?,CURDATE(),?,'confirmee')",
                [$devisId, $devis['client_id'], $commercialId, $devis['net_a_payer']]
            );
            $cmdId = (int)Database::lastId();

            foreach ($devis['lignes'] as $l) {
                Database::execute(
                    "INSERT INTO commande_lignes (commande_id, produit_id, quantite, prix_unitaire, montant_ligne)
                     VALUES (?,?,?,?,?)",
                    [$cmdId, $l['produit_id'], $l['quantite'], $l['prix_unitaire'], $l['montant_ligne']]
                );
            }

            Database::execute("UPDATE devis SET statut='converti' WHERE id=?", [$devisId]);
            Database::commit();
            return $cmdId;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function getPipeline(): array
    {
        return Database::query("SELECT * FROM v_pipeline_devis");
    }
}
