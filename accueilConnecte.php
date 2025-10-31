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
    include 'template/ini.php';
    include 'template/chekEtat.php';
    session_start();
    
    // Traitement de l'ajout de produit - DOIT être traité AVANT tout output HTML
    if (isset($_POST['valider']) && isset($_POST['idProduit']) && isset($_POST['quantite'])) {
        // Connexion à la base de données
        $dbh = db_connect();
        
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
        session_start();
        session_destroy();
        header("Location: index.php");
        exit();
    }

    include "template/ini.php";

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
            <a class="profile"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
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
    include 'component/componentProfile.php';
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
                <form id="ajouterProduitForm" method="POST" class="voir-article-btns">
                    <input type="hidden" name="idProduit" id="produitId">
                    <input type="number" name="quantite" id="quantiteProduit" placeholder="1" min="1" value="1" required>
                    <button type="submit" class="valider-btn" name="valider">Valider</button>
                    <button type="button" id="closeVoirArticle" class="retour-btn">Retour</button>
                </form>
            </div>
        </section>
    </div>

    <?php
    include 'component/componentVoirCommandeOverlay.php';
    ?>

    <script>
        document.querySelectorAll('.article').forEach(article => {
            article.addEventListener('click', function() {
                const overlay = document.getElementById('voirArticleOverlay');
                const imgSrc = this.querySelector('img').src;
                const nom = this.querySelector('h2').textContent;
                const prix = this.querySelector('h3').textContent;
                const idProduit = this.getAttribute('data-id');

                overlay.querySelector('.voir-article-img img').src = imgSrc;
                overlay.querySelector('.voir-article-nom').textContent = nom;
                overlay.querySelector('.voir-article-prix').textContent = 'Prix : ' + prix;
                overlay.querySelector('#produitId').value = idProduit;
                overlay.style.display = 'flex';
            });
        });

        document.getElementById('closeVoirArticle').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('voirArticleOverlay').style.display = 'none';
        });

        document.addEventListener('DOMContentLoaded', function() {
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

            function afficherSection(sectionAfficher, sectionsACacher) {
                const sectionAAfficher = document.getElementById(sectionAfficher) || document.querySelector('.' + sectionAfficher);
                if (sectionAAfficher) {
                    sectionAAfficher.style.display = 'block';
                }
                
                sectionsACacher.forEach(function(sectionId) {
                    const section = document.getElementById(sectionId) || document.querySelector('.' + sectionId);
                    if (section) {
                        section.style.display = 'none';
                    }
                });
            }

            function afficherPaiement() {
                afficherSection('sectionPaiement', ['sectionPanier', 'sectionArticle', 'sectionProfile']);
            }

            function afficherPanier() {
                afficherSection('sectionPanier', ['sectionArticle', 'sectionPaiement', 'sectionProfile']);
            }

            function afficherArticles() {
                afficherSection('sectionArticle', ['sectionPanier', 'sectionPaiement', 'sectionProfile']);
            }

            function afficherProfile() {
                afficherSection('sectionProfile', ['sectionPanier', 'sectionPaiement', 'sectionArticle']);
            }

            const boutonValiderPaiement = document.querySelector('.bouton-valider-paiement');
            if (boutonValiderPaiement) {
                boutonValiderPaiement.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const boutonAnnulerPaiement = document.querySelector('.bouton-annuler');
            if (boutonAnnulerPaiement) {
                boutonAnnulerPaiement.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const boutonRetourPanier = document.querySelector('.bouton-retour');
            if (boutonRetourPanier) {
                boutonRetourPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const logoLien = document.querySelector('.logo a');
            if (logoLien) {
                logoLien.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const lienProfile = document.querySelector('.profile');
            if (lienProfile) {
                lienProfile.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherProfile();
                });
            }

            const voirCommandeOverlay = document.getElementById('voirCommandeOverlay');
            const closeVoirCommande = document.getElementById('closeVoirCommande');
            const voirCommandeBtns = document.querySelectorAll('.voir-commande-btn');
            
            voirCommandeBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (voirCommandeOverlay) {
                        voirCommandeOverlay.classList.add('active');
                    }
                });
            });
            
            if (closeVoirCommande && voirCommandeOverlay) {
                closeVoirCommande.addEventListener('click', function() {
                    voirCommandeOverlay.classList.remove('active');
                });
            }

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
