<?php
/**
 * PUBLIC/CLIENTS.PHP
 * Module de gestion des fiches clients (CRUD).
 * CORRIGÉ: Utilise $_SESSION['user_id'] pour le contrôle d'accès.
 */

session_start();

// --- INCLUSIONS ---
// Assurez-vous que BASE_URL et la connexion à la DB sont disponibles
require_once '../includes/db.php'; 
require_once '../includes/header.php'; // Ce fichier doit contenir la vérification de session et les balises HTML de début
require_once '../includes/fonctions.php';

// --- CONTRÔLE D'ACCÈS CORRIGÉ ---
// Le header.php contient déjà cette logique, mais nous la gardons ici par sécurité si le header est inclus tardivement.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$action = $_GET['action'] ?? 'list'; // Par défaut : afficher la liste
$client_a_modifier = null; // Utilisé pour le mode 'edit'

// ------------------------------------------------------------------
// --- 1. TRAITEMENT DE L'AJOUT/MODIFICATION (C & U de CRUD) ---
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    $nom = $conn->real_escape_string(strtoupper(trim($_POST['nom'])));
    $prenoms = $conn->real_escape_string(ucwords(strtolower(trim($_POST['prenoms']))));
    $telephone = $conn->real_escape_string(trim($_POST['telephone']));
    $email = $conn->real_escape_string(strtolower(trim($_POST['email'])));
    $adresse = $conn->real_escape_string(trim($_POST['adresse']));
    $dateNaissance = $conn->real_escape_string($_POST['date_naissance']);
    $typeIdentifiant = $conn->real_escape_string($_POST['type_identifiant']);
    $numeroIdentifiant = $conn->real_escape_string(trim($_POST['numero_identifiant']));
    $statut = $_POST['statut'] ?? 'Actif'; // Par défaut actif

    if (empty($nom) || empty($prenoms) || empty($email) || empty($numeroIdentifiant)) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (Nom, Prénoms, Email, Numéro d'identité).</div>";
        $action = ($_POST['action'] == 'update_client') ? 'edit' : 'add';
    } else {
        try {
            if ($_POST['action'] == 'add_client') {
                // Création (C de CRUD)
                $sql = "INSERT INTO CLIENTS (Nom, Prenoms, Email, Telephone, Adresse, DateNaissance, TypeIdentifiant, NumeroIdentifiant, Statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                // Le statut est inclus dans bind_param pour être sûr (même si par défaut 'Actif')
                $stmt->bind_param("sssssssss", $nom, $prenoms, $email, $telephone, $adresse, $dateNaissance, $typeIdentifiant, $numeroIdentifiant, $statut); 

                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Client **$nom $prenoms** ajouté avec succès (ID: " . $stmt->insert_id . ").</div>";
                } else {
                    // Gestion des erreurs SQL comme les doublons d'email/ID
                    $error_msg = $conn->error;
                    if (strpos($error_msg, 'Duplicate entry') !== false) {
                         $message = "<div class='alert alert-danger'>Erreur : Le Numéro d'Identifiant ou l'Email existe déjà.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Erreur SQL lors de l'ajout : " . $error_msg . "</div>";
                    }
                }
                $stmt->close();
                $action = 'list'; // Retourne à la liste après ajout réussi ou échec SQL
            } elseif ($_POST['action'] == 'update_client') {
                // Modification (U de CRUD)
                $clientID = (int)$_POST['client_id'];
                $sql = "UPDATE CLIENTS SET Nom = ?, Prenoms = ?, Email = ?, Telephone = ?, Adresse = ?, DateNaissance = ?, TypeIdentifiant = ?, NumeroIdentifiant = ?, Statut = ? WHERE ClientID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssi", $nom, $prenoms, $email, $telephone, $adresse, $dateNaissance, $typeIdentifiant, $numeroIdentifiant, $statut, $clientID);

                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Fiche client (ID: $clientID) mise à jour avec succès.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Erreur SQL lors de la modification : " . $conn->error . "</div>";
                }
                $stmt->close();
                $action = 'list';
            }
        } catch (\Exception $e) {
            $message = "<div class='alert alert-danger'>Erreur système : " . $e->getMessage() . "</div>";
        }
    }
}

