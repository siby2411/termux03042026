<?php
require 'config/db.php';
$page_title = 'Nouveau Client';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['nom']) || empty($f['prenom'])) {
        $error = 'Nom et prénom obligatoires.';
    } else {
        try {
            $pdo->prepare("INSERT INTO clients (nom,prenom,email,telephone,passeport,nationalite,date_naissance,adresse)
            VALUES(:nom,:prenom,:email,:tel,:pp,:nat,:dob,:adr)")->execute([
                ':nom'=>$f['nom'],':prenom'=>$f['prenom'],
                ':email'=>$f['email']??null,':tel'=>$f['telephone']??null,
                ':pp'=>$f['passeport']??null,':nat'=>$f['nationalite']??'Sénégalaise',
                ':dob'=>$f['date_naissance']??null,':adr'=>$f['adresse']??null,
            ]);
            $success = 'Client <strong>'.htmlspecialchars($f['prenom'].' '.$f['nom']).'</strong> enregistré.';
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}
require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Base Clients</div>
      <h1>Nouveau Client</h1>
    </div>
    <a href="clients.php" class="btn btn-ghost">← Retour</a>
  </div>
  <?php if($success) echo "<div class='alert alert-success'>✓ $success &nbsp;<a href='clients.php' style='color:inherit;text-decoration:underline'>Voir les clients →</a></div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <form method="POST" style="max-width:700px">
    <div class="form-section">
      <div class="form-section-title">👤 Identité</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Prénom <span class="req">*</span></label>
            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($_POST['prenom']??'') ?>" placeholder="Mamadou" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nom <span class="req">*</span></label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($_POST['nom']??'') ?>" placeholder="DIALLO" required>
          </div>
        </div>
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($_POST['date_naissance']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Nationalité</label>
            <input type="text" name="nationalite" class="form-control" value="<?= htmlspecialchars($_POST['nationalite']??'Sénégalaise') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Numéro de Passeport</label>
          <input type="text" name="passeport" class="form-control" value="<?= htmlspecialchars($_POST['passeport']??'') ?>" placeholder="Ex: A1234567">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">📞 Contact</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($_POST['telephone']??'') ?>" placeholder="+221 77 000 00 00">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="prenom.nom@exemple.com">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Adresse</label>
          <textarea name="adresse" class="form-control" placeholder="Adresse complète"><?= htmlspecialchars($_POST['adresse']??'') ?></textarea>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
      <a href="clients.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-gold">+ Enregistrer le Client</button>
    </div>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
