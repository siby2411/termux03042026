<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Helper.php';
require_once dirname(__DIR__,2).'/core/Database.php';
header('Content-Type: application/json; charset=utf-8');
Auth::check();
$a=$_GET['action']??'list';
try {
    switch($a){
        case 'list':
            $s='%'.(Helper::sanitize($_GET['s']??'')).'%';
            Helper::jsonResponse(true,Database::query("SELECT * FROM clients WHERE nom LIKE ? OR telephone LIKE ? ORDER BY nom LIMIT 200",[$s,$s]));
        case 'get':
            Helper::jsonResponse(true,Database::queryOne("SELECT * FROM clients WHERE id=?",[(int)$_GET['id']]));
        case 'create':
            $b=json_decode(file_get_contents('php://input'),true)??[];
            Database::execute("INSERT INTO clients(nom,prenom,telephone,cni,adresse,mutuelle,num_assurance,credit_autorise) VALUES(?,?,?,?,?,?,?,?)",
                [$b['nom'],$b['prenom']??null,$b['telephone'],$b['cni']??null,$b['adresse']??null,$b['mutuelle']??null,$b['num_assurance']??null,$b['credit_autorise']??0]);
            Helper::jsonResponse(true,['id'=>Database::lastId()],'Client créé');
        case 'update':
            $b=json_decode(file_get_contents('php://input'),true)??[];
            Database::execute("UPDATE clients SET nom=?,prenom=?,telephone=?,cni=?,adresse=?,mutuelle=?,credit_autorise=? WHERE id=?",
                [$b['nom'],$b['prenom']??null,$b['telephone'],$b['cni']??null,$b['adresse']??null,$b['mutuelle']??null,$b['credit_autorise']??0,(int)$_GET['id']]);
            Helper::jsonResponse(true,null,'Modifié');
        default: Helper::jsonResponse(false,null,'Action inconnue');
    }
} catch(Throwable $e){ Helper::jsonResponse(false,null,$e->getMessage()); }
