<?php
// /tableau_de_bord_calcul.php
include_once __DIR__ . '/config/db.php';
$database = new Database();
$db = $database->getConnection();

// 1. Récupération des données de Performance (via la Vue V_Marge_Ventes)
$query_perf = $db->query("SELECT SUM(MontantCA) as CA, SUM(MontantCDV) as CDV, SUM(MargeBrute) as Marge FROM V_Marge_Ventes");
$perf = $query_perf->fetch(PDO::FETCH_ASSOC);

$ca = $perf['CA'] ?: 0;
$cdv = $perf['CDV'] ?: 0;
$marge_brute = $perf['Marge'] ?: 0;

// 2. Récupération de la Valorisation Stock (via la Vue V_Valorisation_Stock)
$stocks_finaux = $db->query("SELECT ValeurTotaleStock FROM V_Valorisation_Stock")->fetchColumn() ?: 0;

// 3. Calcul du BFR (Basé sur le Grand Livre)
// Note: Adaptez les codes comptes (411: Clients, 401: Fournisseurs)
function get_solde($pdo, $code) {
    $sql = "SELECT COALESCE(SUM(CASE WHEN CompteDebiteur = :c THEN Montant ELSE 0 END), 0) - 
                   COALESCE(SUM(CASE WHEN CompteCrediteur = :c THEN Montant ELSE 0 END), 0) 
            FROM GrandLivre WHERE CompteDebiteur = :c OR CompteCrediteur = :c";
    $st = $pdo->prepare($sql);
    $st->execute([':c' => $code]);
    return (float)$st->fetchColumn();
}

$creances_clients = abs(get_solde($db, '411'));
$dettes_fournisseurs = abs(get_solde($db, '401'));
$bfr = ($stocks_finaux + $creances_clients) - $dettes_fournisseurs;

// 4. Seuil de Rentabilité
$charges_fixes = 65000.00; // À lier idéalement à une requête SUM sur comptes classe 6 hors 607
$taux_mscv = ($ca > 0) ? ($marge_brute / $ca) : 0;
$sr = ($taux_mscv > 0) ? ($charges_fixes / $taux_mscv) : 0;
?>
