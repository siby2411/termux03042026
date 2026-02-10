<?php
// /var/www/piece_auto/modules/gestion_clients.php

include '../config/Database.php';
include '../includes/header.php';
$page_title = "Gestion des Clients";

$database = new Database();
$db = $database->getConnection(); 
$message_status = "";

// --- 1. LOGIQUE : GESTION DE L'AJOUT DE CLIENT ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_client') {
    // Nettoyage des données
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $adresse = htmlspecialchars($_POST['adresse']);
    $telephone = htmlspecialchars($_POST['telephone']);
    $email = htmlspecialchars($_POST['email']);
    
    // Requête d'insertion
    $query = "INSERT INTO CLIENTS (nom, prenom, adresse, telephone, email) 
              VALUES (:nom, :prenom, :adresse, :tel, :email)";
    
    $stmt = $db->prepare($query);

    try {
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':adresse' => $adresse,
            ':tel' => $telephone,
            ':email' => $email
        ]);
        $message_status = "<div class='alert alert-success'>Client **" . $nom . " " . $prenom . "** ajouté avec succès !</div>";
    } catch (PDOException $e) {
        // Erreur 23000 (Duplicate entry) est typique pour les champs UNIQUE (email)
        if ($e->getCode() == '23000') {
             $message_status = "<div class='alert alert-danger'>Erreur : L'email **" . $email . "** est déjà utilisé.</div>";
        } else {
             $message_status = "<div class='alert alert-danger'>Erreur lors de l'ajout : " . $e->getMessage() . "</div>";
        }
    }
}

// --- 2. LOGIQUE : RÉCUPÉRATION DE LA LISTE DES CLIENTS ---
$clients = [];
$query_clients = "SELECT * FROM CLIENTS ORDER BY nom, prenom LIMIT 50";
$stmt_clients = $db->query($query_clients);
if ($stmt_clients) {
    $clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-users"></i> Gestion des Clients</h2>
        <a href="#formulaire_ajout" class="btn btn-primary mb-4" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="formulaire_ajout">
            <i class="fas fa-user-plus"></i> Ajouter un Nouveau Client
        </a>
        
        <?= $message_status ?>

        <div class="collapse mb-5" id="formulaire_ajout">
            <div class="card p-4">
                <h4 class="card-title mb-4">Fiche Client</h4>
                <form method="POST" action="gestion_clients.php">
                    <input type="hidden" name="action" value="add_client">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3 text-secondary"><i class="fas fa-id-card"></i> Coordonnées</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email (Unique)</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-12">
                            <label for="adresse" class="form-label">Adresse Complète</label>
                            <input type="text" class="form-control" id="adresse" name="adresse">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Enregistrer le Client</button>
                </form>
            </div>
        </div>

        <div class="card p-4">
            <h4 class="card-title mb-4">Base de Données Clients (<?= count($clients) ?> Fiches)</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nom & Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucun client enregistré.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td class="fw-bold"><?= $client['nom'] . ' ' . $client['prenom'] ?></td>
                                    <td><?= $client['email'] ?></td>
                                    <td><?= $client['telephone'] ?></td>
                                    <td><small class="text-muted"><?= $client['adresse'] ?></small></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-info" title="Voir Historique"><i class="fas fa-history"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
