<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoWeb</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <nav>
        <div class="logo"><a href="index.php">RestoWeb</a></div>
        <div class="auth-buttons">
            <div><a href="connection.php">Connection</a></div>
            <div><a href="inscription.php">Inscription</a></div>
        </div>
    </nav>

    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
            include "component/componentArticle.php";
            ?>
        </div>
    </section>

    <div class="voir-article-overlay" id="voirArticleOverlay">
        <section class="voir-article">
            <div class="voir-article-img">
                <img src="" alt="">
            </div>
            <div class="voir-article-details">
                <h2 class="voir-article-nom"></h2>
                <div class="voir-article-prix"></div>
                <div class="voir-article-btns">
                    <input type="number" placeholder="1" min="1">
                    <button class="valider-btn" onclick="window.location.href='connection.php'">
                        <p>Valider</p>
                    </button>
                    <button id="closeVoirArticle" class="retour-btn">
                        <p>Retour</p>
                    </button>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.querySelectorAll('.article').forEach(article => {
            article.addEventListener('click', function() {
                const overlay = document.getElementById('voirArticleOverlay');
                const imgSrc = this.querySelector('img').src;
                const nom = this.querySelector('h2').textContent;
                const prix = this.querySelector('h3').textContent;

                overlay.querySelector('.voir-article-img img').src = imgSrc;
                overlay.querySelector('.voir-article-nom').textContent = nom;
                overlay.querySelector('.voir-article-prix').textContent = 'Prix : ' + prix;
                overlay.style.display = 'flex';
            });
        });

        document.getElementById('closeVoirArticle').addEventListener('click', function() {
            document.getElementById('voirArticleOverlay').style.display = 'none';
        });
    </script>
</body>
</html>