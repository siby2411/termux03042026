<?php
class NotificationSystem {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Créer la table des notifications si elle n'existe pas
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                type VARCHAR(50),
                title VARCHAR(255),
                message TEXT,
                link VARCHAR(255),
                is_read TINYINT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
                INDEX idx_user_read (user_id, is_read)
            )
        ");
    }
    
    public function add($user_id, $type, $title, $message, $link = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $type, $title, $message, $link]);
    }
    
    public function addForAll($type, $title, $message, $link = null, $roles = null) {
        $sql = "SELECT id FROM utilisateurs WHERE statut = 'actif'";
        if ($roles && is_array($roles)) {
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $sql .= " AND role IN ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($roles);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        
        $users = $stmt->fetchAll();
        foreach ($users as $user) {
            $this->add($user['id'], $type, $title, $message, $link);
        }
    }
    
    public function getUnread($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    public function getAll($user_id, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    }
    
    public function markAsRead($id, $user_id) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $user_id]);
    }
    
    public function markAllAsRead($user_id) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ? AND is_read = 0
        ");
        return $stmt->execute([$user_id]);
    }
    
    public function delete($id, $user_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM notifications 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $user_id]);
    }
    
    public function getCount($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
    
    public function checkStockAlert() {
        $stmt = $this->pdo->query("
            SELECT * FROM livres 
            WHERE quantite_stock <= quantite_min 
            AND quantite_stock > 0
        ");
        $low_stock = $stmt->fetchAll();
        
        foreach ($low_stock as $livre) {
            $this->addForAll(
                'stock',
                'Alerte stock faible',
                "Le livre '{$livre['titre']}' n'a plus que {$livre['quantite_stock']} exemplaire(s).",
                "modules/livres/modifier.php?id={$livre['id']}",
                ['admin', 'gestionnaire']
            );
        }
    }
}
?>
