<?php
// Fichier : modifier_client.php

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$id_client = intval($_GET['id'] ?? 0);

if ($id_client === 0) {
    // Si l'ID est manquant, rediriger
    header("Location: crud_clients.php");
    exit();
}

$conn = db_connect();

// Récupérer les données du client (avec requête préparée pour la sécurité)
$sql = "SELECT id_client, nom, adresse, telephone FROM clients WHERE id_client = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_client);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    $_SESSION['message'] = "Erreur: Client non trouvé.";
    $stmt->close();
    $conn->close();
    header("Location: crud_clients.php");
    exit();
}

$stmt->close();
$conn->close();

// 1. INCLUSION DU HEADER
include 'header.php';
?>

<h1 class="mb-4 text-info">✏️ Modifier le Client</h1>
<p><a href="crud_clients.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour à la Liste des Clients</a></p>

<div class="card shadow p-4 mt-4">
    <div class="card-header bg-info text-white">
        <h2 class="h5 mb-0">Client: <?php echo htmlspecialchars($client['nom']); ?> (ID: <?php echo $client['id_client']; ?>)</h2>
    </div>
    
    <div class="card-body">
        <form action="traitement_client.php" method="post">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id_client" value="<?php echo $client['id_client']; ?>">
            
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du Client:</label>
                <input type="text" class="form-control" id="nom" name="nom" 
                       value="<?php echo htmlspecialchars($client['nom']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse:</label>
                <input type="text" class="form-control" id="adresse" name="adresse" 
                       value="<?php echo htmlspecialchars($client['adresse']); ?>">
            </div>
            
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone:</label>
                <input type="tel" class="form-control" id="telephone" name="telephone" 
                       value="<?php echo htmlspecialchars($client['telephone']); ?>">
            </div>
            
            <button type="submit" class="btn btn-success mt-3">
                Enregistrer les Modifications
            </button>
        </form>
    </div>
</div>

<?php
// 2. INCLUSION DU FOOTER
include 'footer.php';
?>
