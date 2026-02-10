<?php
require_once __DIR__ . '/Db.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Db::getInstance();
    }

    public function authenticate($email, $password) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT * FROM USERS WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                return $user;
            }
            return false;
        } catch (Exception $e) {
            error_log("Auth error: " . $e->getMessage());
            return false;
        }
    }

    public function getSocietes() {
        try {
            $stmt = $this->db->getConnection()->query("SELECT * FROM SOCIETES");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>

