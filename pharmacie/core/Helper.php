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
