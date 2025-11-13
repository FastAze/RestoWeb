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
    // Inclusion des fichiers nécessaires
    include 'template/ini.php';         // Configuration de la base de données
    include 'template/chekEtat.php';    // Fonctions de gestion des commandes
    session_start();                    // Démarrage de la session
    
    // ===== TRAITEMENT DE L'AJOUT DE PRODUIT AU PANIER =====
    // Ce traitement DOIT être effectué AVANT tout output HTML
    if (isset($_POST['valider']) && isset($_POST['idProduit']) && isset($_POST['quantite'])) {
        // Connexion à la base de données
        $dbh = db_connect();
        
        // Log de débogage dans les logs PHP
        error_log("POST reçu: " . print_r($_POST, true));
        
        // Récupération et conversion sécurisée des données
        $idProduit = (int)$_POST['idProduit'];
        $quantite = (int)$_POST['quantite'];
        
        // Log des valeurs récupérées
        error_log("ID Produit: $idProduit, Quantité: $quantite");
        
        // ===== VALIDATION DE L'ID PRODUIT =====
        if ($idProduit <= 0) {
            echo 'ID produit invalide: ' . $idProduit;
            exit;
        }
        
        // ===== VÉRIFICATION DE L'AUTHENTIFICATION =====
        if (!isset($_SESSION['user_id'])) {
            echo 'Utilisateur non connecté';
            exit;
        }
        
        $idUtilisateur = $_SESSION['user_id'];
        
        try {
            // ===== VÉRIFICATION DE L'EXISTENCE DU PRODUIT =====
            $sqlProduit = "SELECT idProduit, libProduit FROM produit WHERE idProduit = :idProduit";
            $sthProduit = $dbh->prepare($sqlProduit);
            $sthProduit->bindParam(':idProduit', $idProduit, PDO::PARAM_INT);
            $sthProduit->execute();
            $produitExiste = $sthProduit->fetch(PDO::FETCH_ASSOC);
            
            // Si le produit n'existe pas dans la base
            if (!$produitExiste) {
                echo 'Produit non trouvé. ID recherché: ' . $idProduit;
                exit;
            }
            
            // ===== RECHERCHE D'UNE COMMANDE ACTIVE =====
            // Recherche une commande existante avec l'état "initialisée" (état = 1)
            $sqlCommande = "SELECT idCommande FROM commande WHERE idUtilisateur = :idUtilisateur AND idEtat = 1";
            $sthCommande = $dbh->prepare($sqlCommande);
            $sthCommande->bindParam(':idUtilisateur', $idUtilisateur);
            $sthCommande->execute();
            $commande = $sthCommande->fetch(PDO::FETCH_ASSOC);
            
            // ===== CRÉATION D'UNE NOUVELLE COMMANDE SI NÉCESSAIRE =====
            if (!$commande) {
                // Insertion d'une nouvelle commande initialisée
                $sqlNewCommande = "INSERT INTO commande (dateHeureCom, totalTTC, typeCom, idEtat, idUtilisateur) 
                                   VALUES (NOW(), 0, 0, 1, :idUtilisateur)";
                $sthNewCommande = $dbh->prepare($sqlNewCommande);
                $sthNewCommande->bindParam(':idUtilisateur', $idUtilisateur);
                $sthNewCommande->execute();
                $idCommande = $dbh->lastInsertId();  // Récupération de l'ID auto-incrémenté
            } else {
                $idCommande = $commande['idCommande'];
            }
            
            // ===== VÉRIFICATION SI LE PRODUIT EST DÉJÀ DANS LA COMMANDE =====
            $sqlLigneExiste = "SELECT quantite FROM lignedecommande WHERE idCommande = :idCommande AND idProduit = :idProduit";
            $sthLigneExiste = $dbh->prepare($sqlLigneExiste);
            $sthLigneExiste->bindParam(':idCommande', $idCommande);
            $sthLigneExiste->bindParam(':idProduit', $idProduit);
            $sthLigneExiste->execute();
            $ligneExiste = $sthLigneExiste->fetch(PDO::FETCH_ASSOC);
            
            if ($ligneExiste) {
                // ===== MISE À JOUR DE LA QUANTITÉ EXISTANTE =====
                // Ajout de la nouvelle quantité à l'ancienne
                $nouvelleQuantite = $ligneExiste['quantite'] + $quantite;
                $sqlUpdate = "UPDATE lignedecommande SET quantite = :quantite WHERE idCommande = :idCommande AND idProduit = :idProduit";
                $sthUpdate = $dbh->prepare($sqlUpdate);
                $sthUpdate->bindParam(':quantite', $nouvelleQuantite);
                $sthUpdate->bindParam(':idCommande', $idCommande);
                $sthUpdate->bindParam(':idProduit', $idProduit);
                $sthUpdate->execute();
            } else {
                // ===== INSERTION D'UNE NOUVELLE LIGNE DE COMMANDE =====
                // Le trigger SQL calculera automatiquement le totalHT
                $sqlInsert = "INSERT INTO lignedecommande (idCommande, idProduit, quantite) VALUES (:idCommande, :idProduit, :quantite)";
                $sthInsert = $dbh->prepare($sqlInsert);
                $sthInsert->bindParam(':idCommande', $idCommande);
                $sthInsert->bindParam(':idProduit', $idProduit);
                $sthInsert->bindParam(':quantite', $quantite);
                $sthInsert->execute();
            }

            // ===== REDIRECTION APRÈS AJOUT RÉUSSI =====
            // Rafraîchir la page pour éviter la resoumission du formulaire
            header("Location: accueilConnecte.php");
            exit();
        } catch (PDOException $ex) {
            // Gestion des erreurs SQL
            echo "Erreur: " . $ex->getMessage();
        }
    }
    
    // ===== VÉRIFICATION/CRÉATION DE COMMANDE AU CHARGEMENT =====
    // Vérifier si l'utilisateur est connecté et a une commande active
    if (isset($_SESSION['user_id'])) {
        verifierEtatCommande($_SESSION['user_id']);
    }

    // ===== GESTION DE LA DÉCONNEXION =====
    if (isset($_GET['logout'])) {
        session_destroy();     // Destruction de toutes les variables de session
        header("Location: index.php");  // Redirection vers la page d'accueil publique
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

    // ===== RÉCUPÉRATION DU NOM D'UTILISATEUR =====
    $username = "Nom d'utilisateur";  // Valeur par défaut

    if (isset($_SESSION['user_id'])) {
        $dbh = db_connect();
        // Requête pour récupérer le nom d'utilisateur depuis la base
        $sql = "SELECT loginUtil FROM utilisateur WHERE idUtil = :user_id";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute([':user_id' => $_SESSION['user_id']]);
            
            $user = $sth->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                // Échappement HTML pour éviter les injections XSS
                $username = htmlspecialchars($user['loginUtil']);
            }
        } catch (PDOException $ex) {
            // Log de l'erreur sans interrompre l'affichage
            error_log("Erreur lors de la requête SQL : " . $ex->getMessage());
        }
    }
    ?>

    <!-- Barre de navigation pour utilisateurs connectés -->
    <nav>
        <!-- Section supérieure de la navigation -->
        <div class="nav-top">
            <!-- Logo du site - lien vers l'accueil connecté -->
            <div class="logo"><a href="accueilConnecte.php">RestoWeb</a></div>
            
            <!-- Liens panier et notifications -->
            <div class="pannier-notif">
                <!-- Lien vers la page du panier -->
                <a class="pannier" href="panier.php">Panier</a>
                <!-- Icône de notification (toggle via JavaScript) -->
                <a><img src="image/notif.png" alt="notif"></a>
            </div>
        </div>
        
        <!-- Boutons d'authentification -->
        <div class="auth-buttons">
            <!-- Bouton de déconnexion -->
            <a class="logout" href="?logout=1">Déconnexion</a>
            <!-- Bouton profil avec nom d'utilisateur -->
            <a class="profile" href="profile.php"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
        </div>
    </nav>

    <!-- Section de notification (masquée par défaut) -->
    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>

    <!-- Section d'affichage des articles (produits) -->
    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
            // Inclusion du composant qui affiche tous les produits
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
            <!-- Zone d'affichage de l'image du produit -->
            <div class="voir-article-img">
                <img src="<?php echo htmlspecialchars($imgWeb); ?>" alt="<?php echo htmlspecialchars($produitChoisi['libProduit']); ?>">
            </div>
            
            <!-- Zone des détails et du formulaire d'ajout -->
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

    <!-- Scripts JavaScript pour l'interactivité -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des notifications
            const notifSection = document.querySelector('.notification');
            const notifIcon = document.querySelector('.pannier-notif a img');
            
            // Masquer la notification au chargement de la page
            if (notifSection) {
                notifSection.style.display = 'none';
            }
            
            // Toggle de l'affichage des notifications au clic sur l'icône
            if (notifIcon) {
                notifIcon.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();  // Empêcher le comportement par défaut du lien
                    
                    if (notifSection) {
                        // Alterner entre affichage et masquage
                        notifSection.style.display = (notifSection.style.display === 'none' || notifSection.style.display === '') ? 'flex' : 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