// ------------------------------------------------------------------
// --- 2. TRAITEMENT DU STATUT (Activation/Désactivation) (D de CRUD) ---
// ------------------------------------------------------------------
if ($action == 'toggle_status' && isset($_GET['id'])) {
    $clientID = (int)$_GET['id'];
    $current_status = $conn->real_escape_string($_GET['current_status']);
    $new_status = ($current_status == 'Actif') ? 'Inactif' : 'Actif';

    $stmt = $conn->prepare("UPDATE CLIENTS SET Statut = ? WHERE ClientID = ?");
    $stmt->bind_param("si", $new_status, $clientID);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Statut du client ID $clientID changé à **$new_status**.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Erreur lors du changement de statut : " . $conn->error . "</div>";
    }
    $stmt->close();
    $action = 'list'; // Retourner à la liste
}

// ------------------------------------------------------------------
// --- 3. PRÉPARATION DU FORMULAIRE D'ÉDITION ---
// ------------------------------------------------------------------
if ($action == 'edit' && isset($_GET['id'])) {
    $clientID = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM CLIENTS WHERE ClientID = ?");
    $stmt->bind_param("i", $clientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $client_a_modifier = $result->fetch_assoc();
    } else {
        $message = "<div class='alert alert-warning'>Client non trouvé.</div>";
        $action = 'list';
    }
    $stmt->close();
}

// ------------------------------------------------------------------
// --- 4. RÉCUPÉRATION DES CLIENTS (Pour l'affichage de la liste) (R de CRUD) ---
// ------------------------------------------------------------------
$clients = [];
$sql_clients = "SELECT ClientID, Nom, Prenoms, Email, Telephone, Statut FROM CLIENTS ORDER BY Nom ASC";
$result_clients = $conn->query($sql_clients);
if ($result_clients) {
    while ($row = $result_clients->fetch_assoc()) {
        $clients[] = $row;
    }
}

// --- 5. AFFICHAGE DE L'INTERFACE ---
// Le header.php a déjà commencé les balises HTML et la bannière
?>

<h1 class="mt-4"><i class="fas fa-users me-2"></i> Gestion du Portefeuille Clients</h1>
<?= $message ?>

