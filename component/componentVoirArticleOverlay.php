<?php
    include "template/ini.php";
    // Connexion à la base de données
    $dbh = db_connect();
    
    // Récupération des produits pour affichage
    $sql = "SELECT idProduit, libProduit, prixProduitHT FROM produit";
    try {
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $produits = $sth->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        die("Erreur lors de la requête SQL : " . $ex->getMessage());
    }
?>
    
<div class="voir-article-overlay" id="voirArticleOverlay">
    <section class="voir-article">
        <div class="voir-article-img">
            <img src="" alt="">
        </div>
        <div class="voir-article-details">
            <h2 class="voir-article-nom"></h2>
            <div class="voir-article-prix"></div>
            <div class="voir-article-btns">
                <form id="ajouterProduitForm" method="POST">
                    <input type="hidden" name="idProduit" id="produitId">
                    <input type="number" name="quantite" id="quantiteProduit" placeholder="1" min="1" value="1" required>
                    <button type="submit" class="valider-btn" name="valider"><p>Ajouter au panier</p></button>
                    <button id="closeVoirArticle" class="retour-btn"><p>Retour</p></button>
                </form>
            </div>
        </div>
    </section>
</div>