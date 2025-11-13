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
        session_start();       // Démarrage de la session (si pas déjà fait)
        session_destroy();     // Destruction de toutes les variables de session
        header("Location: index.php");  // Redirection vers la page d'accueil publique
        exit();
    }

    include "template/ini.php";

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
            <a class="profile"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
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
    // Inclusion du composant de profil utilisateur
    include 'component/componentProfile.php';
    include "template/ini.php";
    
    // ===== RÉCUPÉRATION DES PRODUITS POUR LA MODAL =====
    $dbh = db_connect();
    
    // Requête pour récupérer tous les produits
    $sql = "SELECT idProduit, libProduit, prixProduitHT FROM produit";
    try {
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $produits = $sth->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        die("Erreur lors de la requête SQL : " . $ex->getMessage());
    }
    ?>
    
    <!-- Modal de visualisation et ajout d'un article au panier -->
    <div class="voir-article-overlay" id="voirArticleOverlay">
        <section class="voir-article">
            <!-- Zone d'affichage de l'image du produit -->
            <div class="voir-article-img">
                <img src="" alt="">
            </div>
            
            <!-- Zone des détails et du formulaire d'ajout -->
            <div class="voir-article-details">
                <!-- Nom du produit -->
                <h2 class="voir-article-nom"></h2>
                <!-- Prix du produit -->
                <div class="voir-article-prix"></div>
                
                <!-- Formulaire d'ajout au panier -->
                <form id="ajouterProduitForm" method="POST" class="voir-article-btns">
                    <!-- Champ caché pour l'ID du produit -->
                    <input type="hidden" name="idProduit" id="produitId">
                    <!-- Champ de saisie de la quantité -->
                    <input type="number" name="quantite" id="quantiteProduit" placeholder="1" min="1" value="1" required>
                    <!-- Bouton de validation -->
                    <button type="submit" class="valider-btn" name="valider">Valider</button>
                    <!-- Bouton de retour -->
                    <button type="button" id="closeVoirArticle" class="retour-btn">Retour</button>
                </form>
            </div>
        </section>
    </div>

    <?php
    // Inclusion du composant modal de détail de commande
    include 'component/componentVoirCommandeOverlay.php';
    ?>

    <!-- Scripts JavaScript pour l'interactivité -->
    <script>
        // ===== GESTION DE L'OUVERTURE DE LA MODAL PRODUIT =====
        // Ajout d'un événement de clic sur chaque article
        document.querySelectorAll('.article').forEach(article => {
            article.addEventListener('click', function() {
                // Récupération de l'overlay (modal)
                const overlay = document.getElementById('voirArticleOverlay');
                
                // Récupération des informations de l'article cliqué
                const imgSrc = this.querySelector('img').src;           // URL de l'image
                const nom = this.querySelector('h2').textContent;        // Nom du produit
                const prix = this.querySelector('h3').textContent;       // Prix du produit
                const idProduit = this.getAttribute('data-id');          // ID du produit

                // Mise à jour du contenu de la modal
                overlay.querySelector('.voir-article-img img').src = imgSrc;
                overlay.querySelector('.voir-article-nom').textContent = nom;
                overlay.querySelector('.voir-article-prix').textContent = 'Prix : ' + prix;
                overlay.querySelector('#produitId').value = idProduit;   // Stockage de l'ID pour le formulaire
                
                // Affichage de la modal
                overlay.style.display = 'flex';
            });
        });

        // ===== FERMETURE DE LA MODAL PRODUIT =====
        document.getElementById('closeVoirArticle').addEventListener('click', function(e) {
            e.preventDefault();  // Empêcher le comportement par défaut du bouton
            document.getElementById('voirArticleOverlay').style.display = 'none';
        });

        // ===== GESTION DE L'AFFICHAGE DES NOTIFICATIONS =====
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération des éléments de notification
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

            // ===== FONCTIONS D'AFFICHAGE DES DIFFÉRENTES SECTIONS =====
            /**
             * Fonction utilitaire pour afficher une section et masquer les autres
             * @param {string} sectionAfficher - ID ou classe de la section à afficher
             * @param {Array} sectionsACacher - Tableau des sections à masquer
             */
            function afficherSection(sectionAfficher, sectionsACacher) {
                // Affichage de la section demandée
                const sectionAAfficher = document.getElementById(sectionAfficher) || document.querySelector('.' + sectionAfficher);
                if (sectionAAfficher) {
                    sectionAAfficher.style.display = 'block';
                }
                
                // Masquage de toutes les autres sections
                sectionsACacher.forEach(function(sectionId) {
                    const section = document.getElementById(sectionId) || document.querySelector('.' + sectionId);
                    if (section) {
                        section.style.display = 'none';
                    }
                });
            }

            // Fonction pour afficher la section paiement
            function afficherPaiement() {
                afficherSection('sectionPaiement', ['sectionPanier', 'sectionArticle', 'sectionProfile']);
            }

            // Fonction pour afficher la section panier
            function afficherPanier() {
                afficherSection('sectionPanier', ['sectionArticle', 'sectionPaiement', 'sectionProfile']);
            }

            // Fonction pour afficher la section articles
            function afficherArticles() {
                afficherSection('sectionArticle', ['sectionPanier', 'sectionPaiement', 'sectionProfile']);
            }

            // Fonction pour afficher le profil utilisateur
            function afficherProfile() {
                afficherSection('sectionProfile', ['sectionPanier', 'sectionPaiement', 'sectionArticle']);
            }

            // ===== GESTION DES BOUTONS DE NAVIGATION =====
            
            // Bouton de validation du paiement
            const boutonValiderPaiement = document.querySelector('.bouton-valider-paiement');
            if (boutonValiderPaiement) {
                boutonValiderPaiement.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            // Bouton d'annulation du paiement
            const boutonAnnulerPaiement = document.querySelector('.bouton-annuler');
            if (boutonAnnulerPaiement) {
                boutonAnnulerPaiement.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            // Bouton de retour depuis le panier
            const boutonRetourPanier = document.querySelector('.bouton-retour');
            if (boutonRetourPanier) {
                boutonRetourPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            // Clic sur le logo pour retourner aux articles
            const logoLien = document.querySelector('.logo a');
            if (logoLien) {
                logoLien.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            // Clic sur le lien profil
            const lienProfile = document.querySelector('.profile');
            if (lienProfile) {
                lienProfile.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherProfile();
                });
            }

            // ===== GESTION DE LA MODAL DE DÉTAIL DE COMMANDE =====
            const voirCommandeOverlay = document.getElementById('voirCommandeOverlay');
            const closeVoirCommande = document.getElementById('closeVoirCommande');
            const voirCommandeBtns = document.querySelectorAll('.voir-commande-btn');
            
            // Ouverture de la modal au clic sur "Voir la commande"
            voirCommandeBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (voirCommandeOverlay) {
                        voirCommandeOverlay.classList.add('active');
                    }
                });
            });
            
            // Fermeture de la modal avec le bouton X
            if (closeVoirCommande && voirCommandeOverlay) {
                closeVoirCommande.addEventListener('click', function() {
                    voirCommandeOverlay.classList.remove('active');
                });
            }

            // Fermeture de la modal en cliquant sur le fond sombre
            if (voirCommandeOverlay) {
                voirCommandeOverlay.addEventListener('click', function(e) {
                    if (e.target === voirCommandeOverlay) {
                        voirCommandeOverlay.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>
