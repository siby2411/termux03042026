<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// 1. Logique d'ajout avec transaction sécurisée
if (isset($_POST['add'])) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO etudiants (nom, prenom, date_naissance, adresse, telephone, email, classe_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $_POST['nom'], $_POST['prenom'], $_POST['date_naissance'], $_POST['adresse'], $_POST['telephone'], $_POST['email'], $_POST['classe_id']);
        $stmt->execute();

        $etudiant_id = $conn->insert_id;
        $code_etudiant = "ETU-" . $etudiant_id . "-" . date("Y");
        $conn->query("UPDATE etudiants SET code_etudiant = '$code_etudiant' WHERE id = $etudiant_id");

        // Initialisation Bulletin
        $annee = "2025-2026";
        $stmt_bull = $conn->prepare("INSERT INTO bulletins (code_etudiant, annee_academique, moyenne_semestre1, moyenne_semestre2, moyenne_annuelle) VALUES (?, ?, 0, 0, 0)");
        $stmt_bull->bind_param("ss", $code_etudiant, $annee);
        $stmt_bull->execute();

        $conn->commit();
        header("Location: crud_etudiants.php?success=1"); exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erreur : " . $e->getMessage();
    }
}

// 2. Suppression
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM etudiants WHERE id = " . intval($_GET['delete']));
    header("Location: crud_etudiants.php"); exit();
}

// 3. Récupération des données avec jointure Filières
$etudiants = $conn->query("
    SELECT e.*, c.nom_class, f.nom_filiere 
    FROM etudiants e 
    LEFT JOIN classes c ON e.classe_id = c.id 
    LEFT JOIN filieres f ON c.filiere_id = f.id 
    ORDER BY e.id DESC")->fetch_all(MYSQLI_ASSOC);

$classes = $conn->query("SELECT id, nom_class FROM classes")->fetch_all(MYSQLI_ASSOC);

include 'header_ecole.php';
?>

<div class="container mt-4">
    <h2 class="mb-4 text-primary fw-bold">Gestion des Étudiants</h2>
    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0">
                <h5 class="mb-3">Nouvelle Inscription</h5>
                <form method="post">
                    <input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required>
                    <input type="text" name="prenom" class="form-control mb-2" placeholder="Prénom" required>
                    <input type="date" name="date_naissance" class="form-control mb-2">
                    <input type="text" name="adresse" class="form-control mb-2" placeholder="Adresse">
                    <input type="text" name="telephone" class="form-control mb-2" placeholder="Téléphone">
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                    <select name="classe_id" class="form-select mb-3" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['nom_class'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="add" class="btn btn-primary w-100">Valider l'Inscription</button>
                </form>
            </div>
        </div>
        
        <div class="col-md-8">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Code</th>
                        <th>Nom & Prénom</th>
                        <th>Classe</th>
                        <th>Filière</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($etudiants as $e): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= $e['code_etudiant'] ?></span></td>
                        <td><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></td>
                        <td><?= htmlspecialchars($e['nom_class']) ?></td>
                        <td><?= htmlspecialchars($e['nom_filiere'] ?? 'N/A') ?></td>
                        <td>
                            <?= htmlspecialchars($e['telephone']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($e['email']) ?></small>
                        </td>
                        <td>
                            <a href="edit_etudiant.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <a href="upload_form.php?id=<?= $e["id"] ?>" class="btn btn-sm btn-secondary" title="Ajouter Photo"><i class="bi bi-camera"></i></a>
                            <a href="?delete=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer ?')"><i class="bi bi-trash"></i></a>
                            
                            <a href="carte_etudiant.php?id=<?= $e['id'] ?>" target="_blank" class="btn btn-sm btn-info" title="Imprimer Carte">
                                <i class="bi bi-person-badge"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
