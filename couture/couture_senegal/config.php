<?php
// ============================================================
// OMEGA INFORMATIQUE CONSULTING
// Configuration Base de Données - Couture Mode Sénégal
// ============================================================

define('DB_HOST',     'localhost');
define('DB_USER',     'root');
define('DB_PASS',     '');          // Modifier selon votre config
define('DB_NAME',     'couture_senegal');
define('DB_CHARSET',  'utf8mb4');

define('APP_NAME',    'CoutureSn Pro');
define('APP_COMPANY', 'OMEGA INFORMATIQUE CONSULTING');
define('APP_VERSION', '2.0');
define('APP_DEVISE',  'FCFA');

// Connexion PDO
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="background:#ff4444;color:#fff;padding:20px;font-family:sans-serif;border-radius:8px;margin:20px;">
                <h3>❌ Erreur de connexion à la base de données</h3>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Vérifiez vos paramètres dans config.php et exécutez setup.sql</p>
            </div>');
        }
    }
    return $pdo;
}

// Formatage montant
function formatMontant(float $montant): string {
    return number_format($montant, 0, ',', ' ') . ' ' . APP_DEVISE;
}

// Formatage date FR
function formatDate(string $date): string {
    if (!$date) return '-';
    $d = new DateTime($date);
    $mois = ['','Janv','Févr','Mars','Avr','Mai','Juin','Juil','Août','Sept','Oct','Nov','Déc'];
    return $d->format('d') . ' ' . $mois[(int)$d->format('m')] . ' ' . $d->format('Y');
}

// Générer code unique
function genererCode(string $prefix, string $table, string $colonne): string {
    $pdo = getDB();
    $annee = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as nb FROM `$table` WHERE `$colonne` LIKE '$prefix-$annee-%'");
    $row = $stmt->fetch();
    $num = str_pad($row['nb'] + 1, 4, '0', STR_PAD_LEFT);
    return "$prefix-$annee-$num";
}

// Message flash
function setFlash(string $type, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Badge statut commande
function badgeStatutCommande(string $statut): string {
    $map = [
        'brouillon'  => ['⚙️', '#94a3b8'],
        'confirmée'  => ['✅', '#3b82f6'],
        'en_cours'   => ['🧵', '#f59e0b'],
        'essayage'   => ['👗', '#8b5cf6'],
        'terminée'   => ['🏁', '#10b981'],
        'livrée'     => ['🎁', '#06b6d4'],
        'annulée'    => ['❌', '#ef4444'],
    ];
    $info = $map[$statut] ?? ['•', '#999'];
    return "<span class='badge' style='background:{$info[1]}'>{$info[0]} " . ucfirst($statut) . "</span>";
}

// Badge statut facture
function badgeStatutFacture(string $statut): string {
    $map = [
        'draft'          => ['📝', '#94a3b8'],
        'émise'          => ['📤', '#3b82f6'],
        'payée_partiel'  => ['💛', '#f59e0b'],
        'payée'          => ['💚', '#10b981'],
        'annulée'        => ['❌', '#ef4444'],
    ];
    $info = $map[$statut] ?? ['•', '#999'];
    return "<span class='badge' style='background:{$info[1]}'>{$info[0]} " . ucfirst(str_replace('_',' ',$statut)) . "</span>";
}
