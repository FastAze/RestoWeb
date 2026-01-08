<?php
require_once "../template/ini.php";
$dbh = db_connect();

// Récupérer l'ID de la commande depuis l'URL
$idCommande = isset($_GET['idCommande']) ? $_GET['idCommande'] : 0;

$sql = "SELECT L.idProduit, P.libProduit, L.quantite, C.idCommande, C.dateHeureCom, U.loginUtil
    FROM commande C, lignedecommande L, produit P, utilisateur U
    WHERE L.idProduit=P.idProduit
    AND C.idUtilisateur=U.idUtilisateur
    AND C.idCommande=L.idCommande
    AND C.idCommande = :idCommande
    GROUP BY L.idProduit
    ORDER BY L.idProduit ASC;";

$stmt = $dbh->prepare($sql);
$stmt->bindParam(':idCommande', $idCommande, PDO::PARAM_INT);
$stmt->execute();
$les_commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Envoi du contenu au format JSON
$json = json_encode($les_commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
header("Content-type: application/json; charset=utf-8");
echo $json;

/*
URL de test :
http://localhost/www/api/detail_commande.php?idCommande=XX
*/