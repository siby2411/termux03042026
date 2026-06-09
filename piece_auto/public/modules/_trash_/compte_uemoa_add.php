<?php
session_start();
require __DIR__ . '/../includes/db.php';
$message = '';
$edit = false;
$compte = [
    'compte_id'=>'',
    'intitule_compte'=>'',
    'classe'=>'',
    'solde_normal'=>'',
    'nature_resultat'=>''
];

// Si modification
if(isset($_GET['id'])){
    $edit = true;
    $stmt = $pdo->prepare("SELECT * FROM PLAN_COMPTABLE_UEMOA WHERE compte_id=?");
    $stmt->execute([$_GET['id']]);
    $compte = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement POST
if($_SERVER['REQUEST_METHOD']==='POST'){
    $compte_id = $_POST['compte_id'] ?? null;
    $intitule = trim($_POST['intitule_compte'] ?? '');
    $classe = $_POST['classe'] ?? '';
    $solde_normal = $_POST['solde_normal'] ?? '';
    $nature_resultat = $_POST['nature_resultat'] ?? '';

    if($intitule && $classe && $solde_normal && $nature_resultat){
        if($edit){
            $stmt = $pdo->prepare("UPDATE PLAN_COMPTABLE_UEMOA 
                SET intitule_compte=?, classe=?, solde_normal=?, nature_resultat=? WHERE compte_id=?");
            $stmt->execute([$intitule, $classe, $solde_normal, $nature_resultat, $compte_id]);
            $message = "Compte modifié avec succès !";
        } else {
            $stmt = $pdo->prepare("INSERT INTO PLAN_COMPTABLE_UEMOA 
                (intitule_compte, classe, solde_normal, nature_resultat) VALUES (?,?,?,?)");
            $stmt->execute([$intitule, $classe, $solde_normal, $nature_resultat]);
            $message = "Compte ajouté avec succès !";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// Récupérer la liste des comptes pour le tableau
$liste = $pdo->query("SELECT * FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= $edit?'Modifier':'Ajouter' ?> Compte UEMOA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
<div class="card p-4 shadow">
<h3><?= $edit?'Modifier':'Ajouter' ?> Compte UEMOA</h3>
<?php if($message) echo "<div class='alert alert-info'>$message</div>"; ?>
<form method="POST" id="compteForm">
<?php if($edit): ?>
<input type="hidden" name="compte_id" value="<?= $compte['compte_id'] ?>">
<?php endif; ?>

<div class="mb-3">
<label>Intitulé du compte :</label>
<input type="text" name="intitule_compte" id="intitule_compte" class="form-control" value="<?= htmlspecialchars($compte['intitule_compte']) ?>" required>
<small id="msg_valid" class="text-danger"></small>
</div>

<div class="mb-3">
<label>Classe :</label>
<select name="classe" class="form-select" required>
<option value="">Sélectionner</option>
<?php
$classes = [
    1 => 'Capitaux propres',
    2 => 'Immobilisations',
    3 => 'Stocks',
    4 => 'Tiers',
    5 => 'Trésorerie'
];
foreach($classes as $k=>$v){
    $selected = ($compte['classe']==$k)?'selected':'';
    echo "<option value='$k' $selected>$k - $v</option>";
}
?>
</select>
</div>

<div class="mb-3">
<label>Solde normal :</label>
<select name="solde_normal" class="form-select" required>
<option value="">Sélectionner</option>
<?php
$solde_options = ['D'=>'Débit','C'=>'Crédit'];
foreach($solde_options as $k=>$v){
    $selected = ($compte['solde_normal']==$k)?'selected':'';
    echo "<option value='$k' $selected>$v</option>";
}
?>
</select>
</div>

<div class="mb-3">
<label>Nature du résultat :</label>
<select name="nature_resultat" class="form-select" required>
<option value="">Sélectionner</option>
<?php
$nature_options = ['EXP'=>'Exploitation','FIN'=>'Financier','HAO'=>'Hors activité','BIL'=>'Bilan'];
foreach($nature_options as $k=>$v){
    $selected = ($compte['nature_resultat']==$k)?'selected':'';
    echo "<option value='$k' $selected>$v</option>";
}
?>
</select>
</div>

<button class="btn btn-primary w-100"><?= $edit?'Modifier':'Ajouter' ?></button>
</form>
</div>

<hr>
<h4>Liste des comptes UEMOA</h4>
<table class="table table-bordered table-striped table-sm" id="listeComptes">
<thead>
<tr>
<th>ID</th><th>Intitulé</th><th>Classe</th><th>Solde</th><th>Nature</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($liste as $c): ?>
<tr>
<td><?= $c['compte_id'] ?></td>
<td><?= htmlspecialchars($c['intitule_compte']) ?></td>
<td><?= $c['classe'] ?></td>
<td><?= $c['solde_normal'] ?></td>
<td><?= $c['nature_resultat'] ?></td>
<td>
<a href="compte_uemoa_add.php?id=<?= $c['compte_id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
<a href="compte_uemoa_delete.php?id=<?= $c['compte_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce compte ?')">Supprimer</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<script>
// Validation AJAX pour intitule unique
$('#intitule_compte').on('blur', function(){
    let val = $(this).val().trim();
    if(val==='') return;
    $.post('compte_uemoa_check.php', {intitule_compte: val}, function(data){
        if(data.exists){
            $('#msg_valid').text('Ce compte existe déjà !');
        } else {
            $('#msg_valid').text('');
        }
    }, 'json');
});
</script>
</body>
</html>

