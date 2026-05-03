// test_whatsapp_twilio.php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

$sid = "VOTRE_TWILIO_SID";
$token = "VOTRE_TWILIO_TOKEN";
$twilio = new Client($sid, $token);

// Envoyer 778084201 (WhatsApp Business) -> 776542803 (WhatsApp personnel)
$message = $twilio->messages->create(
    "whatsapp:+221776542803",
    [
        "from" => "whatsapp:+221778084201",
        "body" => "Test API Twilio – Dieynaba GP Holding. Si vous recevez ce message, le canal est opérationnel."
    ]
);

echo "Message envoyé : " . $message->sid;
