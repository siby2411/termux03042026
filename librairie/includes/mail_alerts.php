<?php
class MailAlert {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function sendMail($to, $subject, $body) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: OMEGA CONSULTING <noreply@omega-consulting.sn>\r\n";
        
        return @mail($to, $subject, $body, $headers);
    }
    
    public function checkStockAndAlert() {
        $stmt = $this->pdo->query("
            SELECT l.*, u.email as admin_email
            FROM livres l
            CROSS JOIN (SELECT email FROM utilisateurs WHERE role = 'admin' AND email IS NOT NULL LIMIT 1) u
            WHERE l.quantite_stock <= l.quantite_min 
            AND l.quantite_stock > 0
        ");
        $low_stock = $stmt->fetchAll();
        
        foreach ($low_stock as $livre) {
            if ($livre['admin_email']) {
                $this->sendStockAlert($livre['admin_email'], $livre);
            }
        }
    }
    
    public function sendStockAlert($email, $livre) {
        $subject = "[ALERTE] Stock faible - " . $livre['titre'];
        
        $body = "
        <html>
        <head><style>
            body { font-family: Arial, sans-serif; }
            .alert-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; }
            .info { color: #856404; }
        </style></head>
        <body>
            <h2>OMEGA CONSULTING - Alerte Stock</h2>
            <div class='alert-box'>
                <p><strong>Livre :</strong> {$livre['titre']}</p>
                <p><strong>Auteur :</strong> {$livre['auteur']}</p>
                <p><strong>Stock actuel :</strong> <span style='color: red;'>{$livre['quantite_stock']}</span></p>
                <p><strong>Stock minimum :</strong> {$livre['quantite_min']}</p>
            </div>
            <hr>
            <small>Ce message a été généré automatiquement par OMEGA CONSULTING.</small>
        </body>
        </html>";
        
        return $this->sendMail($email, $subject, $body);
    }
}
?>
