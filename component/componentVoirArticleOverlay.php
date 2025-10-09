<?php
    include "template/ini.php";
    // Connexion à la base de données
    $dbh = db_connect();
    
    // Traitement de l'ajout de produit
    if ($_POST && isset($_POST['action']) && $_POST['action'] === 'ajouter_produit') {
        $idProduit = (int)$_POST['idProduit'];
        $quantite = (int)$_POST['quantite'];
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            echo 'Utilisateur non connecté';
        }
        
        $idUtilisateur = $_SESSION['user_id'];
        
        try {
            $sqlInsert = "INSERT INTO lignedecommande (idCommande, idProduit, quantite, totalHT) VALUES (NULL, :idProduit, :quantite, NULL)";
            $sthInsert = $dbh->prepare($sqlInsert);
            $sthInsert->bindParam(':idProduit', $idProduit);
            $sthInsert->bindParam(':quantite', $quantite);
            
            if ($sthInsert->execute()) {
                echo 'Produit ajouté au panier avec succès.';
            } else {
                echo 'Erreur lors de l\'ajout du produit au panier.';
            }
        } catch (PDOException $ex) {
            echo "Erreur";
        }
    }
    
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
                    <input type="hidden" name="action" value="ajouter_produit">
                    <input type="hidden" name="idProduit" id="produitId">
                    <input type="number" name="quantite" id="quantiteProduit" placeholder="1" min="1" value="1" required>
                    <button type="submit" class="valider-btn"><p>Ajouter au panier</p></button>
                </form>
                <button id="closeVoirArticle" class="retour-btn"><p>Retour</p></button>
            </div>
        </div>
    </section>
</div>