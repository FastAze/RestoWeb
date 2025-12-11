<?php
require_once "../template/ini.php";

// Connexion à la base de données
$connexion = new PDO('mysql:host=localhost;dbname=restoweb;charset=utf8', 'root', '');
$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Requête SQL directe pour récupérer les commandes en attente
$sql = "SELECT c.idCommande, c.dateHeureCom, c.totalTTC, c.typeCom, c.idEtat, c.idUtilisateur, 
         e.libEtat, u.loginUtil, u.emailUtil
    FROM commande c
    INNER JOIN etat e ON c.idEtat = e.idEtat
    INNER JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
    WHERE c.idEtat = 4
    ORDER BY c.dateHeureCom ASC";

$stmt = $connexion->prepare($sql);
$stmt->execute();
$les_commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Envoi du contenu au format JSON
$json = json_encode($les_commandes, JSON_PRETTY_PRINT);
header("Content-type: application/json; charset=utf-8");
echo $json;