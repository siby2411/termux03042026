#!/bin/bash
# ============================================================
# SCRIPT 04 — FICHIERS PHP CORE (config + DB + helpers)
# Généré via cat << 'EOF' pour chaque fichier
# ============================================================

BASE="$HOME/shared/htdocs/apachewsl2026"

# ─────────────────────────────────────────────────────────────
# 4.1 PHARMACIE — config/config.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/config/config.php"
<?php
/**
 * Configuration principale — Pharmacie
 * Adapté à l'environnement Termux/proot-distro/MariaDB
 */

define('APP_NAME',    'PharmaSen');
define('APP_VERSION', '1.0.0');
define('APP_LANG',    'fr');
define('APP_TIMEZONE','Africa/Dakar');
define('DEVISE',      'FCFA');
define('TVA_DEFAULT', 0);   // Médicaments exonérés TVA au Sénégal

// Base de données
define('DB_HOST',   '127.0.0.1');
define('DB_PORT',   '3306');
define('DB_NAME',   'pharmacie');
define('DB_USER',   'root');
define('DB_PASS',   '');
define('DB_CHARSET','utf8mb4');

// Chemins
define('ROOT_PATH',    dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('LOG_PATH',     ROOT_PATH . '/logs');
define('TMPL_PATH',    ROOT_PATH . '/templates');

// Session
define('SESSION_LIFETIME', 3600); // 1 heure

// POS
define('TICKET_ENTETE',  "PHARMACIE — DAKAR\nTél : +221 XX XXX XX XX\nNINEA : XXXXXXXXXXX");
define('TICKET_PIED',    "Merci de votre visite\nConservez votre ticket");

date_default_timezone_set(APP_TIMEZONE);
EOF

# ─────────────────────────────────────────────────────────────
# 4.2 PHARMACIE — core/Database.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/core/Database.php"
<?php
/**
 * Classe Database — Singleton PDO MariaDB
 * Pharmacie PharmaSen
 */

require_once dirname(__DIR__) . '/config/config.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
                );
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci,
                                                        time_zone = '+00:00'"
                ]);
            } catch (PDOException $e) {
                error_log('[DB ERROR] ' . $e->getMessage());
                http_response_code(500);
                die(json_encode(['error' => 'Connexion base de données impossible']));
            }
        }
        return self::$instance;
    }

    /** Requête SELECT — retourne un tableau */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Requête SELECT — retourne une ligne */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** INSERT / UPDATE / DELETE */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Retourne le dernier ID inséré */
    public static function lastId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    public static function beginTransaction(): void { self::getInstance()->beginTransaction(); }
    public static function commit(): void           { self::getInstance()->commit(); }
    public static function rollback(): void         { self::getInstance()->rollBack(); }
}
EOF

# ─────────────────────────────────────────────────────────────
# 4.3 PHARMACIE — core/Auth.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/core/Auth.php"
<?php
/**
 * Gestion Authentification & Sessions — PharmaSen
 */

require_once __DIR__ . '/Database.php';

class Auth
{
    /** Connexion utilisateur */
    public static function login(string $login, string $password): bool
    {
        session_start();
        $user = Database::queryOne(
            "SELECT * FROM utilisateurs WHERE login = ? AND actif = 1",
            [$login]
        );
        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            return false;
        }
        Database::execute(
            "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?",
            [$user['id']]
        );
        $_SESSION['user'] = [
            'id'     => $user['id'],
            'nom'    => $user['nom'] . ' ' . $user['prenom'],
            'login'  => $user['login'],
            'role'   => $user['role'],
        ];
        $_SESSION['login_time'] = time();
        return true;
    }

    public static function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /pharmacie/login.php');
        exit;
    }

    public static function check(): void
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header('Location: /pharmacie/login.php');
            exit;
        }
        // Expiration session
        if ((time() - ($_SESSION['login_time'] ?? 0)) > SESSION_LIFETIME) {
            self::logout();
        }
    }

    public static function hasRole(string ...$roles): bool
    {
        return in_array($_SESSION['user']['role'] ?? '', $roles, true);
    }

    public static function getUser(): array
    {
        return $_SESSION['user'] ?? [];
    }

    public static function requireRole(string ...$roles): void
    {
        self::check();
        if (!self::hasRole(...$roles)) {
            http_response_code(403);
            die('<h2>Accès refusé</h2>');
        }
    }
}
EOF

# ─────────────────────────────────────────────────────────────
# 4.4 PHARMACIE — core/Helper.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/core/Helper.php"
<?php
/**
 * Fonctions utilitaires — PharmaSen Sénégal
 */

class Helper
{
    /** Formater montant en FCFA */
    public static function fcfa(float $montant): string
    {
        return number_format($montant, 0, ',', ' ') . ' ' . DEVISE;
    }

    /** Nettoyer entrée utilisateur */
    public static function sanitize(mixed $val): string
    {
        return htmlspecialchars(strip_tags(trim((string)$val)), ENT_QUOTES, 'UTF-8');
    }

    /** Générer code aléatoire */
    public static function genCode(string $prefix, int $len = 6): string
    {
        return $prefix . '-' . strtoupper(bin2hex(random_bytes((int)ceil($len / 2))))
                                 . date('d');
    }

    /** Date FR */
    public static function dateFr(?string $date): string
    {
        if (!$date) return '—';
        return date('d/m/Y', strtotime($date));
    }

    /** DateTime FR */
    public static function datetimeFr(?string $dt): string
    {
        if (!$dt) return '—';
        return date('d/m/Y H:i', strtotime($dt));
    }

    /** Réponse JSON standardisée */
    public static function jsonResponse(bool $success, mixed $data = null, string $message = ''): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success, 'data' => $data, 'message' => $message],
                         JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Pagination */
    public static function paginate(int $total, int $page, int $perPage = 25): array
    {
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        return ['total' => $total, 'pages' => $pages, 'current' => $page,
                'offset' => $offset, 'per_page' => $perPage];
    }

    /** Logger */
    public static function log(string $message, string $level = 'INFO'): void
    {
        $line = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $message);
        @file_put_contents(LOG_PATH . '/app.log', $line, FILE_APPEND);
    }
}
EOF

echo "✅ Fichiers core pharmacie créés"

# ─────────────────────────────────────────────────────────────
# 4.5 REVENDEUR MÉDICAL — config/config.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/revendeur_medical/config/config.php"
<?php
/**
 * Configuration principale — Revendeur Matériel Médical
 */

define('APP_NAME',    'MedEquip Pro');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE','Africa/Dakar');
define('DEVISE',      'FCFA');
define('TVA_DEFAULT', 18.00); // TVA 18% sur matériel médical au Sénégal

define('DB_HOST',   '127.0.0.1');
define('DB_PORT',   '3306');
define('DB_NAME',   'revendeur_medical');
define('DB_USER',   'root');
define('DB_PASS',   '');
define('DB_CHARSET','utf8mb4');

define('ROOT_PATH',    dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('LOG_PATH',     ROOT_PATH . '/logs');
define('TMPL_PATH',    ROOT_PATH . '/templates');
define('SESSION_LIFETIME', 7200);

define('SOCIETE_NOM',     'MedEquip Sénégal SARL');
define('SOCIETE_ADRESSE', 'Dakar, Sénégal');
define('SOCIETE_NINEA',   'XXXXXXXXXXX');
define('SOCIETE_TEL',     '+221 XX XXX XX XX');

date_default_timezone_set(APP_TIMEZONE);
EOF

echo "✅ Config revendeur médical créée"
