<?php
// crud_etudiants.php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. Connexion via le point central (Socket MariaDB)
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Fonction pour générer un code étudiant unique (ex: INF-15-2026)
function generateStudentCode($nom_class, $id_etudiant) {
    $prefix = strtoupper(substr($nom_class, 0, 3));
    $annee = date('Y');
    return $prefix . '-' . $id_etudiant . '-' . $annee;
}

// Logique d'ajout d'étudiant
if (isset($_POST['add'])) {
    $conn->begin_transaction();
    try {
        $nom = $_POST['nom'] ?? ''; $prenom = $_POST['prenom'] ?? '';
        $adresse = $_POST['adresse'] ?? ''; $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? ''; $date_naissance = $_POST['date_naissance'] ?? null;
        $classe_id = $_POST['classe_id'] ?? 0;

        $stmt = $conn->prepare("INSERT INTO etudiants (nom, prenom, adresse, telephone, email, date_naissance, classe_id, code_etudiant) VALUES (?, ?, ?, ?, ?, ?, ?, '')");
        $stmt->bind_param("ssssssi", $nom, $prenom, $adresse, $telephone, $email, $date_naissance, $classe_id);
        $stmt->execute();
        $id_etudiant = $conn->insert_id;

        $stmt2 = $conn->prepare("SELECT nom_class FROM classes WHERE id = ?");
        $stmt2->bind_param("i", $classe_id);
        $stmt2->execute();
        $nom_class = $stmt2->get_result()->fetch_assoc()['nom_class'] ?? 'CLS';

        $code_etudiant = generateStudentCode($nom_class, $id_etudiant);
        $stmt3 = $conn->prepare("UPDATE etudiants SET code_etudiant = ? WHERE id = ?");
        $stmt3->bind_param("si", $code_etudiant, $id_etudiant);
        $stmt3->execute();

        $conn->commit();
        $success = "Étudiant inscrit avec succès : <b>$code_etudiant</b>";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erreur : " . $e->getMessage();
    }
}

// Logique de suppression
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM etudiants WHERE id = $id");
    header("Location: crud_etudiants.php?msg=deleted");
    exit();
}

// Données pour l'affichage
$etudiants = $conn->query("SELECT e.*, c.nom_class, f.nom_filiere FROM etudiants e LEFT JOIN classes c ON e.classe_id = c.id LEFT JOIN filieres f ON c.filiere_id = f.id ORDER BY e.id DESC")->fetch_all(MYSQLI_ASSOC);
$classes = $conn->query("SELECT c.id, c.nom_class, f.nom_filiere FROM classes c LEFT JOIN filieres f ON c.filiere_id = f.id")->fetch_all(MYSQLI_ASSOC);

include 'header_ecole.php'; // Ce header doit être celui sans les alertes critiques
?>

<style>
    .gold-card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .table-premium thead { background: #1a2a6c; color: white; }
    .btn-action { border-radius: 8px; font-weight: 600; padding: 5px 12px; }
    .page-title { color: #1a2a6c; font-weight: 800; border-left: 5px solid #D4AF37; padding-left: 15px; }
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title text-uppercase">Gestion des Étudiants</h2>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour Dashboard</a>
    </div>

    <?php if(!empty($success)): ?> <div class="alert alert-success shadow-sm border-0"><?= $success ?></div> <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card gold-card p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Inscription</h5>
                <form method="post" class="row g-2">
                    <div class="col-12"><label class="small fw-bold">Nom</label><input type="text" name="nom" class="form-control form-control-sm" required></div>
                    <div class="col-12"><label class="small fw-bold">Prénom</label><input type="text" name="prenom" class="form-control form-control-sm" required></div>
                    <div class="col-12"><label class="small fw-bold">Classe</label>
                        <select name="classe_id" class="form-select form-select-sm" required>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['nom_class'] ?> (<?= $c['nom_filiere'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="small fw-bold">Téléphone</label><input type="text" name="telephone" class="form-control form-control-sm"></div>
                    <div class="col-12 mt-3"><button type="submit" name="add" class="btn btn-primary w-100 fw-bold">Valider l'Inscription</button></div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card gold-card p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-premium align-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Nom & Prénom</th>
                                <th>Filière / Classe</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($etudiants as $e): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= $e['code_etudiant'] ?></td>
                                    <td><?= $e['nom'].' '.$e['prenom'] ?></td>
                                    <td><small class="badge bg-light text-dark"><?= $e['nom_filiere'] ?></small><br><b><?= $e['nom_class'] ?></b></td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="saisir_notes.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-success" title="Notes"><i class="bi bi-pencil-square"></i></a>
                                            <a href="bulletin.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-info" title="Bulletin"><i class="bi bi-file-earmark-text"></i></a>
                                            <a href="?delete=<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
