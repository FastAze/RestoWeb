<?php

require_once "../template/ini.php";
$dbh = db_connect();

$idCommande = isset($_GET['id_commande']) ? $_GET['id_commande'] : 0;

if ($idCommande > 0) {        
    // Requête pour passer la commande à l'état "abandonnée"
    $sql = "UPDATE commande SET idEtat = 5 WHERE idCommande = :idCommande";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':idCommande', $idCommande, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $response["message"] = "OK - Commande refuser";
    } else {
        $response["message"] = "KO - Commande non trouvée";
    }
}

$json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
header("Content-type: application/json; charset=utf-8");
echo $json;

/*
URL de test :
http://localhost/www/api/commande_refuser.php?id_commande=XX
*/