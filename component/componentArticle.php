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

        // Sécuriser le nom pour éviter les parcours de répertoire
        $safe = trim($lib);
        $safe = str_replace(["\0", "../", "..\\", "/", "\\"], '', $safe);

        // Chemins côté serveur
        $fsPng = __DIR__ . '/../image/' . $safe . '.png';
        $fsJpg = __DIR__ . '/../image/' . $safe . '.jpg';

        // Choisir l'image existante (png prioritaire), sinon fallback
        if (file_exists($fsPng)) {
            $imgWeb = 'image/' . rawurlencode($safe) . '.png';
        } elseif (file_exists($fsJpg)) {
            $imgWeb = 'image/' . rawurlencode($safe) . '.jpg';
        } else {
            $imgWeb = 'image/pizza.jpg';
        }

        echo '<article class="article" data-id="' . (int)$produit['idProduit'] . '">';
        echo '<img src="' . htmlspecialchars($imgWeb) . '" alt="' . htmlspecialchars($lib) . '">';
        echo '<h2>' . htmlspecialchars($lib) . '</h2>';
        echo '<h3>' . htmlspecialchars($produit['prixProduitHT']) . '€</h3>';
        echo '</article>';
    }
?>