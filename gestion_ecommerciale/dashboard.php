<?php
// Fichier : dashboard.php
// Page d'accueil protégée

// 1. Démarrer la session en premier
session_start();

// 2. Protection de session : Si l'utilisateur n'est pas connecté, redirection vers le login
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

// Récupérer le nom de l'utilisateur pour l'affichage
$nom_vendeur = $_SESSION['nom_vendeur'] ?? 'Utilisateur';

// 3. INCLUSION DU HEADER
// Ceci ouvre le HTML, le <head>, le <body> et la balise <div class="container mt-4 mb-5">
include 'header.php';
?>

<div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
    <h1 class="display-5 fw-bold">Bienvenue, <?php echo htmlspecialchars($nom_vendeur); ?> !</h1>
    <p class="col-md-8 fs-4">
        Vous êtes connecté au système de gestion commerciale. Utilisez le menu de navigation ci-dessus ou les liens rapides ci-dessous pour accéder aux modules.
    </p>
</div>

<h2 class="mt-5 mb-4 border-bottom pb-2">Modules et Actions Rapides</h2>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php
    // Liste des modules avec les informations nécessaires (icône, titre, lien, couleur Bootstrap)
    $modules = [
        ['href' => 'crud_produits.php', 'title' => 'Gestion des Produits', 'icon' => '📦', 'color' => 'primary'],
        ['href' => 'crud_clients.php', 'title' => 'Gestion des Clients', 'icon' => '👥', 'color' => 'info'],
        ['href' => 'facturation.php', 'title' => 'Nouvelle Facture/BL/BC', 'icon' => '📄', 'color' => 'success'],
        ['href' => 'crud_appro.php', 'title' => 'Enregistrement Approvisionnement', 'icon' => '🚚', 'color' => 'warning'],
        ['href' => 'stock_dashboard.php', 'title' => 'Suivi des Stocks et Alertes', 'icon' => '📈', 'color' => 'secondary'],
        ['href' => 'rapports_ventes.php', 'title' => 'Rapports de Ventes', 'icon' => '💰', 'color' => 'dark'],
        ['href' => 'logout.php', 'title' => 'Déconnexion Sécurisée', 'icon' => '🚪', 'color' => 'danger']
    ];

    foreach ($modules as $module) {
        // Affichage de chaque module dans une "Card" Bootstrap pour un look moderne
        echo "<div class='col'>";
        echo "<div class='card h-100 border-{$module['color']}'>";
        echo "<div class='card-body d-flex flex-column'>";
        echo "<h5 class='card-title text-{$module['color']}'><span class='me-2'>{$module['icon']}</span> {$module['title']}</h5>";
        echo "<p class='card-text'>Accédez à la gestion des {$module['title']} de l'application.</p>";
        // Bouton d'accès au module
        echo "<a href='{$module['href']}' class='btn btn-outline-{$module['color']} mt-auto'>Accéder au Module</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</div>

<?php
// 4. INCLUSION DU FOOTER
// Ceci ferme la balise </div> (conteneur), <footer>, </body>, et </html>
include 'footer.php';
?>
