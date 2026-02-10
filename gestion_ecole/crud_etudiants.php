<?php
// crud_etudiants.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base
function db_connect_ecole() {
    $host = "localhost";
    $user = "root";
    $pass = "123";
    $db   = "ecole";

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Erreur connexion DB : " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

$conn = db_connect_ecole();

// Fonction pour générer un code étudiant unique
function generateStudentCode($nom_class, $id_etudiant) {
    $prefix = strtoupper(substr($nom_class, 0, 3));
    $annee = date('Y');
    return $prefix . '-' . $id_etudiant . '-' . $annee;
}

// Ajouter un étudiant
if (isset($_POST['add'])) {
    $conn->begin_transaction();
    try {
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? '';
        $date_naissance = $_POST['date_naissance'] ?? null;
        $classe_id = $_POST['classe_id'] ?? 0;

        // 1. Insertion avec code_etudiant vide
        $stmt = $conn->prepare("INSERT INTO etudiants (nom, prenom, adresse, telephone, email, date_naissance, classe_id, code_etudiant) VALUES (?, ?, ?, ?, ?, ?, ?, '')");
        $stmt->bind_param("ssssssi", $nom, $prenom, $adresse, $telephone, $email, $date_naissance, $classe_id);
        $stmt->execute();
        $id_etudiant = $conn->insert_id;

        // 2. Récupérer nom de la classe
        $stmt2 = $conn->prepare("SELECT nom_class FROM classes WHERE id = ?");
        $stmt2->bind_param("i", $classe_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $nom_class = $result->fetch_assoc()['nom_class'] ?? 'CLS';

        // 3. Générer code unique
        $code_etudiant = generateStudentCode($nom_class, $id_etudiant);

        // 4. Mise à jour de code_etudiant
        $stmt3 = $conn->prepare("UPDATE etudiants SET code_etudiant = ? WHERE id = ?");
        $stmt3->bind_param("si", $code_etudiant, $id_etudiant);
        $stmt3->execute();

        $conn->commit();
        $success = "Étudiant ajouté avec succès : $code_etudiant";

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erreur ajout étudiant : " . $e->getMessage();
    }
}

// Supprimer un étudiant
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM etudiants WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $success = "Étudiant supprimé avec succès.";
    } catch (Exception $e) {
        $error = "Erreur suppression : " . $e->getMessage();
    }
}

// Récupérer liste des étudiants avec jointure classes et filières
$etudiants = [];
try {
    $sql = "SELECT e.*, c.nom_class, f.nom_filiere, f.cycle_id
            FROM etudiants e
            LEFT JOIN classes c ON e.classe_id = c.id
            LEFT JOIN filieres f ON c.filiere_id = f.id
            ORDER BY e.id DESC";
    $res = $conn->query($sql);
    if ($res) {
        $etudiants = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = "Erreur récupération étudiants : " . $e->getMessage();
}

// Récupérer les classes pour le formulaire
$classes = [];
try {
    $res = $conn->query("SELECT c.id, c.nom_class, f.nom_filiere FROM classes c LEFT JOIN filieres f ON c.filiere_id = f.id");
    if ($res) $classes = $res->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Erreur récupération classes : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des Étudiants</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Gestion des Étudiants</h2>

    <?php if(!empty($success)) : ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(!empty($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Inscrire un Étudiant</div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-4">
                    <label>Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Date de Naissance</label>
                    <input type="date" name="date_naissance" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Adresse</label>
                    <input type="text" name="adresse" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Classe (Filière / Cycle)</label>
                    <select name="classe_id" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['nom_class'] ?> (<?= $c['nom_filiere'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" name="add" class="btn btn-primary">Ajouter Étudiant</button>
                </div>
            </form>
        </div>
    </div>

    <h4>Liste des Étudiants</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Nom & Prénom</th>
                    <th>Cycle</th>
                    <th>Filière</th>
                    <th>Classe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($etudiants as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['code_etudiant']) ?></td>
                        <td><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></td>
                        <td><?= htmlspecialchars($e['cycle_id']) ?></td>
                        <td><?= htmlspecialchars($e['nom_filiere']) ?></td>
                        <td><?= htmlspecialchars($e['nom_class']) ?></td>
                        <td>
                            <a href="edit_etudiant.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="?delete=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet étudiant ?')">Supprimer</a>
                            <a href="bulletin.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-info">Éditer Bulletin</a>
                            <a href="saisir_notes.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-success">Saisir Notes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($etudiants)) echo '<tr><td colspan="6" class="text-center">Aucun étudiant</td></tr>'; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

