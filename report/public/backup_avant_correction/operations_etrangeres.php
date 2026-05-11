<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Opérations en devises étrangères";
$page_icon = "currency-exchange";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'ajouter_operation') {
        $type = $_POST['type_operation'];
        $date = $_POST['date_operation'];
        $ref = $_POST['reference'];
        $tiers_id = (int)$_POST['tiers_id'];
        $montant_devise = (float)$_POST['montant_devise'];
        $code_devise = $_POST['code_devise'];
        $stmt = $pdo->prepare("SELECT taux_fcfa FROM DEVISES WHERE code = ? ORDER BY date_taux DESC LIMIT 1");
        $stmt->execute([$code_devise]);
        $taux = $stmt->fetchColumn();
        $montant_fcfa = $montant_devise * $taux;
        if ($type == 'EXPORT') {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 411, 701, ?, ?, 'EXPORT'), (?, ?, 701, 4451, ?, ?, 'EXPORT')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date, "Facture export $ref", $montant_fcfa, $ref, $date, "TVA export $ref", $montant_fcfa * 0.18, $ref]);
        } else {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 601, 401, ?, ?, 'IMPORT'), (?, ?, 4454, 401, ?, ?, 'IMPORT')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date, "Import $ref", $montant_fcfa, $ref, $date, "TVA import $ref", $montant_fcfa * 0.18, $ref]);
        }
        $stmt = $pdo->prepare("INSERT INTO OPERATIONS_ETRANGERES (type_operation, date_operation, reference, tiers_id, montant_devise, code_devise, taux_originel) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $date, $ref, $tiers_id, $montant_devise, $code_devise, $taux]);
        $message = "✅ Opération enregistrée, écritures comptables générées.";
    }
    if ($_POST['action'] === 'regler_operation') {
        $op_id = (int)$_POST['operation_id'];
        $date_reg = $_POST['date_reglement'];
        $taux_reg = (float)$_POST['taux_reglement'];
        $op = $pdo->prepare("SELECT * FROM OPERATIONS_ETRANGERES WHERE id = ?");
        $op->execute([$op_id]);
        $o = $op->fetch();
        $montant_fcfa_initial = $o['montant_fcfa_originel'];
        $montant_fcfa_reg = $o['montant_devise'] * $taux_reg;
        $ecart = $montant_fcfa_reg - $montant_fcfa_initial;
        if ($o['type_operation'] == 'EXPORT') {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, 411, ?, ?, 'REGLEMENT')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date_reg, "Règlement {$o['reference']}", $montant_fcfa_reg, $o['reference']]);
            if ($ecart != 0) {
                if ($ecart > 0) $sql2 = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 476, 411, ?, ?, 'ECART')";
                else $sql2 = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 411, 676, ?, ?, 'ECART')";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([$date_reg, "Écart de change {$o['reference']}", abs($ecart), $o['reference']]);
            }
        } else {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 401, 521, ?, ?, 'REGLEMENT')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date_reg, "Paiement {$o['reference']}", $montant_fcfa_reg, $o['reference']]);
            if ($ecart != 0) {
                if ($ecart > 0) $sql2 = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 401, 766, ?, ?, 'ECART')";
                else $sql2 = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 666, 401, ?, ?, 'ECART')";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([$date_reg, "Écart de change {$o['reference']}", abs($ecart), $o['reference']]);
            }
        }
        $update = $pdo->prepare("UPDATE OPERATIONS_ETRANGERES SET date_reglement = ?, taux_reglement = ?, montant_fcfa_reglement = ?, ecart_change = ? WHERE id = ?");
        $update->execute([$date_reg, $taux_reg, $montant_fcfa_reg, $ecart, $op_id]);
        $message = "✅ Règlement enregistré, écritures d'écart de change générées.";
    }
}
// ... reste de l'interface HTML (similaire à la version précédente)
// Inclure les listes, formulaires, etc.
?>
<?php include 'inc_footer.php'; ?>
