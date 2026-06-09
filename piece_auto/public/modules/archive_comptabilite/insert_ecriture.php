<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../views/layout.php';

\$errors = [];
if(\$_SERVER['REQUEST_METHOD'] === 'POST'){
    \$date = \$_POST['date_operation'] ?? '';
    \$lib = trim(\$_POST['libelle_operation'] ?? '');
    \$debit = intval(\$_POST['compte_debite_id'] ?? 0);
    \$credit = intval(\$_POST['compte_credite_id'] ?? 0);
    \$montant = floatval(str_replace(',','.',\$_POST['montant'] ?? 0));
    \$soc = intval(\$_POST['societe_id'] ?? 1);

    // validation simple
    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', \$date)) \$errors[] = 'Date incorrecte';
    if(strlen(\$lib) < 3) \$errors[] = 'Libellé trop court';
    if(\$montant <= 0) \$errors[] = 'Montant doit être > 0';

    // comptes existence
    \$q = \$pdo->prepare('SELECT COUNT(*) FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = ?');
    \$q->execute([\$debit]); if(\$q->fetchColumn()==0) \$errors[] = 'Compte débité introuvable';
    \$q->execute([\$credit]); if(\$q->fetchColumn()==0) \$errors[] = 'Compte crédité introuvable';

    // societe existence
    \$q2 = \$pdo->prepare('SELECT COUNT(*) FROM SOCIETES WHERE societe_id = ?');
    \$q2->execute([\$soc]); if(\$q2->fetchColumn()==0) \$errors[] = 'Société introuvable';

    if(empty(\$errors)){
        try{
            \$ins = \$pdo->prepare('INSERT INTO ECRITURES_COMPTABLES (date_operation, libelle_operation, compte_debite_id, compte_credite_id, montant, societe_id) VALUES (?,?,?,?,?,?)');
            \$ins->execute([\$date,\$lib,\$debit,\$credit,\$montant,\$soc]);
            header('Location: dashboard.php?msg=insert_ok');
            exit;
        } catch(PDOException \$e){
            \$errors[] = 'Erreur DB: '.\$e->getMessage();
            // record in IMPORT_ERRORS
            \$log = \$pdo->prepare('INSERT INTO IMPORT_ERRORS (source_file, line_number, account_code, message) VALUES (?,?,?,?)');
            \$log->execute(['web-insert',0,\$debit,\$e->getMessage()]);
        }
    } else {
        // log each error
        \$log = \$pdo->prepare('INSERT INTO IMPORT_ERRORS (source_file, line_number, account_code, message) VALUES (?,?,?,?)');
        foreach(\$errors as \$err) \$log->execute(['web-validate',0,\$debit,\$err]);
    }
}

ob_start();
?>
<div class="card">
  <div class="card-body">
    <h5>Insérer une écriture</h5>
    <?php if(!empty(\$errors)): ?><div class="alert alert-danger"><?php foreach(\$errors as \$e) echo '<div>'.htmlspecialchars(\$e).'</div>'; ?></div><?php endif; ?>
    <form method="POST">
      <div class="mb-2"><label>Date</label><input class="form-control" type="date" name="date_operation" required></div>
      <div class="mb-2"><label>Libellé</label><input class="form-control" type="text" name="libelle_operation" required></div>
      <div class="mb-2"><label>Compte débité</label><input class="form-control" type="number" name="compte_debite_id" required></div>
      <div class="mb-2"><label>Compte crédité</label><input class="form-control" type="number" name="compte_credite_id" required></div>
      <div class="mb-2"><label>Montant</label><input class="form-control" name="montant" required></div>
      <div class="mb-2"><label>Société (ID)</label><input class="form-control" type="number" name="societe_id" value="1" required></div>
      <button class="btn btn-primary" type="submit">Insérer</button>
    </form>
  </div>
</div>
<?php
\$content = ob_get_clean();
render('Insertion écriture', \$content);
?>
