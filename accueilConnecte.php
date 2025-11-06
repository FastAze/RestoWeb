<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoWeb</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <?php
        session_start();
    include 'template/ini.php';
    include 'template/chekEtat.php';
    
    // Traitement de l'ajout de produit - DOIT être traité AVANT tout output HTML
    if (isset($_POST['valider']) && isset($_POST['idProduit']) && isset($_POST['quantite'])) {
        // Connexion à la base de données
        $dbh = db_connect();
        
        // Ajouter du débogage
        error_log("POST reçu: " . print_r($_POST, true));
        
        $idProduit = $_POST['idProduit'];
        $quantite = $_POST['quantite'];
        
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

            // Rafraîchir la page après ajout réussi
            header("Location: accueilConnecte.php");
            exit();
        } catch (PDOException $ex) {
            echo "Erreur: " . $ex->getMessage();
        }
    }
    
    // Vérifier si l'utilisateur est connecté et a une commande active
    if (isset($_SESSION['user_id'])) {
        verifierEtatCommande($_SESSION['user_id']);
    }

    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit();
    }

    // Gestion de l'affichage de l'article choisi en PHP
    $afficherArticle = isset($_GET['article']);
    $produitChoisi = null;
    
    if ($afficherArticle) {
        $dbh = db_connect();
        $idProduit = (int)$_GET['article'];
        
        $sql = "SELECT idProduit, libProduit, prixProduitHT FROM produit WHERE idProduit = :idProduit";
        try {
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':idProduit', $idProduit, PDO::PARAM_INT);
            $sth->execute();
            $produitChoisi = $sth->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            error_log("Erreur lors de la requête SQL : " . $ex->getMessage());
        }
    }

    $username = "Nom d'utilisateur";

    if (isset($_SESSION['user_id'])) {
        $dbh = db_connect();
        $sql = "SELECT loginUtil FROM utilisateur WHERE idUtil = :user_id";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute([':user_id' => $_SESSION['user_id']]);
            
            $user = $sth->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $username = htmlspecialchars($user['loginUtil']);
            }
        } catch (PDOException $ex) {
            error_log("Erreur lors de la requête SQL : " . $ex->getMessage());
        }
    }
    ?>

    <nav>
        <div class="nav-top">
            <div class="logo"><a href="accueilConnecte.php">RestoWeb</a></div>
            <div class="pannier-notif">
                <a class="pannier" href="panier.php">Panier</a>
                <a><img src="image/notif.png" alt="notif"></a>
            </div>
        </div>
        
        <div class="auth-buttons">
            <a class="logout" href="?logout=1">Déconnexion</a>
            <a class="profile" href="profile.php"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
        </div>
    </nav>

    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>

    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
            include "component/componentArticle.php";
            ?>
        </div>
    </section>

    <?php 
    if ($afficherArticle && $produitChoisi) {
        // Déterminer le chemin de l'image
        $lib = $produitChoisi['libProduit'];
        if (file_exists('image/' . $lib . '.png')) {
            $imgWeb = 'image/' . $lib . '.png';
        } else {
            $imgWeb = 'image/pizza.jpg';
        }
    ?>
    <div class="voir-article-overlay show" id="voirArticleOverlay">
        <section class="voir-article">
            <div class="voir-article-img">
                <img src="<?php echo htmlspecialchars($imgWeb); ?>" alt="<?php echo htmlspecialchars($produitChoisi['libProduit']); ?>">
            </div>
            <div class="voir-article-details">
                <h2 class="voir-article-nom"><?php echo htmlspecialchars($produitChoisi['libProduit']); ?></h2>
                <div class="voir-article-prix">Prix : <?php echo $produitChoisi['prixProduitHT']; ?>€</div>
                <form method="POST" action="accueilConnecte.php" class="voir-article-btns">
                    <input type="hidden" name="idProduit" value="<?php echo $produitChoisi['idProduit']; ?>">
                    <input type="number" name="quantite" placeholder="1" min="1" value="1" required>
                    <button type="submit" class="valider-btn" name="valider">Valider</button>
                    <a href="accueilConnecte.php" class="retour-btn">Retour</a>
                </form>
            </div>
        </section>
    </div>
    <?php 
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des notifications
            const notifSection = document.querySelector('.notification');
            const notifIcon = document.querySelector('.pannier-notif a img');
            
            if (notifSection) {
                notifSection.style.display = 'none';
            }
            
            if (notifIcon) {
                notifIcon.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (notifSection) {
                        notifSection.style.display = (notifSection.style.display === 'none' || notifSection.style.display === '') ? 'flex' : 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
