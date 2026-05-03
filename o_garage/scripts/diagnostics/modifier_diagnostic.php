<?php
require_once '../../includes/config/config.php';
require_once '../../includes/classes/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Récupérer l'ID du diagnostic depuis l'URL
$id_diagnostic = intval($_GET['id'] ?? 0);

// Récupérer les détails du diagnostic
$sql = "SELECT * FROM diagnostics WHERE id_diagnostic = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_diagnostic);
$stmt->execute();
$result = $stmt->get_result();
$diagnostic = $result->fetch_assoc();

// Récupérer la liste des clients et des mécaniciens pour les menus déroulants
$sql_clients = "SELECT id_client, nom, prenom, immatriculation_vehicule FROM clients";
$result_clients = $conn->query($sql_clients);

$sql_mecaniciens = "SELECT id_mecanicien, nom, prenom FROM mecaniciens";
$result_mecaniciens = $conn->query($sql_mecaniciens);

$stmt->close();

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = intval($_POST['id_client'] ?? 0);
    $symptomes = htmlspecialchars(trim($_POST['symptomes'] ?? ''));
    $diagnostic_text = htmlspecialchars(trim($_POST['diagnostic'] ?? ''));
    $pieces_a_remplacer = htmlspecialchars(trim($_POST['pieces_a_remplacer'] ?? ''));
    $main_d_oeuvre = htmlspecialchars(trim($_POST['main_d_oeuvre'] ?? ''));
    $cout_estime = floatval($_POST['cout_estime'] ?? 0);
    $id_mecanicien_1 = !empty($_POST['id_mecanicien_1']) ? intval($_POST['id_mecanicien_1']) : null;
    $id_mecanicien_2 = !empty($_POST['id_mecanicien_2']) ? intval($_POST['id_mecanicien_2']) : null;
    $etat = htmlspecialchars(trim($_POST['etat'] ?? ''));

    $sql_update = "UPDATE diagnostics SET
                    id_client = ?,
                    symptomes = ?,
                    diagnostic = ?,
                    pieces_a_remplacer = ?,
                    main_d_oeuvre = ?,
                    cout_estime = ?,
                    id_mecanicien_1 = ?,
                    id_mecanicien_2 = ?,
                    etat = ?
                    WHERE id_diagnostic = ?";

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("issssdiisi", $id_client, $symptomes, $diagnostic_text, $pieces_a_remplacer, $main_d_oeuvre, $cout_estime, $id_mecanicien_1, $id_mecanicien_2, $etat, $id_diagnostic);

    if ($stmt_update->execute()) {
        $message = "Diagnostic mis à jour avec succès !";
        $type = "success";
    } else {
        $message = "Erreur lors de la mise à jour : " . $stmt_update->error;
        $type = "error";
    }

    $stmt_update->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Diagnostic - Omega Garage</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <img src="../../images/banniere_omega.png" alt="Omega Informatique CONSULTING" class="banniere">
        <nav>
            <ul>
                <li><a href="../../index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="liste_diagnostics.php"><i class="fas fa-list"></i> Liste Diagnostics</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1><i class="fas fa-edit"></i> Modifier le Diagnostic #<?= $id_diagnostic ?></h1>
        <?php if (isset($message)): ?>
            <div class="result-message <?= $type ?>">
                <i class="fas <?= ($type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <p><?= $message ?></p>
            </div>
        <?php endif; ?>
        <form action="modifier_diagnostic.php?id=<?= $id_diagnostic ?>" method="POST" class="form-diagnostic">
            <div class="form-group">
                <label for="id_client"><i class="fas fa-user"></i> Client :</label>
                <select id="id_client" name="id_client" required>
                    <option value="">-- Sélectionner un client --</option>
                    <?php
                    $result_clients->data_seek(0);
                    while ($client = $result_clients->fetch_assoc()) :
                        $selected = ($client['id_client'] == $diagnostic['id_client']) ? 'selected' : '';
                    ?>
                        <option value="<?= $client['id_client'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom'] . ' (' . $client['immatriculation_vehicule'] . ')') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="symptomes"><i class="fas fa-exclamation-triangle"></i> Symptômes :</label>
                <textarea id="symptomes" name="symptomes" rows="4" required><?= htmlspecialchars($diagnostic['symptomes']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="diagnostic"><i class="fas fa-diagnoses"></i> Diagnostic :</label>
                <textarea id="diagnostic" name="diagnostic" rows="4" required><?= htmlspecialchars($diagnostic['diagnostic']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="pieces_a_remplacer"><i class="fas fa-cogs"></i> Pièces à remplacer :</label>
                <textarea id="pieces_a_remplacer" name="pieces_a_remplacer" rows="3"><?= htmlspecialchars($diagnostic['pieces_a_remplacer']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="main_d_oeuvre"><i class="fas fa-tools"></i> Main d'œuvre :</label>
                <textarea id="main_d_oeuvre" name="main_d_oeuvre" rows="3"><?= htmlspecialchars($diagnostic['main_d_oeuvre']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="cout_estime"><i class="fas fa-money-bill-wave"></i> Coût estimé (FCFA) :</label>
                <input type="number" step="0.01" id="cout_estime" name="cout_estime" value="<?= $diagnostic['cout_estime'] ?>">
            </div>
            <div class="form-group">
                <label for="id_mecanicien_1"><i class="fas fa-user-cog"></i> Mécanicien 1 :</label>
                <select id="id_mecanicien_1" name="id_mecanicien_1">
                    <option value="">-- Sélectionner un mécanicien --</option>
                    <?php
                    $result_mecaniciens->data_seek(0);
                    while ($mecanicien = $result_mecaniciens->fetch_assoc()) :
                        $selected = ($mecanicien['id_mecanicien'] == $diagnostic['id_mecanicien_1']) ? 'selected' : '';
                    ?>
                        <option value="<?= $mecanicien['id_mecanicien'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($mecanicien['nom'] . ' ' . $mecanicien['prenom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_mecanicien_2"><i class="fas fa-user-cog"></i> Mécanicien 2 :</label>
                <select id="id_mecanicien_2" name="id_mecanicien_2">
                    <option value="">-- Sélectionner un mécanicien --</option>
                    <?php
                    $result_mecaniciens->data_seek(0);
                    while ($mecanicien = $result_mecaniciens->fetch_assoc()) :
                        $selected = ($mecanicien['id_mecanicien'] == $diagnostic['id_mecanicien_2']) ? 'selected' : '';
                    ?>
                        <option value="<?= $mecanicien['id_mecanicien'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($mecanicien['nom'] . ' ' . $mecanicien['prenom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="etat"><i class="fas fa-info-circle"></i> État :</label>
                <select id="etat" name="etat" required>
                    <option value="En attente" <?= ($diagnostic['etat'] == 'En attente') ? 'selected' : '' ?>>En attente</option>
                    <option value="En cours" <?= ($diagnostic['etat'] == 'En cours') ? 'selected' : '' ?>>En cours</option>
                    <option value="Terminé" <?= ($diagnostic['etat'] == 'Terminé') ? 'selected' : '' ?>>Terminé</option>
                    <option value="Facturé" <?= ($diagnostic['etat'] == 'Facturé') ? 'selected' : '' ?>>Facturé</option>
                </select>
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Mettre à jour</button>
        </form>
    </main>
    <footer>
        <p>© 2026 Omega Informatique CONSULTING – Tous droits réservés</p>
    </footer>
</body>
</html>
