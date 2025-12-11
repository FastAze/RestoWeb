<?php

require_once "../template/ini.php";

$date = new DateTime();

$response = [
    "app" => "restoweb",
    "date" => $date->format("d/m/Y H:i:s"),
    "operation" => "accepter_commande",
    "message" => "???"
];

$idCommande = isset($_GET['id_commande']) ? $_GET['id_commande'] : 0;

if ($idCommande > 0) {
    try {
        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=restoweb;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Requête pour passer la commande à l'état "acceptée" (idEtat = 2)
        $sql = "UPDATE commande SET idEtat = 5 WHERE idCommande = :idCommande";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCommande', $idCommande, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $response["message"] = "OK - Commande acceptée";
        } else {
            $response["message"] = "KO - Commande non trouvée";
        }
    } catch (PDOException $e) {
        $response["message"] = "Erreur : " . $e->getMessage();
    }
} else {
    $response["message"] = "KO - ID commande invalide";
}

$json = json_encode($response, JSON_PRETTY_PRINT);
header("Content-type: application/json; charset=utf-8");
echo $json;
