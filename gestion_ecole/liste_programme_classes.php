<?php include 'header_ecole.php'; 
require_once 'db_connect_ecole.php'; 
$conn = db_connect_ecole(); ?>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #2c3e50;">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">OMEGA CONSULTING</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="liste_programme_classes.php">Programmes</a></li>
        <li class="nav-item"><a class="nav-link" href="etat_classe.php">Finances</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <div class="p-5 mb-5 rounded shadow text-white" style="background: linear-gradient(135deg, #2c3e50, #8e44ad);">
        <h1 class="display-5 fw-bold">OMEGA INFORMATIQUE CONSULTING</h1>
        <p class="lead">Gestion École de Formation | Référentiel des Programmes Académiques</p>
        <hr class="my-4 border-light">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <span>Visualisation en temps réel des unités de valeur par filière</span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3 border-0">
            <h4 class="mb-0 text-dark"><i class="bi bi-book-half me-2 text-primary"></i>Programmes par Classe</h4>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Classe</th>
                        <th>Unités de Valeur (UV)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = $conn->query("SELECT c.nom_class, GROUP_CONCAT(u.nom_uv ORDER BY u.nom_uv SEPARATOR ', ') as liste_uv 
                                     FROM programme_classe pc 
                                     JOIN classes c ON pc.classe_id = c.id 
                                     JOIN uvs u ON pc.uv_id = u.id 
                                     GROUP BY c.nom_class ORDER BY c.nom_class ASC");
                while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge bg-dark fs-6 px-3"><?= htmlspecialchars($row['nom_class']) ?></span></td>
                        <td class="text-secondary"><?= htmlspecialchars($row['liste_uv']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center py-4 text-muted bg-light">
    <p class="mb-0">&copy; 2026 OMEGA INFORMATIQUE CONSULTING | Dakar, Sénégal</p>
</footer>

<?php include 'footer_ecole.php'; ?>
