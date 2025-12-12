<?php
require_once "../template/ini.php";
$dbh = db_connect();

$sql = "SELECT C.idCommande, C.dateHeureCom, E.libEtat, COUNT(*), C.totalTTC 
    FROM commande C,  etat E, lignedecommande L
    -- produit P, utilisateur U,
    WHERE E.idEtat=C.idEtat 
    -- AND L.idProduit=P.idProduit
    -- AND C.idUtilisateur=U.idUtilisateur
    AND C.idCommande=L.idCommande
    AND (C.idEtat = 4 OR C.idEtat = 6)
    GROUP BY C.idCommande
    ORDER BY C.dateHeureCom ASC;";

$stmt = $dbh->prepare($sql);
$stmt->execute();
$les_commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Envoi du contenu au format JSON
$json = json_encode($les_commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
header("Content-type: application/json; charset=utf-8");
echo $json;

/*
URL de test :
http://localhost/www/api/liste_commandes.php
*/