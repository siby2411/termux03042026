<?php
$host   = 'localhost';
$dbname = 'agence_voyage';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="font-family:monospace;padding:20px;background:#1a0a0a;color:#ff6b6b;border-radius:8px">
        <strong>Erreur DB :</strong> ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Helpers
function money(float $n): string {
    return number_format($n, 0, ',', ' ') . ' FCFA';
}
function ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff/60) . ' min';
    if ($diff < 86400) return floor($diff/3600) . 'h';
    return floor($diff/86400) . 'j';
}
