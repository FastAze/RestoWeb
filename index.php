<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoWeb</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <!-- Barre de navigation pour utilisateurs non connectés -->
    <nav>
        <!-- Logo du site - lien vers la page d'accueil -->
        <div class="logo"><a href="index.php">RestoWeb</a></div>
        
        <!-- Boutons d'authentification -->
        <div class="auth-buttons">
            <!-- Lien vers la page de connexion -->
            <div><a href="connection.php">Connection</a></div>
            <!-- Lien vers la page d'inscription -->
            <div><a href="inscription.php">Inscription</a></div>
        </div>
    </nav>

    <!-- Section d'affichage des articles (produits) -->
    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
            // Inclusion du composant qui affiche tous les produits
            // Ce composant récupère les produits depuis la base de données
            include "component/componentArticleIndex.php";
            ?>
        </div>
    </section>

    <!-- Modal de visualisation d'un article en détail -->
    <div class="voir-article-overlay" id="voirArticleOverlay">
        <section class="voir-article">
            <!-- Zone d'affichage de l'image du produit -->
            <div class="voir-article-img">
                <img src="" alt="">
            </div>
            
            <!-- Zone d'affichage des détails du produit -->
            <div class="voir-article-details">
                <!-- Nom du produit -->
                <h2 class="voir-article-nom"></h2>
                <!-- Prix du produit -->
                <div class="voir-article-prix"></div>
                
                <!-- Boutons d'action -->
                <div class="voir-article-btns">
                    <!-- Champ de quantité (désactivé pour les non-connectés) -->
                    <input type="number" placeholder="1" min="1">
                    
                    <!-- Bouton de validation - redirige vers la page de connexion -->
                    <button class="valider-btn" onclick="window.location.href='connection.php'">
                        <p>Valider</p>
                    </button>
                    
                    <!-- Bouton de retour pour fermer la modal -->
                    <button id="closeVoirArticle" class="retour-btn">
                        <p>Retour</p>
                    </button>
                </div>
            </div>
        </section>
    </div>
</body>
</html>