<?php
    include "component/componentDocType.php";
?>
<body>
    <?php
        include "component/componentNav.php";
    ?>

    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
                include "component/componentArticle.php";
            ?>
        </div>
    </section>

    <div class="voir-article-overlay" id="voirArticleOverlay">
        <section class="voir-article">
            <?php
                include "component/componentArticleOverlay.php";
            ?>
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