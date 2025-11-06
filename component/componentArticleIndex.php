<?php
    include "template/ini.php";
    // Connexion à la base de données
    $dbh = db_connect();
    $sql = "SELECT idProduit, libProduit, prixProduitHT FROM produit";
    try {
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $produits = $sth->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        die("Erreur lors de la requête SQL : " . $ex->getMessage());
    }

    foreach ($produits as $produit) {
        $lib = isset($produit['libProduit']) ? $produit['libProduit'] : 'produit';

        if (file_exists('image/' . $lib . '.png')) {
            $imgWeb = 'image/' . $lib . '.png';
        } else {
            $imgWeb = 'image/pizza.jpg';
        }   

        echo '<a href="connection.php?article=' . $produit['idProduit'] . '" class="article" data-id="' . $produit['idProduit'] . '">';
        echo '<img src="' . htmlspecialchars($imgWeb) . '" alt="' . htmlspecialchars($lib) . '">';
        echo '<h2>' . htmlspecialchars($lib) . '</h2>';
        echo '<h3>' . htmlspecialchars($produit['prixProduitHT']) . '€</h3>';
        echo '</a>';
    }
?>