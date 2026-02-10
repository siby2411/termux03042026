<?php
namespace App\Controllers;

use App\Services\CsvParser;
use App\Models\Db;

class EcritureController {

    public function uploadForm() {
        require __DIR__ . '/../Views/upload_form.php';
    }

    public function processUpload() {

        if (!isset($_FILES["file"])) {
            die("Aucun fichier envoyé.");
        }

        $parser = new CsvParser($_FILES["file"]["tmp_name"]);
        $rows = $parser->parse();

        $pdo = Db::getInstance()->getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO ECRITURES_COMPTABLES(societe_id, date_operation, compte_debite_id, compte_credite_id, montant)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($rows as $r) {
            $stmt->execute([
                1,                      // Societe par défaut
                $r["date_operation"],
                $r["compte_debite_id"],
                $r["compte_credite_id"],
                $r["montant"]
            ]);
        }

        require __DIR__ . '/../Views/success_view.php';
    }
}
