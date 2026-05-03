#!/bin/bash

echo "========================================="
echo "📱 Test WhatsApp - Dieynaba GP Holding"
echo "📞 Numéro cible: +221 77 654 28 03"
echo "========================================="
echo ""

echo "📦 Liste des colis :"
mysql -u root -p gp_db -e "SELECT id, numero_suivi FROM colis LIMIT 5" 2>/dev/null
echo ""

send_test() {
    echo -n "📤 Envoi colis ID $1 ($2)... "
    result=$(curl -s -X POST http://127.0.0.1:8000/api_whatsapp.php \
        -d "action=send_qr&colis_id=$1&phone=221776542803&type=$2")
    
    if echo "$result" | grep -q '"success":true'; then
        echo "✅ Succès"
    else
        echo "❌ Échec"
    fi
}

# Tester les colis 1 à 5 pour destinataire
for i in 1 2 3 4 5; do
    send_test $i "destinataire"
done

# Tester les colis 1 à 3 pour expéditeur
for i in 1 2 3; do
    send_test $i "expediteur"
done

echo ""
echo "========================================="
echo "✅ Tests terminés"
echo "📱 Vérifiez votre téléphone +221776542803"
echo "========================================="
