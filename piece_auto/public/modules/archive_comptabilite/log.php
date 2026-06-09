<?php
// public/login.php
$page_title = "Connexion - SynthesePro";
require_once __DIR__ . '/layout.php'; // affichera topbar/sidebar si tu veux, sinon tu peux l'enlever
?>

<style>
.login-card {
    max-width: 420px;
    margin: 40px auto;
    border-radius: 12px;
}
.brand {
    display:flex;
    align-items:center;
    gap:12px;
}
.brand img { height:48px; border-radius:6px; }
.note-tests { font-size:0.9rem; color:#666; margin-top:10px; }
</style>

<div class="container">
    <div class="card shadow-sm login-card">
        <div class="card-body p-4">
            <div class="brand mb-3">
                <img src="omega.jpg" alt="Logo" />
                <div>
                    <h4 class="mb-0">SynthesePro</h4>
                    <small class="text-muted">Plateforme d'analyse financière & comptabilité SYSCOHADA</small>
                </div>
            </div>

            <h5 class="mb-3">Connexion</h5>

            <!-- NOTE : changez l'attribut action pour pointer vers votre script d'auth existant -->
            <form method="post" action="../includes/auth_check.php" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input name="username" class="form-control" required />
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input name="password" type="password" class="form-control" required />
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div><input type="checkbox" name="remember" /> Se souvenir</div>
                    <a href="#" class="small">Mot de passe oublié ?</a>
                </div>

                <button class="btn btn-primary w-100">Se connecter</button>
            </form>

            <div class="note-tests mt-3">
                <strong>Compte de test :</strong><br>
                utilisateur : <code>admin</code> &nbsp; mot de passe : <code>admin123</code>
            </div>
        </div>
    </div>
</div>

