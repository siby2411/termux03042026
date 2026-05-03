<?php
require_once '../../includes/config/config.php';
require_once '../../includes/classes/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Récupérer la liste des clients et des mécaniciens pour les menus déroulants
$sql_clients = "SELECT id_client, nom, prenom, immatriculation_vehicule FROM clients";
$result_clients = $conn->query($sql_clients);

$sql_mecaniciens = "SELECT id_mecanicien, nom, prenom FROM mecaniciens";
$result_mecaniciens = $conn->query($sql_mecaniciens);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Diagnostic - Omega Garage</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <img src="../../images/banniere_omega.png" alt="Omega Informatique CONSULTING" class="banniere">
        <nav>
            <ul>
                <li><a href="../../index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="liste_diagnostics.php"><i class="fas fa-clipboard-list"></i> Liste Diagnostics</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1><i class="fas fa-clipboard-list"></i> Nouveau Diagnostic</h1>
        <form action="traitement_diagnostic.php" method="POST" class="form-diagnostic">
            <div class="form-group">
                <label for="id_client"><i class="fas fa-user"></i> Client :</label>
                <select id="id_client" name="id_client" required>
                    <option value="">-- Sélectionner un client --</option>
                    <?php while ($client = $result_clients->fetch_assoc()) : ?>
                        <option value="<?= $client['id_client'] ?>">
                            <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom'] . ' (' . $client['immatriculation_vehicule'] . ')') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="symptomes"><i class="fas fa-exclamation-triangle"></i> Symptômes :</label>
                <textarea id="symptomes" name="symptomes" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="diagnostic"><i class="fas fa-diagnoses"></i> Diagnostic :</label>
                <textarea id="diagnostic" name="diagnostic" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="pieces_a_remplacer"><i class="fas fa-cogs"></i> Pièces à remplacer :</label>
                <textarea id="pieces_a_remplacer" name="pieces_a_remplacer" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="main_d_oeuvre"><i class="fas fa-tools"></i> Main d'œuvre :</label>
                <textarea id="main_d_oeuvre" name="main_d_oeuvre" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="cout_estime"><i class="fas fa-money-bill-wave"></i> Coût estimé (FCFA) :</label>
                <input type="number" step="0.01" id="cout_estime" name="cout_estime">
            </div>
            <div class="form-group">
                <label for="id_mecanicien_1"><i class="fas fa-user-cog"></i> Mécanicien 1 :</label>
                <select id="id_mecanicien_1" name="id_mecanicien_1">
                    <option value="">-- Sélectionner un mécanicien --</option>
                    <?php while ($mecanicien = $result_mecaniciens->fetch_assoc()) : ?>
                        <option value="<?= $mecanicien['id_mecanicien'] ?>">
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
                    while ($mecanicien = $result_mecaniciens->fetch_assoc()) : ?>
                        <option value="<?= $mecanicien['id_mecanicien'] ?>">
                            <?= htmlspecialchars($mecanicien['nom'] . ' ' . $mecanicien['prenom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer le Diagnostic</button>
        </form>
    </main>
    <footer>
        <p>© 2026 Omega Informatique CONSULTING – Tous droits réservés</p>
    </footer>
</body>
</html>
