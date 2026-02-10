<?php
// edit_etudiant.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect_ecole.php';

try {
    $conn = db_connect_ecole();

    // id passé en GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) throw new Exception("ID étudiant manquant.");

    // Charger étudiant et classes
    $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$student) throw new Exception("Étudiant introuvable.");

    $classes = $conn->query("SELECT id, nom_class, code_class FROM classes ORDER BY nom_class");

    // POST update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $adresse = $_POST['adresse'] ?? null;
        $telephone = $_POST['telephone'] ?? null;
        $email = $_POST['email'] ?? null;
        $date_naissance = $_POST['date_naissance'] ?? null;
        $classe_id = intval($_POST['classe_id'] ?? 0);

        $stmt = $conn->prepare("UPDATE etudiants SET nom=?, prenom=?, adresse=?, telephone=?, email=?, date_naissance=?, classe_id=? WHERE id = ?");
        $stmt->bind_param("ssssssii", $nom, $prenom, $adresse, $telephone, $email, $date_naissance, $classe_id, $id);
        if ($stmt->execute()) {
            $success = "Étudiant mis à jour.";
            // recharger
            $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            throw new Exception("Erreur mise à jour : ".$stmt->error);
        }
    }

} catch (Exception $e) {
    $err = $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier Étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-4">
    <?php if (!empty($err)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5>Modifier l'étudiant — <?php echo htmlspecialchars($student['code_etudiant']); ?></h5>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input name="nom" class="form-control" required value="<?php echo htmlspecialchars($student['nom']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prénom</label>
                    <input name="prenom" class="form-control" required value="<?php echo htmlspecialchars($student['prenom']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Adresse</label>
                    <input name="adresse" class="form-control" value="<?php echo htmlspecialchars($student['adresse']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Téléphone</label>
                    <input name="telephone" class="form-control" value="<?php echo htmlspecialchars($student['telephone']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="<?php echo htmlspecialchars($student['date_naissance']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Classe</label>
                    <select name="classe_id" class="form-select">
                        <?php while ($c = $classes->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $student['classe_id']==$c['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['nom_class'].' ('.$c['code_class'].')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button name="save" class="btn btn-primary">Enregistrer</button>
                    <a href="crud_etudiants.php" class="btn btn-outline-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