<?php if ($action == 'add' || $action == 'edit') : ?>
    <div class="card shadow mb-4">
        <div class="card-header bg-<?= ($action == 'add') ? 'success' : 'warning' ?> text-white">
            <h4><?= ($action == 'add') ? 'Ajouter un Nouveau Client' : 'Modifier la Fiche Client ID ' . $client_a_modifier['ClientID'] ?></h4>
        </div>
        <div class="card-body">
            <form action="clients.php" method="POST">
                <input type="hidden" name="action" value="<?= ($action == 'add') ? 'add_client' : 'update_client' ?>">
                <?php if ($action == 'edit') : ?>
                    <input type="hidden" name="client_id" value="<?= $client_a_modifier['ClientID'] ?>">
                <?php endif; ?>

                <h5 class="mt-3 mb-3 text-primary"><i class="fas fa-id-card me-2"></i> Informations Personnelles</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= $client_a_modifier['Nom'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="prenoms" class="form-label">Prénoms</label>
                        <input type="text" class="form-control" id="prenoms" name="prenoms" value="<?= $client_a_modifier['Prenoms'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_naissance" class="form-label">Date de Naissance</label>
                        <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= $client_a_modifier['DateNaissance'] ?? '' ?>" required>
                    </div>
                </div>

                <h5 class="mt-3 mb-3 text-primary"><i class="fas fa-address-book me-2"></i> Coordonnées et Identité</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $client_a_modifier['Email'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone" value="<?= $client_a_modifier['Telephone'] ?? '' ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="type_identifiant" class="form-label">Type d'Identifiant</label>
                        <select class="form-select" id="type_identifiant" name="type_identifiant" required>
                            <option value="CNI" <?= (isset($client_a_modifier['TypeIdentifiant']) && $client_a_modifier['TypeIdentifiant'] == 'CNI') ? 'selected' : '' ?>>CNI (Carte Nationale)</option>
                            <option value="Passeport" <?= (isset($client_a_modifier['TypeIdentifiant']) && $client_a_modifier['TypeIdentifiant'] == 'Passeport') ? 'selected' : '' ?>>Passeport</option>
                            <option value="Permis" <?= (isset($client_a_modifier['TypeIdentifiant']) && $client_a_modifier['TypeIdentifiant'] == 'Permis') ? 'selected' : '' ?>>Permis de Conduire</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="numero_identifiant" class="form-label">Numéro d'Identifiant</label>
                        <input type="text" class="form-control" id="numero_identifiant" name="numero_identifiant" value="<?= $client_a_modifier['NumeroIdentifiant'] ?? '' ?>" required>
                    </div>
                    <?php if ($action == 'edit') : ?>
                        <div class="col-md-4 mb-3">
                            <label for="statut" class="form-label">Statut du Client</label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="Actif" <?= (isset($client_a_modifier['Statut']) && $client_a_modifier['Statut'] == 'Actif') ? 'selected' : '' ?>>Actif</option>
                                <option value="Inactif" <?= (isset($client_a_modifier['Statut']) && $client_a_modifier['Statut'] == 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse Complète</label>
                    <textarea class="form-control" id="adresse" name="adresse" rows="2"><?= $client_a_modifier['Adresse'] ?? '' ?></textarea>
                </div>

                <button type="submit" class="btn btn-<?= ($action == 'add') ? 'success' : 'warning' ?> mt-3"><i class="fas fa-save me-2"></i> Enregistrer la Fiche Client</button>
                <a href="clients.php" class="btn btn-secondary mt-3">Retour à la Liste</a>
            </form>
        </div>
    </div>

<?php else : ?>
    <div class="d-flex justify-content-end mb-3">
        <a href="clients.php?action=add" class="btn btn-success"><i class="fas fa-user-plus me-2"></i> Ajouter Nouveau Client</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Nom & Prénoms</th>
                            <th>Email / Téléphone</th>
                            <th>Statut</th>
                            <th style="width: 250px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)) : ?>
                            <tr><td colspan="5" class="text-center">Aucun client enregistré.</td></tr>
                        <?php else : ?>
                            <?php foreach ($clients as $client) : ?>
                                <tr>
                                    <td><?= $client['ClientID'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($client['Nom'] . ' ' . $client['Prenoms']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($client['Email']) ?><br>
                                        <?= htmlspecialchars($client['Telephone']) ?>
                                    </td>
                                    <td>
                                        <?php 
                                            // Utilisation d'une fonction d'aide si elle existe, sinon en ligne
                                            $status_class = ($client['Statut'] == 'Actif') ? 'bg-success' : 'bg-danger';
                                        ?>
                                        <span class="badge <?= $status_class ?>"><?= $client['Statut'] ?></span>
                                    </td>
                                    <td>
                                        <a href="clients.php?action=edit&id=<?= $client['ClientID'] ?>" class="btn btn-sm btn-warning text-dark me-2" title="Modifier la fiche"><i class="fas fa-edit"></i> Modifier</a>
                                        
                                        <?php 
                                            $new_status_action = ($client['Statut'] == 'Actif') ? 'Désactiver' : 'Activer';
                                            $btn_class = ($client['Statut'] == 'Actif') ? 'btn-danger' : 'btn-success';
                                            $icon_class = ($client['Statut'] == 'Actif') ? 'fas fa-ban' : 'fas fa-check';
                                            $confirm_msg = ($client['Statut'] == 'Actif') ? 'Voulez-vous vraiment désactiver ce client ?' : 'Voulez-vous vraiment réactiver ce client ?';
                                        ?>
                                        <a href="clients.php?action=toggle_status&id=<?= $client['ClientID'] ?>&current_status=<?= $client['Statut'] ?>" 
                                            class="btn btn-sm <?= $btn_class ?>" 
                                            onclick="return confirm('<?= $confirm_msg ?>')"><i class="<?= $icon_class ?>"></i> <?= $new_status_action ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
