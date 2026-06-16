<?php include 'header_ecole.php'; ?>
<div class="container mt-5">
    <div class="card shadow col-md-6 mx-auto p-4">
        <h4>Rechercher la Fiche de Suivi</h4>
        <form action="fiche_suivi.php" method="GET">
            <div class="mb-3">
                <label>Entrez le Code Étudiant</label>
                <input type="text" name="code_etudiant" class="form-control" placeholder="ex: ETU-2026-100" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Afficher la fiche</button>
        </form>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
