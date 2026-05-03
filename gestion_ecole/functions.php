<?php
function genererCode($type) {
    global $conn;
    $annee = date('Y');
    
    // Verrouiller la table pour éviter les doublons en cas de clics simultanés
    $conn->query("UPDATE compteurs SET dernier_numero = dernier_numero + 1 WHERE type_code = '$type'");
    $res = $conn->query("SELECT dernier_numero FROM compteurs WHERE type_code = '$type'");
    $row = $res->fetch_assoc();
    $num = str_pad($row['dernier_numero'], 4, '0', STR_PAD_LEFT);
    
    $prefixe = ($type == 'ETUDIANT') ? 'ETU' : 'PRO';
    return "$prefixe-$annee-$num";
}
?>
