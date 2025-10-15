<?php
    include "template/ini.php";
    // Connexion à la base de données
    $dbh = db_connect();
    
    // Traitement de l'ajout de produit
    if ($_POST && isset($_POST['action']) && $_POST['action'] === 'ajouter_produit') {
        // Ajouter du débogage
        error_log("POST reçu: " . print_r($_POST, true));
        
        $idProduit = (int)$_POST['idProduit'];
        $quantite = (int)$_POST['quantite'];
        
        error_log("ID Produit: $idProduit, Quantité: $quantite");
        
        // Vérifier si idProduit est valide
        if ($idProduit <= 0) {
            echo 'ID produit invalide: ' . $idProduit;
            exit;
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            echo 'Utilisateur non connecté';
            exit;
        }
        
        $idUtilisateur = $_SESSION['user_id'];
        
        try {
            // Vérifier si le produit existe et récupérer ses informations
            $sqlProduit = "SELECT idProduit, libProduit FROM produit WHERE idProduit = :idProduit";
            $sthProduit = $dbh->prepare($sqlProduit);
            $sthProduit->bindParam(':idProduit', $idProduit, PDO::PARAM_INT);
            $sthProduit->execute();
            $produitExiste = $sthProduit->fetch(PDO::FETCH_ASSOC);
            
            if (!$produitExiste) {
                echo 'Produit non trouvé. ID recherché: ' . $idProduit;
                exit;
            }
            
            // Chercher une commande existante avec l'état "initialisée" pour cet utilisateur
            $sqlCommande = "SELECT idCommande FROM commande WHERE idUtilisateur = :idUtilisateur AND idEtat = 1";
            $sthCommande = $dbh->prepare($sqlCommande);
            $sthCommande->bindParam(':idUtilisateur', $idUtilisateur);
            $sthCommande->execute();
            $commande = $sthCommande->fetch(PDO::FETCH_ASSOC);
            
            if (!$commande) {
                // Créer une nouvelle commande si aucune n'existe
                $sqlNewCommande = "INSERT INTO commande (dateHeureCom, totalTTC, typeCom, idEtat, idUtilisateur) VALUES (NOW(), 0, 0, 1, :idUtilisateur)";
                $sthNewCommande = $dbh->prepare($sqlNewCommande);
                $sthNewCommande->bindParam(':idUtilisateur', $idUtilisateur);
                $sthNewCommande->execute();
                $idCommande = $dbh->lastInsertId();
            } else {
                $idCommande = $commande['idCommande'];
            }
            
           // Vérifier si le produit existe déjà dans la commande
            $sqlLigneExiste = "SELECT quantite FROM lignedecommande WHERE idCommande = :idCommande AND idProduit = :idProduit";
            $sthLigneExiste = $dbh->prepare($sqlLigneExiste);
            $sthLigneExiste->bindParam(':idCommande', $idCommande);
            $sthLigneExiste->bindParam(':idProduit', $idProduit);
            $sthLigneExiste->execute();
            $ligneExiste = $sthLigneExiste->fetch(PDO::FETCH_ASSOC);
            
            if ($ligneExiste) {
                // Mettre à jour la quantité existante
                $nouvelleQuantite = $ligneExiste['quantite'] + $quantite;
                $sqlUpdate = "UPDATE lignedecommande SET quantite = :quantite WHERE idCommande = :idCommande AND idProduit = :idProduit";
                $sthUpdate = $dbh->prepare($sqlUpdate);
                $sthUpdate->bindParam(':quantite', $nouvelleQuantite);
                $sthUpdate->bindParam(':idCommande', $idCommande);
                $sthUpdate->bindParam(':idProduit', $idProduit);
                $sthUpdate->execute();
            } else {
                // Insérer une nouvelle ligne de commande
                $sqlInsert = "INSERT INTO lignedecommande (idCommande, idProduit, quantite) VALUES (:idCommande, :idProduit, :quantite)";
                $sthInsert = $dbh->prepare($sqlInsert);
                $sthInsert->bindParam(':idCommande', $idCommande);
                $sthInsert->bindParam(':idProduit', $idProduit);
                $sthInsert->bindParam(':quantite', $quantite);
                $sthInsert->execute();
            }
        } catch (PDOException $ex) {
            echo "Erreur: " . $ex->getMessage();
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
                    <button id="closeVoirArticle" class="retour-btn"><p>Retour</p></button>
                </form>
            </div>
        </div>
    </section>
</div>