<?php
    include "component/componentDocType.php";
?>
<body>
    <?php
        include 'component/componentNavConnected.php'; 
    ?>
    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
                include "component/componentArticle.php";
            ?>
        </div>
    </section>

    <?php
        include 'component/componentPanier.php';
    ?>

    <?php
        include 'component/componentPaiement.php';
    ?>

    <?php
        include 'component/componentProfile.php';
    ?>

    <?php
        include 'component/componentVoirArticleOverlay.php';
    ?>

    <?php
        include 'component/componentVoirCommandeOverlay.php';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Masquer la notification au chargement et toggle au clic
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

            // Pop-up voir-article sur clic d'un article
            const voirArticleOverlay = document.getElementById('voirArticleOverlay');
            const closeVoirArticle = document.getElementById('closeVoirArticle');
            const articles = document.querySelectorAll('.areaArticle .article');
            
            articles.forEach(function(article) {
                article.addEventListener('click', function() {
                    if (voirArticleOverlay) {
                        voirArticleOverlay.classList.add('active');
                    }
                });
            });
            
            if (closeVoirArticle && voirArticleOverlay) {
                closeVoirArticle.addEventListener('click', function() {
                    voirArticleOverlay.classList.remove('active');
                });
            }

            // Fonctions de navigation entre sections
            function afficherSection(sectionAfficher, sectionsACacher) {
                // Afficher la section demandée
                const sectionAAfficher = document.getElementById(sectionAfficher) || document.querySelector('.' + sectionAfficher);
                if (sectionAAfficher) {
                    sectionAAfficher.style.display = 'block';
                }
                
                // Cacher toutes les autres sections
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

            // Événements pour les boutons
            const boutonValiderPanier = document.querySelector('.bouton-valider');
            if (boutonValiderPanier) {
                boutonValiderPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherPaiement();
                });
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

            const lienPanier = document.querySelector('.pannier');
            if (lienPanier) {
                lienPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherPanier();
                });
            }

            const logoLien = document.querySelector('.logo a');
            if (logoLien) {
                logoLien.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            // Événement pour afficher le profile
            const lienProfile = document.querySelector('.profile');
            if (lienProfile) {
                lienProfile.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherProfile();
                });
            }

            // Gestion de l'overlay pour voir le détail d'une commande
            const voirCommandeOverlay = document.getElementById('voirCommandeOverlay');
            const closeVoirCommande = document.getElementById('closeVoirCommande');
            const voirCommandeBtns = document.querySelectorAll('.voir-commande-btn');
            
            voirCommandeBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Ici, on pourrait récupérer les données spécifiques à la commande
                    // et les injecter dans l'overlay
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

            // Fermer l'overlay en cliquant en dehors de la popup
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