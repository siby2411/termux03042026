<?php
require_once 'db_connect.php';
require_once 'etiquette_pdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargez PHPMailer manuellement (à adapter selon votre installation)
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

function envoyer_etiquette_par_email($colis_id, $email_destinataire, $nom_destinataire = 'Client') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT numero_suivi FROM colis WHERE id = ?");
    $stmt->execute([$colis_id]);
    $colis = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$colis) return false;
    
    $pdf_content = generer_pdf_etiquette_memoire($colis_id);
    if (!$pdf_content) return false;
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';       // modifiez selon votre hébergeur
        $mail->SMTPAuth = true;
        $mail->Username = 'votre_email@gmail.com';
        $mail->Password = 'votre_mdp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('contact@dieynabakeita.com', 'Dieynaba Keita');
        $mail->addAddress($email_destinataire, $nom_destinataire);
        $mail->isHTML(true);
        $mail->Subject = 'Étiquette de votre colis n° ' . $colis['numero_suivi'];
        $mail->Body = '<p>Bonjour,</p><p>Veuillez trouver en pièce jointe l\'étiquette PDF de votre colis.</p><p>Cordialement,<br>Dieynaba Keita</p>';
        $mail->AltBody = 'Bonjour, Veuillez trouver en pièce jointe l\'étiquette de votre colis.';
        $mail->addStringAttachment($pdf_content, 'etiquette_'.$colis['numero_suivi'].'.pdf', 'base64', 'application/pdf');
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur envoi email: " . $mail->ErrorInfo);
        return false;
    }
}
?>
