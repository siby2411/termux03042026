<?php
class Database {
    private $host = "localhost";
    private $db_name = "restaurant_management";
    private $username = "root";
    private $password = ""; // Vide pour le mode OMEGA Termux
    private $socket = "/var/run/mysqld/mysqld.sock";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Version OMEGA : On ajoute unix_socket au DSN
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";unix_socket=" . $this->socket;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Tentative de secours classique si le socket échoue
            try {
                $this->conn = new PDO("mysql:host=127.0.0.1;dbname=" . $this->db_name, $this->username, $this->password);
            } catch(PDOException $e) {
                echo "Erreur OMEGA de connexion: " . $e->getMessage();
            }
        }
        return $this->conn;
    }
}

// Configuration upload images (Gardé tel quel)
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

function uploadImage($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) { throw new Exception('Erreur de téléchargement'); }
    if ($file['size'] > MAX_FILE_SIZE) { throw new Exception('Fichier trop volumineux'); }
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_TYPES)) { throw new Exception('Type non autorisé'); }
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
    $filePath = UPLOAD_DIR . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $filePath)) { throw new Exception('Erreur enregistrement'); }
    return $fileName;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
