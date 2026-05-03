<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

try {
    // remove AUTO entries
    \$pdo->exec(\"DELETE FROM VENTILATION_ITEMS WHERE item_code LIKE 'AUTO-%'\");
    \$stmt = \$pdo->query(\"SELECT compte_id,intitule_compte,classe FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id\");
    \$ins = \$pdo->prepare(\"INSERT INTO VENTILATION_ITEMS (item_code,item_label,item_type,criteria,display_order) VALUES (?,?,?,?,?)\");
    \$i = 1;
    while(\$r = \$stmt->fetch(PDO::FETCH_ASSOC)){
        \$compte = (int)\$r['compte_id'];
        \$label = \$r['intitule_compte'] ?: 'Compte '.\$compte;
        \$classe = (int)\$r['classe'];
        if(\$classe >= 60 && \$classe < 70) \$type='CR_CHARGES';
        elseif(\$classe >= 70 && \$classe < 80) \$type='CR_PRODUITS';
        elseif(\$classe >=1 && \$classe <20) \$type='BILAN_PASSIF';
        else \$type='BILAN_ACTIF';
        \$criteria = json_encode(['compte_id'=>\$compte]);
        \$code = 'AUTO-'.str_pad(\$compte,6,'0',STR_PAD_LEFT);
        \$ins->execute([\$code,\$label,\$type,\$criteria,\$i++]);
    }
    header('Location: ventilation.php?status=ok');
} catch(Exception \$e){
    die('Erreur: '.\$e->getMessage());
}
