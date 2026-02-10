<?php
include '../../includes/header.php';
include '../../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: list.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, d.nom as departement_nom, s.nom as specialite_nom 
          FROM personnel p 
          LEFT JOIN departements d ON p.id_departement = d.id 
          LEFT JOIN specialites s ON p.id_specialite = s.id 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$personnel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$personnel) {
    header("Location: list.php");
    exit();
}
?>

<div class="content">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Profil du Personnel</h2>
            <a href="edit.php?id=<?php echo $personnel['id']; ?>" class="btn btn-warning">Modifier</a>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h3>Informations Personnelles</h3>
                    <table class="table">
                        <tr>
                            <th>Matricule:</th>
                            <td><?php echo htmlspecialchars($personnel['matricule']); ?></td>
                        </tr>
                        <tr>
                            <th>Nom Complet:</th>
                            <td><?php echo htmlspecialchars($personnel['civilite'] . ' ' . $personnel['prenom'] . ' ' . $personnel['nom']); ?></td>
                        </tr>
                        <tr>
                            <th>Rôle:</th>
                            <td><span class="role-badge"><?php echo htmlspecialchars($personnel['role']); ?></span></td>
                        </tr>
                        <tr>
                            <th>Spécialité:</th>
                            <td><?php echo htmlspecialchars($personnel['specialite_nom'] ?? '-'); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div>
                    <h3>Informations Professionnelles</h3>
                    <table class="table">
                        <tr>
                            <th>Département:</th>
                            <td><?php echo htmlspecialchars($personnel['departement_nom']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($personnel['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Téléphone:</th>
                            <td><?php echo htmlspecialchars($personnel['telephone']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
