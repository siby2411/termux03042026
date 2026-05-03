<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .api-card { border-radius: 15px; overflow: hidden; transition: transform 0.2s; margin-bottom: 20px; }
    .api-card:hover { transform: translateY(-5px); }
    .code-block { background: #1a1a2e; color: #ffaa66; padding: 15px; border-radius: 10px; font-family: monospace; overflow-x: auto; }
    .definition { background: linear-gradient(135deg, #0a2b44, #1e4a76); color: white; padding: 25px; border-radius: 15px; margin-bottom: 25px; }
</style>

<div class="definition">
    <h1><i class="fab fa-whatsapp"></i> <i class="fas fa-code"></i> API - Interface de Programmation d'Applications</h1>
    <p class="lead">Une API (Application Programming Interface) est un pont logiciel qui permet à deux applications de communiquer entre elles.</p>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <h3>🌍 WhatsApp + Twilio</h3>
            <p>Twilio agit comme un intermédiaire entre votre application PHP et WhatsApp. Votre code envoie une requête à l'API Twilio, qui transforme et transmet le message à WhatsApp.</p>
        </div>
        <div class="col-md-6">
            <h3>🔧 Utilisation dans le projet</h3>
            <p>Le formulaire d'envoi de notification utilise l'API Twilio en mode Sandbox pour tester l'envoi de messages WhatsApp sur votre numéro sans validation préalable.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card api-card">
            <div class="card-header bg-primary text-white">📱 API WhatsApp & Twilio - Vue d'ensemble</div>
            <div class="card-body">
                <p><strong>API (Application Programming Interface)</strong> : Interface de programmation permettant à votre application d'interagir avec WhatsApp via Twilio.</p>
                <p><strong>Twilio</strong> : Service cloud qui agit comme un relais entre votre code PHP et WhatsApp.</p>
                <p><strong>Sandbox</strong> : Environnement de test gratuit qui permet d'envoyer des messages à des numéros pré-approuvés (sans validation Meta).</p>
                <div class="code-block mt-3">
                    <?php
                    echo "// Exemple d'appel API depuis votre code\n";
                    echo "\$result = sendWhatsAppTwilio(\n";
                    echo "    'whatsapp:+221776542803',\n";
                    echo "    'Votre colis est en route !'\n";
                    echo ");\n";
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card api-card">
            <div class="card-header bg-success text-white">⚡ Intérêt du formulaire d'envoi via API</div>
            <div class="card-body">
                <ul>
                    <li><strong>📨 Automatisation</strong> : Envoi automatique des notifications lors des changements de statut</li>
                    <li><strong>⏱️ Rapidité</strong> : Messages délivrés en quelques secondes</li>
                    <li><strong>📊 Traçabilité</strong> : Logs conservés dans la base de données</li>
                    <li><strong>🎨 Personnalisation</strong> : Messages formatés avec emojis, liens, QR codes</li>
                    <li><strong>🔒 Sécurité</strong> : Authentification via token Twilio</li>
                    <li><strong>💰 Économique</strong> : Mode sandbox gratuit pour les tests</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">📋 Architecture technique</div>
            <div class="card-body">
                <pre class="code-block">
Application PHP (admin_colis.php)
        │
        ├── 1. Changement de statut colis
        │
        ▼
Fonction sendWhatsAppTwilio()
        │
        ├── 2. Construction de la requête HTTP POST
        ├── 3. Authentification (Account SID + Auth Token)
        │
        ▼
API Twilio (api.twilio.com)
        │
        ├── 4. Réception de la requête
        ├── 5. Vérification du Sandbox
        │
        ▼
WhatsApp Business API
        │
        └── 6. Délivrance du message sur votre téléphone
                </pre>
                <p class="mt-3 text-muted"><i class="fab fa-whatsapp"></i> <strong>Le Sandbox Twilio</strong> : Environnement de test gratuit qui vous permet d'envoyer des messages à votre propre numéro sans avoir à valider votre application auprès de Meta (WhatsApp). Idéal pour le développement et les tests.</p>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
