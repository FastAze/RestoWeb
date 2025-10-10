<?php
    include "template/ini.php";
    // Connexion à la base de données
    $dbh = db_connect();
    $sql = "SELECT idProduit, libProduit, prixProduitHT FROM produit";
    try {
        // Préparation et exécution de la requête
        $sth = $dbh->prepare($sql);
        $sth->execute();
        
        // Récupération de tous les résultats
        $produits = $sth->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        // Gestion des erreurs
        die("Erreur lors de la requête SQL : " . $ex->getMessage());
    }

    foreach ($produits as $produit) {
        echo '<div class="article" data-id="' . $produit['idProduit'] . '">';
        echo '<img src="image/pizza.jpg" alt="' . htmlspecialchars($produit['libProduit']) . '">';
        echo '<h2>' . htmlspecialchars($produit['libProduit']) . '</h2>';
        echo '<h3>' . htmlspecialchars($produit['prixProduitHT']) . '€</h3>';
        echo '</div>';
    }
?>